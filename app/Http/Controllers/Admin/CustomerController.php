<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search'    => ['nullable', 'string', 'max:100'],
            'sort'      => ['nullable', 'string', 'in:id,name'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);
        $search    = trim($validated['search'] ?? '');
        $sort      = $validated['sort'] ?? 'name';
        $direction = $validated['direction'] ?? 'asc';

        $records = Customer::query()
            ->with('city')
            ->withCount('orders')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('city', fn ($cityQuery) => $cityQuery->where('name', 'like', "%{$search}%"));
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.resources.index', [
            'title'           => 'Pelanggan',
            'routeName'       => 'admin.customers',
            'records'         => $records,
            'columns'         => [
                'name'         => 'Nama pelanggan',
                'phone'        => 'Telepon',
                'city.name'    => 'Kota',
                'orders_count' => 'Jumlah pesanan',
            ],
            'sortableColumns' => ['name' => 'Nama pelanggan'],
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
        Customer::create($this->validatedData($request));

        return to_route('admin.customers.index')->with('status', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Customer $customer): View
    {
        return $this->form($customer);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validatedData($request));

        return to_route('admin.customers.index')->with('status', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->orders()->exists()) {
            return back()->with('error', 'Pelanggan masih memiliki pesanan dan tidak dapat dihapus.');
        }

        $customer->delete();

        return to_route('admin.customers.index')->with('status', 'Pelanggan berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
        ]);
    }

    private function form(?Customer $customer = null): View
    {
        return view('admin.resources.form', [
            'title' => 'Pelanggan',
            'routeName' => 'admin.customers',
            'record' => $customer,
            'fields' => [
                'name' => ['label' => 'Nama pelanggan', 'type' => 'text'],
                'phone' => ['label' => 'Nomor telepon', 'type' => 'tel'],
                'city_id' => [
                    'label' => 'Kota',
                    'type' => 'select',
                    'options' => City::query()->orderBy('name')->pluck('name', 'id'),
                ],
            ],
        ]);
    }
}
