@extends('site.layouts.master')

@section('title', $keyword
    ? 'Pencarian: ' . $keyword . ' | SMKMUH1BLORA'
    : 'Pencarian | SMKMUH1BLORA')

@section('content')
<main class="py-10 sm:py-14 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto relative overflow-hidden">

    {{-- Ambient Glow --}}
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-64 h-64 sm:w-96 sm:h-96 bg-[#acf7f0]/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-6 sm:space-y-8">

        {{-- Breadcrumb --}}
        @include('site.partials.breadcrumb', ['items' => [
            ['label' => 'Pencarian', 'url' => null],
        ]])

        {{-- Header --}}
        <div class="space-y-4">
            <div class="flex flex-col items-center text-center space-y-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-medium text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none"></span>
                    Pencarian
                </div>

                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                    @if($keyword)
                        Hasil untuk: <span class="text-slate-900">"{{ $keyword }}"</span>
                    @else
                        Cari di <span class="text-slate-900">Website</span>
                    @endif
                </h1>

                <div class="w-20 h-1 bg-gradient-to-r from-transparent via-[#acf7f0] to-transparent rounded-full"></div>

                @if($keyword && $total > 0)
                    <p class="text-sm sm:text-base text-slate-500">
                        Ditemukan <strong class="text-slate-900">{{ $total }}</strong> hasil
                    </p>
                @endif
            </div>

            {{-- Search Form --}}
            <form action="{{ route('search') }}" method="GET" class="max-w-2xl mx-auto">
                <div class="relative bg-white border border-slate-200 rounded-2xl shadow-sm focus-within:border-[#acf7f0] focus-within:shadow-md transition-all">
                    <div class="absolute left-4 sm:left-5 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="q"
                        value="{{ $keyword }}"
                        placeholder="Cari berita, fasilitas, ekstrakurikuler..."
                        class="w-full bg-transparent pl-12 sm:pl-14 pr-28 sm:pr-32 py-3.5 sm:py-4 text-sm sm:text-base text-slate-900 placeholder-slate-400 focus:outline-none rounded-2xl"
                        autofocus
                    >
                    <button
                        type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 text-xs sm:text-sm font-bold transition-all shadow-md shadow-slate-950/15">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cari
                    </button>
                </div>
            </form>
        </div>

        {{-- Results --}}
        @if($keyword && mb_strlen($keyword) < 2)
            {{-- Keyword terlalu pendek --}}
            <div class="text-center py-12 bg-white rounded-3xl border border-slate-200 shadow-sm">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-slate-700 font-semibold mb-1">Kata kunci terlalu pendek</p>
                <p class="text-slate-500 text-sm">Masukkan minimal 2 karakter untuk memulai pencarian.</p>
            </div>

        @elseif($keyword && $results->count() > 0)
            {{-- Ada hasil --}}
            <div class="space-y-3 sm:space-y-4">
                @foreach($results as $item)
                    <a href="{{ $item['url'] }}"
                       class="group block bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-xl hover:border-[#f0acf7]/50 hover:-translate-y-1 transition-all duration-300">

                        <div class="flex items-start gap-4">

                            {{-- Icon --}}
                            <div class="flex-shrink-0 w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900 group-hover:bg-[#acf7f0] group-hover:scale-110 transition-all duration-300">
                                @switch($item['icon'])
                                    @case('news')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                        </svg>
                                        @break
                                    @case('calendar')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        @break
                                    @case('briefcase')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        @break
                                    @case('star')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                        @break
                                    @case('building')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        @break
                                    @default
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                @endswitch
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#acf7f0]/30 text-slate-900 text-[10px] font-bold uppercase tracking-wider border border-[#acf7f0]">
                                        {{ $item['type'] }}
                                    </span>
                                    @if($item['category'])
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                                            {{ $item['category'] }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-slate-950 transition-colors mb-2 line-clamp-2">
                                    {!! preg_replace(
                                        '/' . preg_quote($keyword, '/') . '/i',
                                        '<mark class="bg-[#acf7f0] text-slate-900 px-0.5 rounded">$0</mark>',
                                        e($item['title'])
                                    ) !!}
                                </h3>

                                @if($item['excerpt'])
                                    <p class="text-sm text-slate-600 leading-relaxed line-clamp-2 mb-3">
                                        {!! preg_replace(
                                            '/' . preg_quote($keyword, '/') . '/i',
                                            '<mark class="bg-[#acf7f0] text-slate-900 px-0.5 rounded">$0</mark>',
                                            e($item['excerpt'])
                                        ) !!}
                                    </p>
                                @endif

                                <div class="flex items-center gap-3 text-xs text-slate-400">
                                    @if($item['date'])
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                    <span class="ml-auto inline-flex items-center gap-1 text-slate-900 font-semibold">
                                        Lihat
                                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

        @elseif($keyword && $results->count() === 0)
            {{-- Tidak ada hasil --}}
            <div class="text-center py-12 sm:py-16 bg-white rounded-3xl border border-slate-200 shadow-sm">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <p class="text-slate-900 font-bold text-lg mb-2">
                    Tidak ada hasil untuk "{{ $keyword }}"
                </p>
                <p class="text-slate-500 text-sm max-w-md mx-auto">
                    Coba gunakan kata kunci lain, periksa ejaan, atau gunakan istilah yang lebih umum.
                </p>
            </div>

        @else
            {{-- Belum ada keyword --}}
            <div class="text-center py-12 bg-white rounded-3xl border border-slate-200 shadow-sm">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <p class="text-slate-900 font-bold text-lg mb-2">Mulai Pencarian</p>
                <p class="text-slate-500 text-sm max-w-md mx-auto">
                    Masukkan kata kunci di kolom pencarian di atas untuk mencari berita, fasilitas, ekstrakurikuler, dan lainnya.
                </p>
            </div>
        @endif

    </div>
</main>
@endsection
