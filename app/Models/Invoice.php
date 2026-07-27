<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $package_id
 * @property int $node_id
 * @property int $egg_id
 * @property string $order_id
 * @property int $amount
 * @property int|null $fee
 * @property int $total_payment
 * @property string $payment_method
 * @property string|null $payment_number
 * @property \Carbon\Carbon|null $expired_at
 * @property string $status
 * @property \Carbon\Carbon|null $paid_at
 * @property int|null $server_id
 */
class Invoice extends Model
{
    protected $table = 'invoices';

    public $timestamps = true;

    protected $fillable = [
        'user_id', 'package_id', 'node_id', 'egg_id', 'order_id',
        'amount', 'fee', 'total_payment', 'payment_method',
        'payment_number', 'expired_at', 'status', 'paid_at', 'server_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'fee' => 'integer',
        'total_payment' => 'integer',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PricePackage::class, 'package_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function egg(): BelongsTo
    {
        return $this->belongsTo(Egg::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}