@section('title', $title.' | Rasa Nusantara')

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold tracking-widest uppercase text-emas-500/70 mb-1">
                    ✦ Kelola Data ✦
                </p>
                <h1 class="text-2xl font-bold tracking-tight text-gradient-emas font-serif">
                    {{ $title }}
                </h1>
                <p class="mt-1 text-sm text-legam-300">
                    {{ number_format($records->total()) }} data ditemukan
                </p>
            </div>

            <a href="{{ route($routeName.'.create') }}"
               class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold
                      text-legam-950 shadow-emas transition-all duration-150
                      focus:outline-none focus:ring-2 focus:ring-emas-400/40 mt-1"
               style="background: linear-gradient(135deg, #f0b429, #a87000);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Data
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
                <a href="{{ route('admin.index') }}"
                   class="text-legam-500 hover:text-emas-600 transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Kelola Data
                </a>
                <svg class="w-3 h-3 text-legam-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-emas-600 font-medium">{{ $title }}</span>
            </nav>

            {{-- Flash Messages --}}
            @if (session('status'))
                <div role="status"
                     class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm text-green-800"
                     style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div role="alert"
                     class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm text-tera-800"
                     style="background: #fff5f2; border: 1px solid #fca38a;">
                    <svg class="w-4 h-4 text-tera-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert"
                     class="rounded-lg px-4 py-3 text-sm text-tera-800"
                     style="background: #fff5f2; border: 1px solid #fca38a;">
                    <p class="font-medium mb-2">Terdapat kesalahan:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Pencarian & Filter --}}
            <form method="GET" action="{{ route($routeName.'.index') }}"
                  class="card-nusantara p-4">

                {{-- Header form --}}
                <div class="flex items-center gap-2 mb-3 pb-3" style="border-bottom: 1px solid #e8d9c4;">
                    <span class="block w-1 h-4 rounded-full" style="background: linear-gradient(to bottom, #f0b429, #a87000);"></span>
                    <p class="text-xs font-semibold tracking-wider uppercase text-legam-600">
                        Pencarian & Filter
                    </p>
                </div>

                <div class="flex flex-wrap items-end gap-3">

                    {{-- Search --}}
                    <div class="min-w-56 flex-1">
                        <label for="search" class="block text-xs font-medium text-legam-600 mb-1">
                            Kata Kunci
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-legam-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <input id="search" name="search" type="search"
                                   value="{{ request('search') }}"
                                   class="input-legam pl-9"
                                   placeholder="Masukkan kata kunci…">
                        </div>
                    </div>

                    {{-- Filter Kategori (jika tersedia) --}}
                    @isset($categoryOptions)
                        <div class="min-w-48">
                            <label for="category_id" class="block text-xs font-medium text-legam-600 mb-1">
                                Kategori
                            </label>
                            <select id="category_id" name="category_id" class="input-legam">
                                <option value="">Semua kategori</option>
                                @foreach ($categoryOptions as $id => $name)
                                    <option value="{{ $id }}" @selected((string) request('category_id') === (string) $id)>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endisset

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold
                                       text-legam-950 shadow-emas transition-all duration-150
                                       focus:outline-none focus:ring-2 focus:ring-emas-400/40"
                                style="background: linear-gradient(135deg, #f0b429, #a87000);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Cari
                        </button>

                        @if (request()->hasAny(['search', 'category_id']))
                            <a href="{{ route($routeName.'.index') }}"
                               class="inline-flex items-center gap-1 text-sm text-legam-500 hover:text-tera-600
                                      px-3 py-2 rounded-lg hover:bg-legam-50 transition-colors duration-150">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reset
                            </a>
                        @endif
                    </div>

                </div>
            </form>

            {{-- Konten Tabel / Empty State --}}
            @if ($records->isEmpty())
                <div class="card-nusantara px-6 py-14 text-center">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-full flex items-center justify-center"
                         style="background: rgba(240,180,41,0.08); border: 1px solid rgba(240,180,41,0.18);">
                        <svg class="w-7 h-7 text-emas-400/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-legam-700">Tidak ada data yang cocok</p>
                    <p class="mt-1 text-xs text-legam-400">Coba ubah kata kunci atau reset filter.</p>
                </div>

            @else
                {{-- Tabel Data --}}
                <div class="card-nusantara overflow-hidden">

                    {{-- Header tabel bergaya Batik Legam --}}
                    <div class="batik-header-bg px-5 py-3 flex items-center justify-between"
                         style="border-bottom: 1px solid rgba(240,180,41,0.20);">
                        <div class="flex items-center gap-2">
                            <span class="block w-1 h-4 rounded-full" style="background: linear-gradient(to bottom, #f0b429, #a87000);"></span>
                            <h2 class="text-xs font-semibold text-emas-400 uppercase tracking-wider">
                                Data {{ $title }}
                            </h2>
                        </div>
                        <span class="text-xs text-legam-400">
                            {{ $records->firstItem() }}–{{ $records->lastItem() }} dari {{ number_format($records->total()) }}
                        </span>
                    </div>

                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="min-w-full table-legam">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column => $label)
                                        <th scope="col">
                                            @if (isset($sortableColumns[$column]))
                                                @php
                                                    $isActive  = isset($sort) && $sort === $column;
                                                    $nextDir   = ($isActive && isset($direction) && $direction === 'asc') ? 'desc' : 'asc';
                                                @endphp
                                                <a href="{{ route($routeName.'.index', array_merge(request()->except(['sort','direction','page']), ['sort' => $column, 'direction' => $nextDir])) }}"
                                                   class="inline-flex items-center gap-1 hover:text-emas-500 transition-colors {{ $isActive ? 'text-emas-500 font-semibold' : '' }}">
                                                    {{ $label }}
                                                    @if ($isActive)
                                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                                            @if (isset($direction) && $direction === 'asc')
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                            @else
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                            @endif
                                                        </svg>
                                                    @else
                                                        <svg class="w-3 h-3 shrink-0 text-legam-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                                        </svg>
                                                    @endif
                                                </a>
                                            @else
                                                {{ $label }}
                                            @endif
                                        </th>
                                    @endforeach
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach ($records as $record)
                                    <tr>
                                        @foreach ($columns as $column => $label)
                                            <td>
                                                @if (in_array($column, ['price', 'subtotal'], true))
                                                    <span class="font-medium text-emas-700">Rp {{ number_format((float) data_get($record, $column), 0, ',', '.') }}</span>
                                                @elseif ($column === 'status')
                                                    @php $sv = (string) data_get($record, $column); @endphp
                                                    @php $bc = $sv === 'menunggu' ? 'badge-menunggu' : ($sv === 'diproses' ? 'badge-diproses' : ($sv === 'dikirim' ? 'badge-dikirim' : ($sv === 'selesai' ? 'badge-selesai' : 'badge-menunggu'))); @endphp
                                                    <span class="{{ $bc }}">{{ ucfirst($sv) }}</span>
                                                @else
                                                    {{ data_get($record, $column) }}
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Aksi --}}
                                        <td class="whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route($routeName.'.edit', $record) }}"
                                                   class="inline-flex items-center gap-1 text-xs font-medium text-emas-700
                                                          hover:text-emas-600 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Ubah
                                                </a>
                                                <form method="POST"
                                                      action="{{ route($routeName.'.destroy', $record) }}"
                                                      onsubmit="return confirm('Hapus data ini? Tindakan tidak dapat diurungkan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 text-xs font-medium text-tera-600
                                                                   hover:text-tera-500 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                         style="border-top: 1px solid #e8d9c4;">
                        <p class="text-sm text-legam-500">
                            Menampilkan
                            <span class="font-semibold text-legam-800">{{ $records->firstItem() }}–{{ $records->lastItem() }}</span>
                            dari
                            <span class="font-semibold text-legam-800">{{ number_format($records->total()) }}</span>
                            data
                        </p>
                        <nav aria-label="Navigasi halaman data" class="pagination-legam">
                            {{ $records->links() }}
                        </nav>
                    </div>

                </div>
            @endif

        </div>
    </div>
</x-app-layout>
