<?php

namespace Pterodactyl\Services\Billing;

use Carbon\Carbon;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Invoice;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\PricePackage;
use Pterodactyl\Models\User;

class InvoiceService
{
    public function __construct(private PakasirService $pakasir)
    {
    }

    /**
     * Create a new invoice + call Pakasir to generate QRIS string.
     */
    public function create(User $user, PricePackage $package, Node $node, Egg $egg): Invoice
    {
        $orderId = $this->generateOrderId();
        $amount = (int) $package->price;

        $pakasirData = $this->pakasir->createQrisTransaction($orderId, $amount);

        $expiredAt = null;
        if (!empty($pakasirData['expired_at'])) {
            try {
                $expiredAt = Carbon::parse($pakasirData['expired_at']);
            } catch (\Throwable $e) {
                $expiredAt = Carbon::now()->addMinutes(15);
            }
        } else {
            $expiredAt = Carbon::now()->addMinutes(15);
        }

        return Invoice::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'node_id' => $node->id,
            'egg_id' => $egg->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'fee' => $pakasirData['fee'],
            'total_payment' => $pakasirData['total_payment'],
            'payment_method' => PakasirService::PAYMENT_METHOD_QRIS,
            'payment_number' => $pakasirData['payment_number'],
            'expired_at' => $expiredAt,
            'status' => Invoice::STATUS_PENDING,
        ]);
    }

    /**
     * Generate a unique order ID. Format: INV-YYYYMMDD-XXXXXXXX.
     */
    public function generateOrderId(): string
    {
        do {
            $id = 'INV-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        } while (Invoice::where('order_id', $id)->exists());

        return $id;
    }

    /**
     * Mark an invoice as paid.
     */
    public function markPaid(Invoice $invoice): bool
    {
        if ($invoice->status === Invoice::STATUS_PAID) {
            return false;
        }

        $invoice->status = Invoice::STATUS_PAID;
        $invoice->paid_at = Carbon::now();
        $invoice->save();

        return true;
    }
}