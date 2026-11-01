<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search'      => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort'        => ['nullable', 'string', 'in:id,qty,subtotal'],
            'direction'   => ['nullable', 'string', 'in:asc,desc'],
        ]);
        $search    = trim($filters['search'] ?? '');
        $sort      = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'desc';

        $records = OrderItem::query()
            ->with(['order', 'menu.category'])
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->whereHas(
                'menu',
                fn ($menuQuery) => $menuQuery->where('category_id', $categoryId),
            ))
            ->where(function ($query) use ($search) {
                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search)
                        ->orWhere('qty', (int) $search)
                        ->orWhere('order_id', (int) $search);
                }

                $query->orWhereHas('menu', fn ($menuQuery) => $menuQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('menu.category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.resources.index', [
            'title'           => 'Item pesanan',
            'routeName'       => 'admin.order-items',
            'records'         => $records,
            'columns'         => [
                'id'                 => 'ID item',
                'order.id'           => 'ID order',
                'menu.name'          => 'Menu',
                'menu.category.name' => 'Kategori',
                'qty'                => 'Jumlah',
                'subtotal'           => 'Subtotal',
            ],
            'sortableColumns' => ['id' => 'ID item', 'qty' => 'Jumlah', 'subtotal' => 'Subtotal'],
            'sort'            => $sort,
            'direction'       => $direction,
            'categoryOptions' => Category::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        return $this->form();
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['subtotal'] = $data['qty'] * Menu::findOrFail($data['menu_id'])->price;
        OrderItem::create($data);

        return to_route('admin.order-items.index')->with('status', 'Item pesanan berhasil ditambahkan.');
    }

    public function edit(OrderItem $orderItem): View
    {
        return $this->form($orderItem);
    }

    public function update(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['subtotal'] = $data['qty'] * Menu::findOrFail($data['menu_id'])->price;
        $orderItem->update($data);

        return to_route('admin.order-items.index')->with('status', 'Item pesanan berhasil diperbarui.');
    }

    public function destroy(OrderItem $orderItem): RedirectResponse
    {
        $orderItem->delete();

        return to_route('admin.order-items.index')->with('status', 'Item pesanan berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);
    }

    private function form(?OrderItem $orderItem = null): View
    {
        return view('admin.resources.form', [
            'title' => 'Item pesanan',
            'routeName' => 'admin.order-items',
            'record' => $orderItem,
            'formNote' => 'Subtotal dihitung otomatis dari jumlah dikali harga menu yang dipilih.',
            'fields' => [
                'order_id' => [
                    'label' => 'Pesanan',
                    'type' => 'select',
                    'options' => Order::query()->orderByDesc('id')->get()
                        ->mapWithKeys(fn (Order $order) => [$order->getKey() => 'Pesanan #'.$order->getKey()]),
                ],
                'menu_id' => [
                    'label' => 'Menu',
                    'type' => 'select',
                    'options' => Menu::query()->orderBy('name')->pluck('name', 'id'),
                ],
                'qty' => ['label' => 'Jumlah', 'type' => 'number', 'min' => 1, 'step' => 1],
            ],
        ]);
    }
}
