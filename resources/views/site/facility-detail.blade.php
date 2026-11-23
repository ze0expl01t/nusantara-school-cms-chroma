@extends('site.layouts.master')

@section('title', isset($facility) && $facility->title
    ? $facility->title . ' | SMKMUH1BLORA'
    : 'Detail Fasilitas | HSE & K3 Applications')

@push('head')
    <meta name="description" content="{{ Str::limit($facility->meta_description ?? $facility->description ?? $facility->summary ?? $facility->excerpt ?? '', 160) }}">
    
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:title" content="{{ $facility->meta_title ?? Str::limit($facility->title ?? $facility->name ?? config('app.name'), 70) }}">
    <meta property="og:description" content="{{ Str::limit($facility->meta_description ?? $facility->description ?? $facility->summary ?? $facility->excerpt ?? '', 160) }}">
    @if($facility->image('cover'))
        <meta property="og:image" content="{{ $facility->image('cover', 'default') ?? $facility->image('thumbnail', 'default') ?? '' }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $facility->meta_title ?? Str::limit($facility->title ?? $facility->name ?? config('app.name'), 70) }}">
    <meta name="twitter:description" content="{{ Str::limit($facility->meta_description ?? $facility->description ?? $facility->summary ?? $facility->excerpt ?? '', 160) }}">
@endpush}
@section('meta_title',
    $facility->meta_title
        ? $facility->meta_title
        : \Illuminate\Support\Str::limit($facility->title . ' | ' . config('app.name'), 70)
)

@section('meta_description',
    $facility->meta_description
        ? $facility->meta_description
        : \Illuminate\Support\Str::limit(strip_tags($facility->description ?? ''), 160)
)

@if($facility->hasImage('og_image', 'default'))
    @section('og_image', $facility->image('og_image', 'default'))
@endif

@section('content')
@php
    $locale = app()->getLocale();

    $title = $facility->title ?: 'Detail Fasilitas';
    $imageAlt = trim(strip_tags($title)) ?: 'Cover fasilitas';

    $coverImage = $facility->hasImage('cover')
        ? $facility->image('cover', 'default')
        : null;

    $badge = $facility->badge;
    $statLabel = $facility->stat_label;
    $statValue = $facility->stat_value;
    $capacityLabel = $facility->capacity_label;
    $capacityValue = $facility->capacity_value;
    $icon = $facility->icon;
    $description = $facility->description;

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
        . '[&_img]:rounded-xl [&_img]:my-4 [&_img]:shadow-sm';

    $nextSlug = isset($nextFacility)
        ? ($nextFacility->slug
            ?? optional($nextFacility->slugs->where('locale', $locale)->first())->slug
            ?? optional($nextFacility->slugs->first())->slug)
        : null;
@endphp

<main class="py-10 sm:py-14 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto relative overflow-hidden">

    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-64 h-64 sm:w-96 sm:h-96 bg-[#acf7f0]/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-6 sm:space-y-8">

        {{-- Breadcrumb Navigation --}}
        @include('site.partials.breadcrumb', ['items' => [
            ['label' => 'Fasilitas', 'url' => url('/fasilitas')],
            ['label' => $title, 'url' => null],
        ]])

        {{-- Header --}}
        <div class="space-y-3 sm:space-y-4">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @if($badge)
                    <div class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-900 text-[11px] sm:text-xs font-bold shadow-sm uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#acf7f0]"></span>
                        {{ $badge }}
                    </div>
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
                    class="w-full h-full object-cover">
            </div>
        @endif

        {{-- Icon Card (Full Width) --}}
        @if($icon)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#f0acf7] flex items-center justify-center text-white shadow-md shadow-slate-950/15">
                    <div class="w-7 h-7 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                        {!! safe_svg($icon) !!}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Fasilitas</p>
                    <p class="text-base font-bold text-slate-900">{{ $title }}</p>
                </div>
            </div>
        @endif

        {{-- Statistik (2 kolom sejajar) --}}
        @if(($statValue !== null && $statValue !== '') || ($capacityValue !== null && $capacityValue !== ''))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">

                {{-- Jumlah --}}
                @if($statValue !== null && $statValue !== '')
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                            {{ $statLabel ?: 'Jumlah' }}
                        </p>
                        <p class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-none">
                            {{ $statValue }}
                        </p>
                    </div>
                @endif

                {{-- Daya Tampung --}}
                @if($capacityValue !== null && $capacityValue !== '')
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                            {{ $capacityLabel ?: 'Daya Tampung' }}
                        </p>
                        <p class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-none">
                            {{ $capacityValue }}
                        </p>
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

        {{-- Blok Tambahan --}}
        @if($facility->blocks->count() > 0)
            <div class="space-y-10 sm:space-y-12">
                {!! $facility->renderBlocks() !!}
            </div>
        @endif

        {{-- Next --}}
        @if(isset($nextFacility) && $nextSlug)
            <div class="pt-4 sm:pt-6 border-t border-slate-200 flex items-center justify-end">
                <a href="{{ route('facility.show', $nextSlug) }}"
                    class="group inline-flex items-center gap-3 px-4 sm:px-6 py-3 sm:py-3.5 rounded-2xl bg-white border border-slate-200 hover:border-[#f0acf7] hover:bg-slate-50 text-slate-700 transition-all duration-300 shadow-sm">
                    <div class="text-right min-w-0">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Fasilitas Selanjutnya</span>
                        <span class="block text-xs sm:text-sm font-bold text-slate-900 group-hover:text-slate-950 transition-colors max-w-[180px] sm:max-w-xs truncate">
                            {{ $nextFacility->title }}
                        </span>
                    </div>
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#acf7f0]/30 border border-[#f0acf7] group-hover:border-[#f0acf7] group-hover:bg-[#acf7f0] flex items-center justify-center text-slate-900 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            </div>
        @endif

    </div>
</main>
@endsection