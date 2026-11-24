@extends('site.layouts.master')

@section('title', 'Ekstrakurikuler | SMKMUH1BLORA')

@section('content')
@php
    // ===== Header Content (hardcode, karena ini page bukan block) =====
    $badgeText      = 'Ekstrakurikuler';
    $titleMain      = 'Pengembangan Diri';
    $titleHighlight = '& Bakat Karyawan';
    $subtitle       = 'Berbagai kegiatan di luar jam pelajaran untuk mengembangkan bakat, minat, dan karakter karyawan.';
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

        @if($extracurriculars->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach($extracurriculars as $item)
                    @php
                        $cover = ($item->hasImage('cover') ? $item->image('cover', 'default') : null) ?: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';
                        $slug = $item->slug
                            ?? optional($item->slugs->where('locale', app()->getLocale())->first())->slug
                            ?? optional($item->slugs->first())->slug;
                    @endphp

                    <a href="{{ route('extracurricular.show', $slug) }}"
                       class="group relative bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:border-[#f0acf7]/50 hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">

                        {{-- Top accent bar muncul saat hover --}}
                        <span class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 z-10"></span>

                        @if($cover)
                            <div class="aspect-video overflow-hidden bg-slate-100">
                                <img src="{{ $cover }}" alt="{{ $item->title }}" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}"
                                     loading="lazy" decoding="async"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                        @else
                            <div class="aspect-video bg-gradient-to-br from-[#f7f0ac] to-[#f0acf7] flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="p-5 sm:p-6 flex flex-col flex-grow">
                            @if($item->category)
                                <span class="inline-flex self-start px-2.5 py-0.5 rounded-full bg-[#acf7f0]/30 text-slate-900 text-[10px] font-bold uppercase tracking-wider border border-[#acf7f0] mb-3">
                                    {{ $item->category }}
                                </span>
                            @endif

                            <h3 class="text-lg font-extrabold text-slate-900 leading-snug mb-2 group-hover:text-slate-950 transition-colors line-clamp-2">
                                {{ $item->title }}
                            </h3>

                            @if($item->schedule)
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-4 h-4 text-slate-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                                        {{ $item->schedule }}
                                    </span>
                                </div>
                            @endif

                            @if($item->description)
                                <p class="text-sm text-slate-600 leading-relaxed line-clamp-3 flex-grow">
                                    {{ strip_tags($item->description) }}
                                </p>
                            @endif

                            @if($item->advisor_name)
                                <div class="flex items-center gap-2 pt-4 mt-4 border-t border-slate-100">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-xs text-slate-500">
                                        <span class="text-slate-400">{{ $item->advisor_role ?: 'Pembina' }}:</span>
                                        <span class="font-semibold text-slate-700">{{ $item->advisor_name }}</span>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 sm:py-10 bg-white border border-slate-200 rounded-3xl shadow-sm">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-slate-500 text-sm font-medium">Belum ada ekstrakurikuler yang dipublikasikan.</p>
            </div>
        @endif

    </div>
</main>
@endsection