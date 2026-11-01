@section('title', 'Daftar Pesanan | Rasa Nusantara')

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                {{-- Ornamen teks bergaya Batik Legam --}}
                <p class="text-xs font-semibold tracking-widest uppercase text-emas-500/70 mb-1">
                    ✦ Pantau Operasional ✦
                </p>
                <h1 class="text-2xl font-bold tracking-tight text-gradient-emas font-serif">
                    Daftar Pesanan
                </h1>
                <p class="mt-1 text-sm text-legam-300">
                    Pelanggan, menu, dan pengiriman dalam satu tampilan terintegrasi.
                </p>
            </div>

            {{-- Statistik ringkas --}}
            <div class="flex items-center gap-2 mt-2 sm:mt-0">
                <div class="rounded-lg px-4 py-2 text-center"
                     style="background: rgba(240,180,41,0.12); border: 1px solid rgba(240,180,41,0.25);">
                    <p class="text-xl font-bold text-emas-400">{{ number_format($orders->total()) }}</p>
                    <p class="text-xs text-legam-400 uppercase tracking-wide">Total Pesanan</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Tabel Pesanan --}}
            <section aria-labelledby="orders-heading" class="card-nusantara">

                {{-- Header tabel bergaya Batik Legam --}}
                <div class="batik-header-bg px-5 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                     style="border-bottom: 1px solid rgba(240,180,41,0.20);">
                    <div class="flex items-center gap-2">
                        <span class="block w-1 h-5 rounded-full" style="background: linear-gradient(to bottom, #f0b429, #a87000);"></span>
                        <h2 id="orders-heading" class="text-sm font-semibold text-emas-400 uppercase tracking-wider">
                            Tabel Pesanan
                        </h2>
                    </div>
                    <div class="flex items-center gap-3">
                        @if(request('sort'))
                            <a href="{{ route('dashboard') }}"
                               class="text-xs text-legam-500 hover:text-emas-500 transition-colors">
                                Reset Filter
                            </a>
                        @endif
                        <a href="{{ route('dashboard', array_merge(request()->except('page'), ['mode' => 'eager'])) }}"
                           class="text-xs {{ $mode === 'eager' ? 'font-semibold text-emas-500' : 'text-legam-500 hover:text-emas-500' }}">
                            Eager
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->except('page'), ['mode' => 'lazy'])) }}"
                           class="text-xs {{ $mode === 'lazy' ? 'font-semibold text-red-500' : 'text-legam-500 hover:text-emas-500' }}">
                            Lazy
                        </a>
                        <span class="text-xs text-legam-400">
                            Halaman {{ $orders->currentPage() }} / {{ $orders->lastPage() }}
                        </span>
                    </div>
                </div>

                @if ($orders->isEmpty())
                    <div class="px-6 py-16 text-center bg-legam-50">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                             style="background: rgba(240,180,41,0.10); border: 1px solid rgba(240,180,41,0.20);">
                            <svg class="w-8 h-8 text-emas-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="text-base font-semibold text-legam-700">Belum ada pesanan</p>
                        <p class="mt-1 text-sm text-legam-400">Data pesanan akan tampil di sini setelah tersedia.</p>
                    </div>

                @else
                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="min-w-full table-legam">
                            <thead>
                                <tr>
                                    <th scope="col" class="whitespace-nowrap w-24">
                                        <a href="{{ route('dashboard', ['sort' => 'id', 'direction' => (request('sort') === 'id' && request('direction') === 'asc') ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-emas-500 transition-colors">
                                            ID Order
                                            @if(request('sort') === 'id')
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    @if(request('direction') === 'asc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                    @endif
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="min-w-52">
                                        <a href="{{ route('dashboard', ['sort' => 'customer', 'direction' => (request('sort') === 'customer' && request('direction') === 'asc') ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-emas-500 transition-colors">
                                            Pelanggan
                                            @if(request('sort') === 'customer')
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    @if(request('direction') === 'asc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                    @endif
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="min-w-80">
                                        Pesanan
                                    </th>
                                    <th scope="col" class="min-w-48">
                                        <a href="{{ route('dashboard', ['sort' => 'status', 'direction' => (request('sort') === 'status' && request('direction') === 'asc') ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-emas-500 transition-colors">
                                            Status
                                            @if(request('sort') === 'status')
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    @if(request('direction') === 'asc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                    @endif
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="min-w-52">
                                        <button type="button"
                                                onclick="toggleDeliverySort(event)"
                                                class="flex items-center gap-1 hover:text-emas-500 transition-colors w-full">
                                            Pengiriman &amp; Pembayaran
                                            @if(in_array(request('sort'), ['courier', 'payment_method']))
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    @if(request('direction') === 'asc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                    @endif
                                                </svg>
                                            @endif
                                        </button>
                                        <div id="deliverySortMenu" class="hidden absolute right-0 mt-1 w-48 rounded-md shadow-lg z-10" style="background: #fffcf7; border: 1px solid #e8d9c4;">
                                            <div class="py-1">
                                                <a href="{{ route('dashboard', ['sort' => 'courier', 'direction' => 'asc']) }}"
                                                   class="block px-4 py-2 text-sm text-legam-700 hover:bg-legam-50 @if(request('sort') === 'courier') font-semibold @endif">
                                                    Kurir (A-Z)
                                                </a>
                                                <a href="{{ route('dashboard', ['sort' => 'courier', 'direction' => 'desc']) }}"
                                                   class="block px-4 py-2 text-sm text-legam-700 hover:bg-legam-50 @if(request('sort') === 'courier' && request('direction') === 'desc') font-semibold @endif">
                                                    Kurir (Z-A)
                                                </a>
                                                <a href="{{ route('dashboard', ['sort' => 'payment_method', 'direction' => 'asc']) }}"
                                                   class="block px-4 py-2 text-sm text-legam-700 hover:bg-legam-50 @if(request('sort') === 'payment_method') font-semibold @endif">
                                                    Pembayaran (A-Z)
                                                </a>
                                                <a href="{{ route('dashboard', ['sort' => 'payment_method', 'direction' => 'desc']) }}"
                                                   class="block px-4 py-2 text-sm text-legam-700 hover:bg-legam-50 @if(request('sort') === 'payment_method' && request('direction') === 'desc') font-semibold @endif">
                                                    Pembayaran (Z-A)
                                                </a>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <script>
                                function toggleDeliverySort(event) {
                                    event.stopPropagation();
                                    const menu = document.getElementById('deliverySortMenu');
                                    menu.classList.toggle('hidden');
                                }
                                document.addEventListener('click', function() {
                                    document.getElementById('deliverySortMenu').classList.add('hidden');
                                });
                            </script>
                            <tbody class="divide-y bg-white" style="divide-color: #e8d9c4;">
                                @foreach ($orders as $order)
                                    <tr class="align-top hover:bg-legam-50 transition-colors duration-100">

                                        {{-- ID Order --}}
                                        <td class="whitespace-nowrap px-4 py-4">
                                            <span class="inline-flex items-center justify-center min-w-[3rem] rounded-md px-2.5 py-1 text-xs font-bold text-emas-700"
                                                  style="background: rgba(240,180,41,0.12); border: 1px solid rgba(240,180,41,0.25);">
                                                #{{ $order->id }}
                                            </span>
                                        </td>

                                        {{-- Pelanggan --}}
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-semibold text-legam-900">{{ $order->customer->name }}</p>
                                            <p class="mt-0.5 text-xs text-legam-500 flex items-center gap-1">
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                {{ $order->customer->city->name }}
                                            </p>
                                        </td>

                                        {{-- Pesanan --}}
                                        <td class="px-4 py-4">
                                            <ul class="space-y-1.5">
                                                @foreach ($order->orderItems as $item)
                                                    <li class="text-sm text-legam-700 leading-5">
                                                        <span class="font-semibold text-legam-900 tabular-nums">{{ $item->qty }}&times;</span>
                                                        {{ $item->menu->name }}
                                                        <span class="text-xs text-legam-400 ml-1">({{ $item->menu->category->name }})</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-4 py-4">
                                            @php
                                                $statusClass = match($order->status) {
                                                    'menunggu'  => 'badge-menunggu',
                                                    'diproses'  => 'badge-diproses',
                                                    'dikirim'   => 'badge-dikirim',
                                                    'selesai'   => 'badge-selesai',
                                                    default     => 'badge-menunggu',
                                                };
                                            @endphp
                                            <span class="{{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                                        </td>

                                        {{-- Pengiriman & Pembayaran --}}
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-medium text-legam-900">{{ $order->courier->name }}</p>
                                            <p class="mt-0.5 text-xs text-legam-500">{{ $order->paymentMethod->name }}</p>
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
                            <span class="font-semibold text-legam-800">{{ $orders->firstItem() }}–{{ $orders->lastItem() }}</span>
                            dari
                            <span class="font-semibold text-legam-800">{{ number_format($orders->total()) }}</span>
                            pesanan
                        </p>
                        <nav aria-label="Navigasi halaman pesanan" class="pagination-legam">
                            {{ $orders->links() }}
                        </nav>
                    </div>
                @endif

            </section>

        </div>
    </div>
</x-app-layout>
