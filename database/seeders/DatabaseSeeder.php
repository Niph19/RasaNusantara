<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Rasa Nusantara',
            'email' => 'admin@rasanusantara.test',
        ]);

        User::factory(9)->customer()->create();

        $cities = City::factory(50)->create();

        foreach ([
            'Pembuka',
            'Utama',
            'Penutup',
            'Minuman',
            'Camilan',
            'Nasi Box',
            'Prasmanan',
            'Tumpeng',
            'Paket Hemat',
            'Dessert',
        ] as $name) {
            Category::factory()->create(['name' => $name]);
        }

        foreach (['Tunai', 'Transfer Bank', 'QRIS'] as $name) {
            PaymentMethod::factory()->create(['name' => $name]);
        }

        Courier::factory(10)->create();

        $categoryIds = Category::query()->pluck('id');

        for ($index = 0; $index < 150; $index++) {
            Menu::factory()->create([
                'category_id' => $categoryIds->random(),
            ]);
        }

        $cityIds = $cities->modelKeys();

        for ($index = 0; $index < 200; $index++) {
            Customer::factory()->create([
                'city_id' => fake()->randomElement($cityIds),
            ]);
        }

        $customerIds = Customer::query()->pluck('id');
        $paymentMethodIds = PaymentMethod::query()->pluck('id');
        $courierIds = Courier::query()->pluck('id');
        $menus = Menu::query()->get();

        for ($index = 0; $index < 150; $index++) {
            $order = Order::factory()->create([
                'customer_id' => $customerIds->random(),
                'payment_method_id' => $paymentMethodIds->random(),
                'courier_id' => $courierIds->random(),
            ]);

            foreach ($menus->shuffle()->take(fake()->numberBetween(3, 5)) as $menu) {
                $qty = fake()->numberBetween(1, 5);

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'qty' => $qty,
                    'subtotal' => $qty * $menu->price,
                ]);
            }
        }
    }
}
