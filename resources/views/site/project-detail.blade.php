@extends('site.layouts.master')

@section('title', isset($project) && $project->title ? $project->title . ' | SMKMUH1BLORA' : 'Detail Project | HSE & K3 Applications')

@push('head')
    <meta name="description" content="{{ Str::limit($project->meta_description ?? $project->description ?? $project->summary ?? $project->excerpt ?? '', 160) }}">
    
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:title" content="{{ $project->meta_title ?? Str::limit($project->title ?? $project->name ?? config('app.name'), 70) }}">
    <meta property="og:description" content="{{ Str::limit($project->meta_description ?? $project->description ?? $project->summary ?? $project->excerpt ?? '', 160) }}">
    @if($project->image('cover'))
        <meta property="og:image" content="{{ $project->image('cover', 'default') ?? $project->image('thumbnail', 'default') ?? '' }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $project->meta_title ?? Str::limit($project->title ?? $project->name ?? config('app.name'), 70) }}">
    <meta name="twitter:description" content="{{ Str::limit($project->meta_description ?? $project->description ?? $project->summary ?? $project->excerpt ?? '', 160) }}">
@endpush}
@section('meta_title',
    $project->meta_title
        ? $project->meta_title
        : \Illuminate\Support\Str::limit($project->title . ' | ' . config('app.name'), 70)
)

@section('meta_description',
    $project->meta_description
        ? $project->meta_description
        : \Illuminate\Support\Str::limit(strip_tags($project->description ?? ''), 160)
)

@if($project->hasImage('og_image', 'default'))
    @section('og_image', $project->image('og_image', 'default'))
@endif

@section('content')
@php
    // ===== Meta project =====
    $locale = app()->getLocale();

    $projectDate = $project->project_date
        ? \Carbon\Carbon::parse($project->project_date)->locale($locale)->timezone('Asia/Jakarta')
        : null;

    $projectTitle = $project->title ?: 'Detail Project';
    $projectImageAlt = trim(strip_tags($projectTitle)) ?: 'Cover proyek';

    // Cover image
    $coverImage = $project->hasImage('cover')
        ? $project->image('cover', 'default')
        : null;

    // ===== Prose class untuk WYSIWYG =====
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

    // ===== Next project =====
    $nextSlug = isset($nextProject)
        ? ($nextProject->slug
            ?? optional($nextProject->slugs->where('locale', $locale)->first())->slug
            ?? optional($nextProject->slugs->first())->slug)
        : null;
@endphp

<main class="py-8 sm:py-10 lg:py-12 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto relative overflow-hidden">

    {{-- Efek Ambient Glow --}}
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-64 h-64 sm:w-96 sm:h-96 bg-[#acf7f0]/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-5 sm:space-y-6">

        {{-- Breadcrumb Navigation --}}
        @include('site.partials.breadcrumb', ['items' => [
            ['label' => 'Project', 'url' => null],
            ['label' => $projectTitle, 'url' => null],
        ]])

        {{-- Header Project --}}
        <div class="space-y-3">

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @if($projectDate)
                    <div class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-900 text-[11px] sm:text-xs font-semibold shadow-sm">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $projectDate->translatedFormat('F d, Y') }}
                    </div>
                @endif

                @if($project->category)
                    <span class="px-3 sm:px-3.5 py-1.5 rounded-full bg-white border border-slate-200 text-[10px] sm:text-xs font-bold text-slate-900 uppercase tracking-wider shadow-sm">
                        {{ $project->category }}
                    </span>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                {{ $projectTitle }}
            </h1>
        </div>

        {{-- Cover Image --}}
        @if($coverImage)
            <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100 aspect-[16/9]">
                <img
                    src="{{ $coverImage }}"
                    alt="{{ $projectImageAlt }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="w-full h-full object-cover"
                >
            </div>
        @endif

        {{-- Konten WYSIWYG --}}
        @if($project->description)
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-6 md:p-8 shadow-sm">
                <div class="{{ $proseClass }}">
                    {!! safe_html($project->description) !!}
                </div>
            </div>
        @endif

        {{-- Blok Tambahan --}}
        @if($project->blocks->count() > 0)
            <div class="space-y-8 sm:space-y-10">
                {!! $project->renderBlocks() !!}
            </div>
        @endif

        {{-- Tombol Navigasi Next Project --}}
        @if(isset($nextProject) && $nextSlug)
            <div class="pt-4 sm:pt-6 border-t border-slate-200 flex items-center justify-end">
                <a
                    href="{{ route('project.show', $nextSlug) }}"
                    data-analytics="project-next-click"
                    data-project-id="{{ $nextProject->id }}"
                    aria-label="Project selanjutnya: {{ $nextProject->title }}"
                    class="group inline-flex items-center gap-3 px-4 sm:px-5 py-3 rounded-2xl bg-white border border-slate-200 hover:border-[#f0acf7] hover:bg-slate-50 text-slate-700 transition-all duration-300 shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none"
                >
                    <div class="text-right min-w-0">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Project Selanjutnya</span>
                        <span class="block text-xs sm:text-sm font-bold text-slate-900 group-hover:text-slate-950 transition-colors max-w-[180px] sm:max-w-xs truncate">
                            {{ $nextProject->title }}
                        </span>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#acf7f0]/30 border border-[#f0acf7] group-hover:border-[#f0acf7] group-hover:bg-[#acf7f0] flex items-center justify-center text-slate-900 shrink-0 transition-all duration-300 shadow-sm motion-reduce:transition-none">
                        <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            </div>
        @endif

    </div>
</main>
@endsection