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
     * Expected payload (Pakasir spec):
     *  {
     *    "amount": 22000,
     *    "order_id": "240910HDE7C9",
     *    "project": "depodomain",
     *    "status": "completed",
     *    "payment_method": "qris",
     *    "completed_at": "2024-09-10T08:07:02.819+07:00"
     *  }
     *
     * Notes:
     *  - "project" must match settings::billing:pakasir_project (or env PAKASIR_PROJECT). Reject mismatches.
     *  - "amount" must equal invoice amount. Reject mismatches to prevent spoofed callbacks.
     *  - "status" normalized to lowercase; "completed" maps to PAID.
     */
    public function callback(Request $request): JsonResponse
    {
        $orderId = (string) $request->input('order_id', $request->input('orderId', ''));
        $status = strtolower((string) $request->input('status', ''));
        $project = (string) $request->input('project', '');
        $amount = (int) $request->input('amount', 0);

        if ($orderId === '') {
            return response()->json(['error' => 'missing order_id'], 422);
        }

        // Verify project matches our Pakasir config (defense against spoofed POSTs).
        $expectedProject = (string) ($this->invoiceService->getPakasirProject() ?? '');
        if ($expectedProject !== '' && $project !== '' && $project !== $expectedProject) {
            return response()->json(['error' => 'project mismatch', 'expected' => $expectedProject, 'got' => $project], 403);
        }

        $invoice = Invoice::where('order_id', $orderId)->first();
        if (!$invoice) {
            return response()->json(['error' => 'invoice not found'], 404);
        }

        // Verify amount matches invoice (Pakasir always echoes original amount).
        if ($amount > 0 && (int) $invoice->amount !== $amount) {
            return response()->json([
                'error' => 'amount mismatch',
                'expected' => (int) $invoice->amount,
                'got' => $amount,
            ], 403);
        }

        $paidStates = ['paid', 'success', 'completed', 'settlement'];
        if (in_array($status, $paidStates, true)) {
            if ($this->invoiceService->markPaid($invoice)) {
                ProvisionServerJob::dispatch($invoice->id);
            }
        } else {
            $allowed = ['pending', 'paid', 'expired', 'failed'];
            if (in_array($status, $allowed, true)) {
                $invoice->status = $status;
                $invoice->save();
            }
        }

        return response()->json(['ok' => true]);
    }
}