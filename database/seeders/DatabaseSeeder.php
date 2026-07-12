<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $shop = Shop::create([
            'name' => 'Nexa POS Master Shop',
            'email' => 'admin@nexapos.com',
            'phone' => '01700000000',
            'address' => 'Dhaka, Bangladesh',
            'is_active' => true,
        ]);

        $admin = User::create([
            'shop_id' => $shop->id,
            'role' => 'Shop Owner',
            'name' => 'Store Admin',
            'email' => 'admin@nexapos.com',
            'password' => Hash::make('password'),
        ]);

        $admin->assignRole('Shop Owner');
    }
}
