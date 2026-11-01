<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourierController extends Controller
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

        $records = Courier::query()
            ->withCount('orders')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.resources.index', [
            'title'           => 'Kurir',
            'routeName'       => 'admin.couriers',
            'records'         => $records,
            'columns'         => ['name' => 'Nama kurir', 'phone' => 'Telepon', 'orders_count' => 'Jumlah pesanan'],
            'sortableColumns' => ['name' => 'Nama kurir'],
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
        Courier::create($this->validatedData($request));

        return to_route('admin.couriers.index')->with('status', 'Kurir berhasil ditambahkan.');
    }

    public function edit(Courier $courier): View
    {
        return $this->form($courier);
    }

    public function update(Request $request, Courier $courier): RedirectResponse
    {
        $courier->update($this->validatedData($request));

        return to_route('admin.couriers.index')->with('status', 'Kurir berhasil diperbarui.');
    }

    public function destroy(Courier $courier): RedirectResponse
    {
        if ($courier->orders()->exists()) {
            return back()->with('error', 'Kurir masih digunakan oleh pesanan dan tidak dapat dihapus.');
        }

        $courier->delete();

        return to_route('admin.couriers.index')->with('status', 'Kurir berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ]);
    }

    private function form(?Courier $courier = null): View
    {
        return view('admin.resources.form', [
            'title' => 'Kurir',
            'routeName' => 'admin.couriers',
            'record' => $courier,
            'fields' => [
                'name' => ['label' => 'Nama kurir', 'type' => 'text'],
                'phone' => ['label' => 'Nomor telepon', 'type' => 'tel'],
            ],
        ]);
    }
}
