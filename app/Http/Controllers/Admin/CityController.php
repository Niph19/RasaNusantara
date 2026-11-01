<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    private const ALLOWED_SORT_COLUMNS = ['id', 'name'];

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

        $records = City::query()
            ->withCount('customers')
            ->where('name', 'like', '%'.$search.'%')
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.resources.index', [
            'title'     => 'Kota',
            'routeName' => 'admin.cities',
            'records'   => $records,
            'columns'   => ['name' => 'Nama kota', 'customers_count' => 'Jumlah pelanggan'],
            'sortableColumns' => ['name' => 'Nama kota'],
            'sort'      => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return $this->form();
    }

    public function store(Request $request): RedirectResponse
    {
        City::create($request->validate(['name' => ['required', 'string', 'max:255']]));

        return to_route('admin.cities.index')->with('status', 'Kota berhasil ditambahkan.');
    }

    public function edit(City $city): View
    {
        return $this->form($city);
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $city->update($request->validate(['name' => ['required', 'string', 'max:255']]));

        return to_route('admin.cities.index')->with('status', 'Kota berhasil diperbarui.');
    }

    public function destroy(City $city): RedirectResponse
    {
        if ($city->customers()->exists()) {
            return back()->with('error', 'Kota masih digunakan oleh pelanggan dan tidak dapat dihapus.');
        }

        $city->delete();

        return to_route('admin.cities.index')->with('status', 'Kota berhasil dihapus.');
    }

    private function form(?City $city = null): View
    {
        return view('admin.resources.form', [
            'title' => 'Kota',
            'routeName' => 'admin.cities',
            'record' => $city,
            'fields' => ['name' => ['label' => 'Nama kota', 'type' => 'text']],
        ]);
    }
}
