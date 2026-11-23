@extends('site.layouts.master')

@section('title', isset($jurusan) && $jurusan->title
    ? $jurusan->title . ' | SMKMUH1BLORA'
    : 'Detail Jurusan | SMKMUH1BLORA')

{{-- SEO META --}}
@section('meta_title',
    $jurusan->meta_title
        ? $jurusan->meta_title
        : \Illuminate\Support\Str::limit($jurusan->title . ' | ' . config('app.name'), 70)
)

@section('meta_description',
    $jurusan->meta_description
        ? $jurusan->meta_description
        : \Illuminate\Support\Str::limit(strip_tags($jurusan->excerpt ?? $jurusan->description ?? ''), 160)
)

@if($jurusan->hasImage('og_image', 'default'))
    @section('og_image', $jurusan->image('og_image', 'default'))
@endif

@section('content')
@php
    $locale = app()->getLocale();

    $title = $jurusan->title ?: 'Detail Jurusan';
    $imageAlt = trim(strip_tags($title)) ?: 'Cover jurusan';

    $coverImage = $jurusan->hasImage('cover')
        ? $jurusan->image('cover', 'default')
        : null;

    $iconImage = $jurusan->hasImage('icon')
        ? $jurusan->image('icon', 'default')
        : null;

    $shortName = $jurusan->short_name;
    $accreditation = $jurusan->accreditation;
    $headOfDepartment = $jurusan->head_of_department;
    $excerpt = $jurusan->excerpt;
    $vision = $jurusan->vision;
    $mission = $jurusan->mission;
    $description = $jurusan->description;

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

    $nextSlug = isset($nextJurusan)
        ? ($nextJurusan->slug
            ?? optional($nextJurusan->slugs->where('locale', $locale)->first())->slug
            ?? optional($nextJurusan->slugs->first())->slug)
        : null;
@endphp

<main class="py-10 sm:py-14 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto relative overflow-hidden">

    {{-- Ambient Glow --}}
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-64 h-64 sm:w-96 sm:h-96 bg-[#acf7f0]/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-6 sm:space-y-8">

        {{-- Breadcrumb Navigation --}}
        @include('site.partials.breadcrumb', ['items' => [
            ['label' => 'Jurusan', 'url' => url('/jurusan')],
            ['label' => $title, 'url' => null],
        ]])

        {{-- Header --}}
        <div class="space-y-3 sm:space-y-4">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @if($shortName)
                    <div class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-900 text-[11px] sm:text-xs font-bold shadow-sm uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        {{ $shortName }}
                    </div>
                @endif

                @if($accreditation && $accreditation !== '-')
                    <span class="px-3 sm:px-3.5 py-1.5 rounded-full bg-white border border-slate-200 text-[10px] sm:text-xs font-bold text-slate-900 uppercase tracking-wider shadow-sm">
                        Akreditasi {{ $accreditation }}
                    </span>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                {{ $title }}
            </h1>

            @if($excerpt)
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed">
                    {{ $excerpt }}
                </p>
            @endif
        </div>

        {{-- Cover --}}
        @if($coverImage)
            <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100 aspect-[16/9]">
                <img src="{{ $coverImage }}" alt="{{ $imageAlt }}"
                    loading="eager" fetchpriority="high" decoding="async"
                    class="w-full h-full object-cover">
            </div>
        @endif

        {{-- Info Grid --}}
        @if($headOfDepartment || $accreditation || $shortName)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">

                @if($headOfDepartment)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl overflow-hidden bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                            @if($iconImage)
                                <img src="{{ $iconImage }}" alt="{{ $title }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                                Kepala Jurusan
                            </p>
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $headOfDepartment }}</p>
                        </div>
                    </div>
                @endif

                @if($shortName)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Singkatan</p>
                            <p class="text-sm font-bold text-slate-900">{{ $shortName }}</p>
                        </div>
                    </div>
                @endif

                @if($accreditation && $accreditation !== '-')
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Akreditasi</p>
                            <p class="text-sm font-bold text-slate-900">{{ $accreditation }}</p>
                        </div>
                    </div>
                @endif

            </div>
        @endif

        {{-- Visi & Misi --}}
        @if($vision || $mission)
            <div class="grid md:grid-cols-2 gap-4 sm:gap-5">
                @if($vision)
                    <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Visi</h2>
                        </div>
                        <div class="{{ $proseClass }}">
                            {!! nl2br(e($vision)) !!}
                        </div>
                    </div>
                @endif

                @if($mission)
                    <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Misi</h2>
                        </div>
                        <div class="{{ $proseClass }}">
                            {!! nl2br(e($mission)) !!}
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
        @if($jurusan->blocks->count() > 0)
            <div class="space-y-10 sm:space-y-12">
                {!! $jurusan->renderBlocks() !!}
            </div>
        @endif

        {{-- Galeri --}}
        @if($jurusan->hasImage('gallery'))
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-4">Galeri</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($jurusan->images('gallery', 'default') as $image)
                        <div class="aspect-video rounded-xl overflow-hidden bg-slate-100">
                            <img src="{{ $image }}" alt="Galeri {{ $title }}"
                                 loading="lazy" decoding="async"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Navigasi Next --}}
        @if(isset($nextJurusan) && $nextSlug)
            <div class="pt-4 sm:pt-6 border-t border-slate-200 flex items-center justify-end">
                <a href="{{ route('jurusan.show', $nextSlug) }}"
                    data-analytics="jurusan-next-click"
                    data-jurusan-id="{{ $nextJurusan->id }}"
                    class="group inline-flex items-center gap-3 px-4 sm:px-6 py-3 sm:py-3.5 rounded-2xl bg-white border border-slate-200 hover:border-[#f0acf7] hover:bg-slate-50 text-slate-700 transition-all duration-300 shadow-sm">
                    <div class="text-right min-w-0">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jurusan Selanjutnya</span>
                        <span class="block text-xs sm:text-sm font-bold text-slate-900 group-hover:text-slate-950 transition-colors max-w-[180px] sm:max-w-xs truncate">
                            {{ $nextJurusan->title }}
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
