<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search'      => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort'        => ['nullable', 'string', 'in:id,name,price'],
            'direction'   => ['nullable', 'string', 'in:asc,desc'],
        ]);
        $search    = trim($filters['search'] ?? '');
        $sort      = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? 'asc';

        $records = Menu::query()
            ->with('category')
            ->withCount('orderItems')
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.resources.index', [
            'title'           => 'Menu',
            'routeName'       => 'admin.menus',
            'records'         => $records,
            'columns'         => [
                'name'              => 'Nama menu',
                'category.name'     => 'Kategori',
                'price'             => 'Harga',
                'order_items_count' => 'Jumlah item terjual',
            ],
            'sortableColumns' => ['name' => 'Nama menu', 'price' => 'Harga'],
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
        Menu::create($this->validatedData($request));

        return to_route('admin.menus.index')->with('status', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu): View
    {
        return $this->form($menu);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $menu->update($this->validatedData($request));

        return to_route('admin.menus.index')->with('status', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        if ($menu->orderItems()->exists()) {
            return back()->with('error', 'Menu sudah digunakan pada item pesanan dan tidak dapat dihapus.');
        }

        $menu->delete();

        return to_route('admin.menus.index')->with('status', 'Menu berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ]);
    }

    private function form(?Menu $menu = null): View
    {
        return view('admin.resources.form', [
            'title' => 'Menu',
            'routeName' => 'admin.menus',
            'record' => $menu,
            'fields' => [
                'name' => ['label' => 'Nama menu', 'type' => 'text'],
                'price' => ['label' => 'Harga (rupiah)', 'type' => 'number', 'min' => 0, 'step' => 1],
                'category_id' => [
                    'label' => 'Kategori',
                    'type' => 'select',
                    'options' => Category::query()->orderBy('name')->pluck('name', 'id'),
                ],
            ],
        ]);
    }
}
