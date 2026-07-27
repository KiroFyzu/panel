<?php

namespace Pterodactyl\Http\Controllers\Billing;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Invoice;
use Pterodactyl\Services\Billing\InvoiceService;
use Pterodactyl\Services\Billing\PakasirService;
use Pterodactyl\Jobs\Billing\ProvisionServerJob;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private PakasirService $pakasir,
    ) {
    }

    /**
     * Show invoice page with QRIS + countdown.
     */
    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorizeOwner($request, $invoice);

        return view('billing.invoice', [
            'invoice' => $invoice->load(['package', 'node', 'egg']),
        ]);
    }

    /**
     * Polling endpoint for invoice status. Optionally re-checks Pakasir to detect payment.
     */
    public function status(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeOwner($request, $invoice);

        if ($invoice->isPending()) {
            try {
                $remote = $this->pakasir->checkTransaction($invoice->order_id, (int) $invoice->amount);
                $status = strtolower((string) ($remote['status'] ?? ($remote['transaction']['status'] ?? '')));
                $paidStates = ['paid', 'success', 'completed', 'settlement'];

                if (in_array($status, $paidStates, true)) {
                    if ($this->invoiceService->markPaid($invoice)) {
                        ProvisionServerJob::dispatch($invoice->id);
                    }
                }
            } catch (\Throwable $e) {
                // ignore polling errors, return current status
            }
        }

        return response()->json([
            'id' => $invoice->id,
            'order_id' => $invoice->order_id,
            'status' => $invoice->status,
            'paid_at' => optional($invoice->paid_at)->toIso8601String(),
            'server_id' => $invoice->server_id,
        ]);
    }

    private function authorizeOwner(Request $request, Invoice $invoice): void
    {
        abort_unless(
            $request->user() && ($request->user()->id === $invoice->user_id || $request->user()->root_admin),
            403
        );
    }
}