<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const ALLOWED_SORT_COLUMNS = [
        'id' => 'orders.id',
        'customer' => 'customer.name',
        'city' => 'city.name',
        'courier' => 'courier.name',
        'payment_method' => 'payment_method.name',
        'status' => 'orders.status',
    ];

    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'sort' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::ALLOWED_SORT_COLUMNS))],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $sort = $validated['sort'] ?? null;
        $direction = $validated['direction'] ?? 'desc';

        $query = Order::query()
            ->with([
                'customer.city',
                'courier',
                'paymentMethod',
                'orderItems.menu.category',
            ]);

        if ($sort && isset(self::ALLOWED_SORT_COLUMNS[$sort])) {
            $sortMapping = self::ALLOWED_SORT_COLUMNS[$sort];

            if (str_contains($sortMapping, '.')) {
                [$relation, $column] = explode('.', $sortMapping, 2);

                if ($relation === 'customer') {
                    $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                        ->select('orders.*')
                        ->orderBy('customers.name', $direction);
                } elseif ($relation === 'city') {
                    $query->join('customers as c', 'orders.customer_id', '=', 'c.id')
                        ->join('cities', 'c.city_id', '=', 'cities.id')
                        ->select('orders.*')
                        ->orderBy('cities.name', $direction);
                } elseif ($relation === 'courier') {
                    $query->join('couriers', 'orders.courier_id', '=', 'couriers.id')
                        ->select('orders.*')
                        ->orderBy('couriers.name', $direction);
                } elseif ($relation === 'payment_method') {
                    $query->join('payment_methods', 'orders.payment_method_id', '=', 'payment_methods.id')
                        ->select('orders.*')
                        ->orderBy('payment_methods.name', $direction);
                }
            } else {
                $query->orderBy($sortMapping, $direction);
            }
        } else {
            $query->orderByDesc('orders.id');
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('dashboard.index', compact('orders', 'sort', 'direction'));
    }
}
