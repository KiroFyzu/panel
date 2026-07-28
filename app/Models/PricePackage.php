<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $price
 * @property int|null $old_price
 * @property int $ram
 * @property int $cpu
 * @property int $disk
 * @property int $period_days
 * @property int $sort
 * @property bool $is_active
 */
class PricePackage extends Model
{
    protected $table = 'price_packages';

    public $timestamps = true;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'old_price',
        'ram', 'cpu', 'disk', 'period_days', 'sort', 'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'old_price' => 'integer',
        'ram' => 'integer',
        'cpu' => 'integer',
        'disk' => 'integer',
        'period_days' => 'integer',
        'sort' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getPeriodDaysAttribute(): int
    {
        $value = (int) ($this->attributes['period_days'] ?? 0);
        return $value > 0 ? $value : 30;
    }

    public function nodes(): BelongsToMany
    {
        return $this->belongsToMany(Node::class, 'package_nodes', 'package_id', 'node_id');
    }

    public function eggs(): BelongsToMany
    {
        return $this->belongsToMany(Egg::class, 'package_eggs', 'package_id', 'egg_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'package_id');
    }
}