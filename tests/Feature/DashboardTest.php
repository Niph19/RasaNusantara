<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createDashboardOrder(array $attributes = []): Order
{
    $city = City::factory()->create();
    $customer = Customer::factory()->create(['city_id' => $city->id]);
    $category = Category::factory()->create();
    $menu = Menu::factory()->create(['category_id' => $category->id]);

    $order = Order::factory()->create(array_merge([
        'customer_id' => $customer->id,
        'courier_id' => Courier::factory()->create()->id,
        'payment_method_id' => PaymentMethod::factory()->create()->id,
    ], $attributes));

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_id' => $menu->id,
        'qty' => 2,
        'subtotal' => 2 * $menu->price,
    ]);

    return $order;
}

it('requires an authenticated user to view the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('renders nested order details for an authenticated user', function () {
    $order = createDashboardOrder()->load([
        'customer.city',
        'courier',
        'paymentMethod',
        'orderItems.menu.category',
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Daftar Pesanan')
        ->assertSee('#'.$order->id)
        ->assertSee($order->customer->name)
        ->assertSee($order->customer->city->name)
        ->assertSee($order->orderItems->first()->menu->name)
        ->assertSee($order->orderItems->first()->menu->category->name)
        ->assertSee($order->courier->name)
        ->assertSee($order->paymentMethod->name);
});

it('paginates orders at fifteen per page', function () {
    $orders = collect(range(1, 16))->map(fn () => createDashboardOrder());

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('1–15')
        ->assertSeeText('dari 16 pesanan')
        ->assertSee('#'.$orders->last()->id)
        ->assertDontSee('#'.$orders->first()->id.'</td>');
});

it('loads the dashboard in fewer than ten database queries', function () {
    createDashboardOrder();
    $user = User::factory()->admin()->create();
    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $queryCount = 0;
    DB::listen(function (QueryExecuted $query) use (&$queryCount): void {
        $queryCount++;
    });

    $this->get(route('dashboard'))->assertOk();

    expect($queryCount)->toBeLessThan(10);
});

it('prevents lazy loading outside production', function () {
    Customer::factory(2)->create();
    $customer = Customer::query()->get()->firstOrFail();

    $this->expectException(LazyLoadingViolationException::class);

    $customer->city;
});

it('seeds the domain data in the required quantities', function () {
    $this->seed();

    expect(City::count())->toBe(50)
        ->and(Category::count())->toBe(10)
        ->and(PaymentMethod::count())->toBe(3)
        ->and(Courier::count())->toBe(10)
        ->and(Menu::count())->toBe(150)
        ->and(Customer::count())->toBe(200)
        ->and(Order::count())->toBe(150)
        ->and(OrderItem::count())->toBeBetween(450, 750)
        ->and(User::where('role', 'admin')->count())->toBeGreaterThanOrEqual(1)
        ->and(User::where('email', 'admin@rasanusantara.test')->value('role'))->toBe('admin');

    $invalidSubtotals = DB::table('order_items')
        ->join('menus', 'menus.id', '=', 'order_items.menu_id')
        ->whereColumn('order_items.subtotal', '!=', DB::raw('order_items.qty * menus.price'))
        ->count();

    expect($invalidSubtotals)->toBe(0);
});
