<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Pterodactyl\Models\PricePackage;

class PricePackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['name' => 'STARTER', 'price' => 4000, 'old_price' => 4999, 'ram' => 1, 'cpu' => 100, 'disk' => 3],
            ['name' => 'LITE', 'price' => 7200, 'old_price' => 8999, 'ram' => 2, 'cpu' => 150, 'disk' => 5],
            ['name' => 'BASIC', 'price' => 10400, 'old_price' => 12999, 'ram' => 3, 'cpu' => 200, 'disk' => 8],
            ['name' => 'MINI', 'price' => 14000, 'old_price' => 17499, 'ram' => 4, 'cpu' => 250, 'disk' => 11],
            ['name' => 'REGULAR', 'price' => 18000, 'old_price' => 22499, 'ram' => 5, 'cpu' => 300, 'disk' => 14],
            ['name' => 'STANDAR', 'price' => 22400, 'old_price' => 27999, 'ram' => 6, 'cpu' => 350, 'disk' => 17],
            ['name' => 'ADVANCED', 'price' => 26800, 'old_price' => 33499, 'ram' => 7, 'cpu' => 375, 'disk' => 20],
            ['name' => 'PRO', 'price' => 32000, 'old_price' => 39999, 'ram' => 8, 'cpu' => 400, 'disk' => 24],
        ];

        foreach ($packages as $i => $pkg) {
            PricePackage::updateOrCreate(
                ['slug' => Str::slug($pkg['name'])],
                array_merge($pkg, [
                    'slug' => Str::slug($pkg['name']),
                    'sort' => $i,
                    'is_active' => true,
                ])
            );
        }
    }
}