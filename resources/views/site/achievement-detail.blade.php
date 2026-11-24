@extends('site.layouts.master')

@section('title', isset($achievement) && $achievement->title
    ? $achievement->title . ' | SMKMUH1BLORA'
    : 'Detail Pencapaian | SMKMUH1BLORA')

@section('meta_title',
    $achievement->meta_title
        ? $achievement->meta_title
        : \Illuminate\Support\Str::limit($achievement->title . ' | ' . config('app.name'), 70)
)

@section('meta_description',
    $achievement->meta_description
        ? $achievement->meta_description
        : \Illuminate\Support\Str::limit(strip_tags($achievement->excerpt ?? $achievement->description ?? ''), 160)
)

@if($achievement->hasImage('og_image', 'default'))
    @section('og_image', $achievement->image('og_image', 'default'))
@endif

@section('content')
@php
    $locale = app()->getLocale();

    $title = $achievement->title ?: 'Detail Pencapaian';
    $imageAlt = trim(strip_tags($title)) ?: 'Cover pencapaian';

    $coverImage = ($achievement->hasImage('cover') ? $achievement->image('cover', 'default') : null) ?: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';
    $photoImage = $achievement->hasImage('photo') ? $achievement->image('photo', 'default') : null;
    $certificateImage = $achievement->hasImage('certificate') ? $achievement->image('certificate', 'default') : null;

    $levelLabels = [
        'perusahaan' => 'Tingkat Perusahaan',
        'kecamatan' => 'Tingkat Kecamatan',
        'kabupaten' => 'Tingkat Kabupaten/Kota',
        'provinsi' => 'Tingkat Provinsi',
        'nasional' => 'Tingkat Nasional',
        'internasional' => 'Tingkat Internasional',
    ];

    $rankLabels = [
        'juara-1' => 'Juara 1',
        'juara-2' => 'Juara 2',
        'juara-3' => 'Juara 3',
        'harapan' => 'Juara Harapan',
        'finalis' => 'Finalis',
        'partisipasi' => 'Partisipasi',
    ];

    $levelLabel = $levelLabels[$achievement->level] ?? null;
    $rankLabel = $rankLabels[$achievement->rank] ?? null;

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
        . '[&_img]:rounded-xl [&_img]:my-4 [&_img]:shadow-sm';

    $nextSlug = isset($nextAchievement)
        ? ($nextAchievement->slug
            ?? optional($nextAchievement->slugs->where('locale', $locale)->first())->slug
            ?? optional($nextAchievement->slugs->first())->slug)
        : null;
@endphp

<main class="py-10 sm:py-14 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto relative overflow-hidden">

    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-64 h-64 sm:w-96 sm:h-96 bg-amber-500/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-6 sm:space-y-8">

        @include('site.partials.breadcrumb', ['items' => [
            ['label' => 'Pencapaian', 'url' => url('/pencapaian')],
            ['label' => $title, 'url' => null],
        ]])

        <div class="space-y-3 sm:space-y-4">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @if($rankLabel)
                    <div class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-[11px] sm:text-xs font-bold shadow-sm uppercase tracking-wider">
                        🏆 {{ $rankLabel }}
                    </div>
                @endif

                @if($levelLabel)
                    <span class="px-3 sm:px-3.5 py-1.5 rounded-full bg-white border border-slate-200 text-[10px] sm:text-xs font-bold text-slate-900 uppercase tracking-wider shadow-sm">
                        {{ $levelLabel }}
                    </span>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                {{ $title }}
            </h1>

            @if($achievement->student_name)
                <div class="flex items-center gap-2 text-base sm:text-lg text-slate-700">
                    <svg class="w-5 h-5 text-slate-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-semibold">{{ $achievement->student_name }}</span>
                </div>
            @endif

            @if($achievement->excerpt)
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed">
                    {{ $achievement->excerpt }}
                </p>
            @endif
        </div>

        @if($coverImage)
            <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100 aspect-[16/9]">
                <img src="{{ $coverImage }}" alt="{{ $imageAlt }}"
                    loading="eager" fetchpriority="high" decoding="async"
                    class="w-full h-full object-cover" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}">
            </div>
        @endif

        @if($achievement->achievement_date || $achievement->organizer)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">

                @if($achievement->achievement_date)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tanggal</p>
                            <p class="text-sm font-bold text-slate-900">{{ $achievement->achievement_date->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                @endif

                @if($achievement->organizer)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Penyelenggara</p>
                            <p class="text-sm font-bold text-slate-900">{{ $achievement->organizer }}</p>
                        </div>
                    </div>
                @endif

            </div>
        @endif

        @if($photoImage || $certificateImage)
            <div class="grid md:grid-cols-2 gap-4 sm:gap-5">
                @if($photoImage)
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Foto Karyawan / Tim</p>
                        <div class="rounded-xl overflow-hidden bg-slate-100 aspect-[4/3]">
                            <img src="{{ $photoImage }}" alt="{{ $title }}" loading="lazy" decoding="async" class="w-full h-full object-cover" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}">
                        </div>
                    </div>
                @endif

                @if($certificateImage)
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Sertifikat / Piala</p>
                        <div class="rounded-xl overflow-hidden bg-slate-100 aspect-[3/4]">
                            <img src="{{ $certificateImage }}" alt="Sertifikat {{ $title }}" loading="lazy" decoding="async" class="w-full h-full object-contain" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}">
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if($achievement->description)
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                <div class="{{ $proseClass }}">
                    {!! safe_html($achievement->description) !!}
                </div>
            </div>
        @endif

        @if($achievement->blocks->count() > 0)
            <div class="space-y-10 sm:space-y-12">
                {!! $achievement->renderBlocks() !!}
            </div>
        @endif

        @if($achievement->hasImage('gallery'))
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-4">Galeri</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($achievement->images('gallery', 'default') as $image)
                        <div class="aspect-video rounded-xl overflow-hidden bg-slate-100">
                            <img src="{{ $image }}" alt="Galeri {{ $title }}" loading="lazy" decoding="async"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(isset($nextAchievement) && $nextSlug)
            <div class="pt-4 sm:pt-6 border-t border-slate-200 flex items-center justify-end">
                <a href="{{ route('achievement.show', $nextSlug) }}"
                    class="group inline-flex items-center gap-3 px-4 sm:px-6 py-3 sm:py-3.5 rounded-2xl bg-white border border-slate-200 hover:border-[#f0acf7] hover:bg-slate-50 text-slate-700 transition-all duration-300 shadow-sm">
                    <div class="text-right min-w-0">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pencapaian Selanjutnya</span>
                        <span class="block text-xs sm:text-sm font-bold text-slate-900 group-hover:text-slate-950 transition-colors max-w-[180px] sm:max-w-xs truncate">
                            {{ $nextAchievement->title }}
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
