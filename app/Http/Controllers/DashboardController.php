<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'mode' => ['nullable', 'string', 'in:eager,lazy'],
            'sort' => ['nullable', 'string', 'in:id,customer,courier,payment_method,status'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $mode = $validated['mode'] ?? 'eager';
        $sort = $validated['sort'] ?? null;
        $direction = $validated['direction'] ?? 'desc';

        $query = Order::query();

        if ($mode === 'eager') {
            $query->with([
                'customer.city',
                'courier',
                'paymentMethod',
                'orderItems.menu.category',
            ]);
        }

        if ($sort === 'customer') {
            $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->select('orders.*')
                ->orderBy('customers.name', $direction);
        } elseif ($sort === 'courier') {
            $query->join('couriers', 'orders.courier_id', '=', 'couriers.id')
                ->select('orders.*')
                ->orderBy('couriers.name', $direction);
        } elseif ($sort === 'payment_method') {
            $query->join('payment_methods', 'orders.payment_method_id', '=', 'payment_methods.id')
                ->select('orders.*')
                ->orderBy('payment_methods.name', $direction);
        } elseif ($sort === 'status') {
            $query->orderBy('orders.status', $direction);
        } elseif ($sort === 'id') {
            $query->orderBy('orders.id', $direction);
        } else {
            $query->orderByDesc('orders.id');
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('dashboard.index', compact('orders', 'mode', 'sort', 'direction'));
    }
}
