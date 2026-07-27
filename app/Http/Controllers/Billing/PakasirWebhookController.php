<?php

namespace Pterodactyl\Http\Controllers\Billing;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Jobs\Billing\ProvisionServerJob;
use Pterodactyl\Models\Invoice;
use Pterodactyl\Services\Billing\InvoiceService;

class PakasirWebhookController extends Controller
{
    public function __construct(private InvoiceService $invoiceService)
    {
    }

    /**
     * Handle Pakasir callback. Endpoint: POST /api/billing/pakasir/callback
     *
     * Expected payload (best-effort, see Pakasir docs):
     *  { "order_id": "INV-...", "status": "paid", "amount": 99000 }
     *
     * Pakasir docs may evolve; we accept common variations.
     */
    public function callback(Request $request): JsonResponse
    {
        $orderId = (string) $request->input('order_id', $request->input('orderId', ''));
        $status = strtolower((string) $request->input('status', ''));

        if ($orderId === '') {
            return response()->json(['error' => 'missing order_id'], 422);
        }

        $invoice = Invoice::where('order_id', $orderId)->first();
        if (!$invoice) {
            return response()->json(['error' => 'invoice not found'], 404);
        }

        $paidStates = ['paid', 'success', 'completed', 'settlement'];
        if (in_array($status, $paidStates, true)) {
            if ($this->invoiceService->markPaid($invoice)) {
                ProvisionServerJob::dispatch($invoice->id);
            }
        } else {
            // Update status for failed/expired etc.
            $allowed = ['pending', 'paid', 'expired', 'failed'];
            if (in_array($status, $allowed, true)) {
                $invoice->status = $status;
                $invoice->save();
            }
        }

        return response()->json(['ok' => true]);
    }
}