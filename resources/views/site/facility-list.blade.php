@extends('site.layouts.master')

@section('title', 'Fasilitas | SMKMUH1BLORA')

@section('content')
@php
    $badgeText      = 'Fasilitas & Sarana';
    $titleMain      = 'Sarana';
    $titleHighlight = '& Prasarana';
    $subtitle       = 'Berbagai fasilitas pendukung kegiatan belajar dan aktivitas karyawan di perusahaan.';
@endphp

<main class="py-10 sm:py-14 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden">

    {{-- Efek Ambient Glow --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[150px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-8 sm:space-y-10">

        {{-- Header Section --}}
        <div class="flex flex-col items-center text-center space-y-3 pb-4 sm:pb-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-medium text-slate-700">
                <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none"></span>
                {{ $badgeText }}
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2">
                {{ $titleMain }}
                @if($titleHighlight)
                    <span class="text-slate-900">{{ $titleHighlight }}</span>
                @endif
            </h2>
            <div class="w-20 h-1 bg-gradient-to-r from-transparent via-[#acf7f0] to-transparent rounded-full"></div>
            @if($subtitle)
                <p class="text-base md:text-lg text-slate-600 max-w-2xl px-2 pt-2">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        @if($facilities->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach($facilities as $item)
                    @php
                        $cover = $item->hasImage('cover') ? $item->image('cover', 'default') : null;
                        $slug = $item->slug
                            ?? optional($item->slugs->where('locale', app()->getLocale())->first())->slug
                            ?? optional($item->slugs->first())->slug;
                    @endphp

                    <a href="{{ route('facility.show', $slug) }}"
                       class="group relative bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:border-[#f0acf7]/50 hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">

                        <span class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 z-10"></span>

                        {{-- Icon Box --}}
                        <div class="p-5 sm:p-6 pb-0">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#f0acf7] flex items-center justify-center text-white shadow-md shadow-slate-950/15 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500">
                                    @if($item->icon)
                                        <div class="w-7 h-7 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                            {!! safe_svg($item->icon) !!}
                                        </div>
                                    @else
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0 pt-1">
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mb-1.5 group-hover:text-slate-950 transition-colors line-clamp-2">
                                        {{ $item->title }}
                                    </h3>
                                    @if($item->badge)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#acf7f0]/30 text-slate-900 text-xs font-semibold border border-[#acf7f0]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#acf7f0]"></span>
                                            {{ $item->badge }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Stats + Desc --}}
                        <div class="p-5 sm:p-6 flex-grow flex flex-col">
                            <div class="border-t border-slate-100 mb-4"></div>

                            @if($item->stat_value !== null && $item->stat_value !== '')
                                <div class="flex items-baseline justify-between mb-3">
                                    <span class="text-sm text-slate-500 font-medium">{{ $item->stat_label ?: 'Jumlah:' }}</span>
                                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none">
                                        {{ $item->stat_value }}
                                    </span>
                                </div>
                            @endif

                            @if($item->capacity_value !== null && $item->capacity_value !== '')
                                <div class="flex items-baseline justify-between mb-3">
                                    <span class="text-sm text-slate-500 font-medium">{{ $item->capacity_label ?: 'Daya Tampung:' }}</span>
                                    <span class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-none">{{ $item->capacity_value }}</span>
                                </div>
                            @endif

                            @if($item->description)
                                <p class="text-sm text-slate-600 leading-relaxed mt-auto line-clamp-3">
                                    {{ strip_tags($item->description) }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 sm:py-10 bg-white border border-slate-200 rounded-3xl shadow-sm">
                <p class="text-slate-500 text-sm font-medium">Belum ada fasilitas yang dipublikasikan.</p>
            </div>
        @endif

    </div>
</main>
@endsection
