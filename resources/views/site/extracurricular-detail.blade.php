@extends('site.layouts.master')

@section('title', isset($extracurricular) && $extracurricular->title
    ? $extracurricular->title . ' | SMKMUH1BLORA'
    : 'Detail Ekstrakurikuler | HSE & K3 Applications')

@push('head')
    <meta name="description" content="{{ Str::limit($extracurricular->meta_description ?? $extracurricular->description ?? $extracurricular->summary ?? $extracurricular->excerpt ?? '', 160) }}">
    
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:title" content="{{ $extracurricular->meta_title ?? Str::limit($extracurricular->title ?? $extracurricular->name ?? config('app.name'), 70) }}">
    <meta property="og:description" content="{{ Str::limit($extracurricular->meta_description ?? $extracurricular->description ?? $extracurricular->summary ?? $extracurricular->excerpt ?? '', 160) }}">
    @if($extracurricular->image('cover'))
        <meta property="og:image" content="{{ $extracurricular->image('cover', 'default') ?? $extracurricular->image('thumbnail', 'default') ?? '' }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $extracurricular->meta_title ?? Str::limit($extracurricular->title ?? $extracurricular->name ?? config('app.name'), 70) }}">
    <meta name="twitter:description" content="{{ Str::limit($extracurricular->meta_description ?? $extracurricular->description ?? $extracurricular->summary ?? $extracurricular->excerpt ?? '', 160) }}">
@endpush}
@section('meta_title',
    $extracurricular->meta_title
        ? $extracurricular->meta_title
        : \Illuminate\Support\Str::limit($extracurricular->title . ' | ' . config('app.name'), 70)
)

@section('meta_description',
    $extracurricular->meta_description
        ? $extracurricular->meta_description
        : \Illuminate\Support\Str::limit(strip_tags($extracurricular->description ?? ''), 160)
)

@if($extracurricular->hasImage('og_image', 'default'))
    @section('og_image', $extracurricular->image('og_image', 'default'))
@endif

@section('content')
@php
    $locale = app()->getLocale();

    $title = $extracurricular->title ?: 'Detail Ekstrakurikuler';
    $imageAlt = trim(strip_tags($title)) ?: 'Cover ekstrakurikuler';

    $coverImage = ($extracurricular->hasImage('cover')
        ? $extracurricular->image('cover', 'default')
        : null) ?: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';

    $advisorPhoto = $extracurricular->hasImage('advisor_photo')
        ? $extracurricular->image('advisor_photo', 'default')
        : null;

    $schedule = $extracurricular->schedule;
    $description = $extracurricular->description;
    $category = $extracurricular->category;
    $location = $extracurricular->location;
    $advisorName = $extracurricular->advisor_name;
    $advisorRole = $extracurricular->advisor_role;

    $proseClass = 'text-slate-600 text-sm sm:text-base leading-relaxed '
        . '[&_p]:my-3 [&_p]:leading-relaxed '
        . '[&_strong]:font-bold [&_strong]:text-slate-900 '
        . '[&_em]:italic '
        . '[&_a]:text-slate-900 [&_a]:underline [&_a]:underline-offset-2 hover:[&_a]:text-slate-900 '
        . '[&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-3 [&_ul]:space-y-1 '
        . '[&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-3 [&_ol]:space-y-1 '
        . '[&_li]:leading-relaxed '
        . '[&_blockquote]:border-l-4 [&_blockquote]:border-[#f7f0ac] [&_blockquote]:pl-4 [&_blockquote]:italic [&_blockquote]:text-slate-500 [&_blockquote]:my-4 '
        . '[&_h2]:font-bold [&_h2]:text-slate-900 [&_h2]:text-xl sm:[&_h2]:text-2xl [&_h2]:mt-6 [&_h2]:mb-3 '
        . '[&_h3]:font-bold [&_h3]:text-slate-900 [&_h3]:text-lg sm:[&_h3]:text-xl [&_h3]:mt-5 [&_h3]:mb-2 '
        . '[&_h4]:font-bold [&_h4]:text-slate-900 [&_h4]:text-base sm:[&_h4]:text-lg [&_h4]:mt-4 [&_h4]:mb-2 '
        . '[&_img]:rounded-xl [&_img]:my-4 [&_img]:shadow-sm '
        . '[&_code]:bg-slate-100 [&_code]:text-slate-900 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded [&_code]:text-sm '
        . '[&_pre]:bg-slate-900 [&_pre]:text-slate-100 [&_pre]:p-4 [&_pre]:rounded-xl [&_pre]:overflow-x-auto [&_pre]:my-4 '
        . '[&_hr]:my-6 [&_hr]:border-slate-200';

    $nextSlug = isset($nextExtracurricular)
        ? ($nextExtracurricular->slug
            ?? optional($nextExtracurricular->slugs->where('locale', $locale)->first())->slug
            ?? optional($nextExtracurricular->slugs->first())->slug)
        : null;
@endphp

<main class="py-10 sm:py-14 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto relative overflow-hidden">

    {{-- Ambient Glow --}}
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-64 h-64 sm:w-96 sm:h-96 bg-[#acf7f0]/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-6 sm:space-y-8">

        {{-- Breadcrumb Navigation --}}
        @include('site.partials.breadcrumb', ['items' => [
            ['label' => 'Ekstrakurikuler', 'url' => url('/ekstrakurikuler')],
            ['label' => $title, 'url' => null],
        ]])

        {{-- Header --}}
        <div class="space-y-3 sm:space-y-4">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @if($schedule)
                    <div class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-900 text-[11px] sm:text-xs font-bold shadow-sm uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $schedule }}
                    </div>
                @endif

                @if($category)
                    <span class="px-3 sm:px-3.5 py-1.5 rounded-full bg-white border border-slate-200 text-[10px] sm:text-xs font-bold text-slate-900 uppercase tracking-wider shadow-sm">
                        {{ $category }}
                    </span>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                {{ $title }}
            </h1>
        </div>

        {{-- Cover --}}
        @if($coverImage)
            <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100 aspect-[16/9]">
                <img src="{{ $coverImage }}" alt="{{ $imageAlt }}"
                    loading="eager" fetchpriority="high" decoding="async"
                    class="w-full h-full object-cover" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}">
            </div>
        @endif

        {{-- Info Grid --}}
        @if($advisorName || $location || $schedule)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">

                @if($advisorName)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl overflow-hidden bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                            @if($advisorPhoto)
                                <img src="{{ $advisorPhoto }}" alt="{{ $advisorName }}" class="w-full h-full object-cover" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}">
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                                {{ $advisorRole ?: 'Pembina' }}
                            </p>
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $advisorName }}</p>
                        </div>
                    </div>
                @endif

                @if($location)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Lokasi</p>
                            <p class="text-sm font-bold text-slate-900">{{ $location }}</p>
                        </div>
                    </div>
                @endif

                @if($schedule)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jadwal</p>
                            <p class="text-sm font-bold text-slate-900">{{ $schedule }}</p>
                        </div>
                    </div>
                @endif

            </div>
        @endif

        {{-- Konten WYSIWYG --}}
        @if($description)
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                <div class="{{ $proseClass }}">
                    {!! safe_html($description) !!}
                </div>
            </div>
        @endif

        {{-- Blok Tambahan (karena block editor aktif) --}}
        @if($extracurricular->blocks->count() > 0)
            <div class="space-y-10 sm:space-y-12">
                {!! $extracurricular->renderBlocks() !!}
            </div>
        @endif

        {{-- Navigasi Next --}}
        @if(isset($nextExtracurricular) && $nextSlug)
            <div class="pt-4 sm:pt-6 border-t border-slate-200 flex items-center justify-end">
                <a href="{{ route('extracurricular.show', $nextSlug) }}"
                    data-analytics="extracurricular-next-click"
                    data-extracurricular-id="{{ $nextExtracurricular->id }}"
                    class="group inline-flex items-center gap-3 px-4 sm:px-6 py-3 sm:py-3.5 rounded-2xl bg-white border border-slate-200 hover:border-[#f0acf7] hover:bg-slate-50 text-slate-700 transition-all duration-300 shadow-sm">
                    <div class="text-right min-w-0">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ekstrakurikuler Selanjutnya</span>
                        <span class="block text-xs sm:text-sm font-bold text-slate-900 group-hover:text-slate-950 transition-colors max-w-[180px] sm:max-w-xs truncate">
                            {{ $nextExtracurricular->title }}
                        </span>
                    </div>
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#acf7f0]/30 border border-[#f0acf7] group-hover:border-[#f0acf7] group-hover:bg-[#acf7f0] flex items-center justify-center text-slate-900 shrink-0 transition-all duration-300">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            </div>
        @endif

    </div>
</main>
@endsection