<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private const STATUSES = ['menunggu', 'diproses', 'dikirim', 'selesai'];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search'    => ['nullable', 'string', 'max:100'],
            'sort'      => ['nullable', 'string', 'in:id,status'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);
        $search    = trim($validated['search'] ?? '');
        $sort      = $validated['sort'] ?? 'id';
        $direction = $validated['direction'] ?? 'desc';

        $records = Order::query()
            ->with(['customer', 'paymentMethod', 'courier'])
            ->withCount('orderItems')
            ->where(function ($query) use ($search) {
                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }

                $query->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('paymentMethod', fn ($paymentQuery) => $paymentQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('courier', fn ($courierQuery) => $courierQuery->where('name', 'like', "%{$search}%"));
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.resources.index', [
            'title'           => 'Pesanan',
            'routeName'       => 'admin.orders',
            'records'         => $records,
            'columns'         => [
                'id'                 => 'ID order',
                'customer.name'      => 'Pelanggan',
                'courier.name'       => 'Kurir',
                'paymentMethod.name' => 'Metode pembayaran',
                'status'             => 'Status',
                'order_items_count'  => 'Jumlah item',
            ],
            'sortableColumns' => ['id' => 'ID order', 'status' => 'Status'],
            'sort'            => $sort,
            'direction'       => $direction,
        ]);
    }

    public function create(): View
    {
        return $this->form();
    }

    public function store(Request $request): RedirectResponse
    {
        Order::create($this->validatedData($request));

        return to_route('admin.orders.index')->with('status', 'Pesanan berhasil ditambahkan.');
    }

    public function edit(Order $order): View
    {
        return $this->form($order);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $order->update($this->validatedData($request));

        return to_route('admin.orders.index')->with('status', 'Pesanan berhasil diperbarui.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return to_route('admin.orders.index')->with('status', 'Pesanan dan seluruh itemnya berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'courier_id' => ['required', 'integer', 'exists:couriers,id'],
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
        ]);
    }

    private function form(?Order $order = null): View
    {
        return view('admin.resources.form', [
            'title' => 'Pesanan',
            'routeName' => 'admin.orders',
            'record' => $order,
            'formNote' => 'Item menu untuk pesanan dikelola pada halaman Item Pesanan.',
            'fields' => [
                'customer_id' => [
                    'label' => 'Pelanggan',
                    'type' => 'select',
                    'options' => Customer::query()->orderBy('name')->pluck('name', 'id'),
                ],
                'payment_method_id' => [
                    'label' => 'Metode pembayaran',
                    'type' => 'select',
                    'options' => PaymentMethod::query()->orderBy('name')->pluck('name', 'id'),
                ],
                'courier_id' => [
                    'label' => 'Kurir',
                    'type' => 'select',
                    'options' => Courier::query()->orderBy('name')->pluck('name', 'id'),
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'select',
                    'options' => collect(self::STATUSES)->mapWithKeys(fn ($status) => [$status => ucfirst($status)]),
                ],
            ],
        ]);
    }
}
