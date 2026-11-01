<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
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

        $records = Category::query()
            ->withCount('menus')
            ->where('name', 'like', '%'.$search.'%')
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.resources.index', [
            'title'          => 'Kategori',
            'routeName'      => 'admin.categories',
            'records'        => $records,
            'columns'        => ['name' => 'Nama kategori', 'menus_count' => 'Jumlah menu'],
            'sortableColumns' => ['name' => 'Nama kategori'],
            'sort'           => $sort,
            'direction'      => $direction,
        ]);
    }

    public function create(): View
    {
        return $this->form();
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($request->validate(['name' => ['required', 'string', 'max:255']]));

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return $this->form($category);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($request->validate(['name' => ['required', 'string', 'max:255']]));

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->menus()->exists()) {
            return back()->with('error', 'Kategori masih digunakan oleh menu dan tidak dapat dihapus.');
        }

        $category->delete();

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil dihapus.');
    }

    private function form(?Category $category = null): View
    {
        return view('admin.resources.form', [
            'title' => 'Kategori',
            'routeName' => 'admin.categories',
            'record' => $category,
            'fields' => ['name' => ['label' => 'Nama kategori', 'type' => 'text']],
        ]);
    }
}
