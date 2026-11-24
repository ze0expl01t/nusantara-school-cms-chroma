@extends('site.layouts.master')

@section('title', isset($jobVacancy) && $jobVacancy->title
    ? $jobVacancy->title . ' | SMKMUH1BLORA'
    : 'Detail Lowongan | SMKMUH1BLORA')

@section('meta_title',
    $jobVacancy->meta_title
        ? $jobVacancy->meta_title
        : \Illuminate\Support\Str::limit($jobVacancy->title . ' | ' . config('app.name'), 70)
)

@section('meta_description',
    $jobVacancy->meta_description
        ? $jobVacancy->meta_description
        : \Illuminate\Support\Str::limit(strip_tags($jobVacancy->excerpt ?? $jobVacancy->description ?? ''), 160)
)

@if($jobVacancy->hasImage('og_image', 'default'))
    @section('og_image', $jobVacancy->image('og_image', 'default'))
@endif

@section('content')
@php
    $locale = app()->getLocale();
    $title = $jobVacancy->title ?: 'Detail Lowongan';
    $imageAlt = trim(strip_tags($title)) ?: 'Cover lowongan';

    $coverImage = ($jobVacancy->hasImage('cover') ? $jobVacancy->image('cover', 'default') : null) ?: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';
    $logoImage = $jobVacancy->hasImage('company_logo') ? $jobVacancy->image('company_logo', 'default') : null;

    $typeLabels = [
        'full-time' => 'Full Time',
        'part-time' => 'Part Time',
        'kontrak' => 'Kontrak',
        'magang' => 'Magang / Internship',
        'freelance' => 'Freelance',
    ];
    $typeLabel = $typeLabels[$jobVacancy->job_type] ?? null;
    $isExpired = $jobVacancy->deadline && $jobVacancy->deadline->isPast();

    $proseClass = 'text-slate-600 text-sm sm:text-base leading-relaxed '
        . '[&_p]:my-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-3 [&_ul]:space-y-1 '
        . '[&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-3 [&_ol]:space-y-1 '
        . '[&_strong]:font-bold [&_strong]:text-slate-900 '
        . '[&_a]:text-slate-900 [&_a]:underline';
@endphp

<main class="py-10 sm:py-14 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto relative overflow-hidden">
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-64 h-64 sm:w-96 sm:h-96 bg-blue-500/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-6 sm:space-y-8">
        @include('site.partials.breadcrumb', ['items' => [
            ['label' => 'Bursa Kerja', 'url' => url('/bursa-kerja')],
            ['label' => $title, 'url' => null],
        ]])

        <div class="space-y-3 sm:space-y-4">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @if($typeLabel)
                    <div class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-[11px] sm:text-xs font-bold shadow-sm uppercase tracking-wider">
                        {{ $typeLabel }}
                    </div>
                @endif
                @if($isExpired)
                    <span class="px-3 sm:px-3.5 py-1.5 rounded-full bg-red-50 border border-red-200 text-red-700 text-[10px] sm:text-xs font-bold uppercase tracking-wider shadow-sm">
                        Lowongan Ditutup
                    </span>
                @endif
            </div>

            @if($jobVacancy->company_name)
                <div class="flex items-center gap-3">
                    @if($logoImage)
                        <img src="{{ $logoImage }}" alt="{{ $jobVacancy->company_name }}" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}""
                             class="w-14 h-14 rounded-xl object-contain bg-white border border-slate-200 p-1.5 flex-shrink-0">
                    @endif
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Perusahaan</p>
                        <p class="text-base font-bold text-slate-900">{{ $jobVacancy->company_name }}</p>
                    </div>
                </div>
            @endif

            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                {{ $title }}
            </h1>

            @if($jobVacancy->excerpt)
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed">{{ $jobVacancy->excerpt }}</p>
            @endif
        </div>

        @if($coverImage)
            <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100 aspect-[16/9]">
                <img src="{{ $coverImage }}" alt="{{ $imageAlt }}" loading="eager" fetchpriority="high" decoding="async" class="w-full h-full object-cover" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}">
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @if($jobVacancy->location)
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Lokasi</p>
                        <p class="text-sm font-bold text-slate-900">{{ $jobVacancy->location }}</p>
                    </div>
                </div>
            @endif

            @if($jobVacancy->salary_range)
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Gaji</p>
                        <p class="text-sm font-bold text-slate-900">{{ $jobVacancy->salary_range }}</p>
                    </div>
                </div>
            @endif

            @if($jobVacancy->deadline)
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-xl {{ $isExpired ? 'bg-red-50 border-red-100 text-red-600' : 'bg-[#acf7f0]/30 border-[#acf7f0] text-slate-900' }} border flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Batas Lamaran</p>
                        <p class="text-sm font-bold {{ $isExpired ? 'text-red-600' : 'text-slate-900' }}">{{ $jobVacancy->deadline->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
            @endif
        </div>

        @if($jobVacancy->requirements)
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-4">Persyaratan</h2>
                <div class="{{ $proseClass }}">{!! safe_html($jobVacancy->requirements) !!}</div>
            </div>
        @endif

        @if($jobVacancy->description)
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-4">Deskripsi Pekerjaan</h2>
                <div class="{{ $proseClass }}">{!! safe_html($jobVacancy->description) !!}</div>
            </div>
        @endif

        @if($jobVacancy->apply_url || $jobVacancy->apply_email || $jobVacancy->contact_person)
            <div class="bg-gradient-to-br from-[#acf7f0]/30 to-[#f0acf7]/30 border border-[#f0acf7] rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-4">Cara Melamar</h2>
                <div class="space-y-4">
                    @if($jobVacancy->apply_url)
                        <a href="{{ $jobVacancy->apply_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold shadow-lg shadow-slate-950/15 transition-all transform hover:-translate-y-0.5">
                            Lamar Sekarang
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        @if($jobVacancy->apply_email)
                            <div class="flex items-center gap-2">
                                <span class="text-slate-600">Email:</span>
                                <a href="mailto:{{ $jobVacancy->apply_email }}" class="font-semibold text-slate-900 hover:underline">{{ $jobVacancy->apply_email }}</a>
                            </div>
                        @endif
                        @if($jobVacancy->contact_person)
                            <div class="flex items-center gap-2">
                                <span class="text-slate-600">Contact:</span>
                                <span class="font-semibold text-slate-900">{{ $jobVacancy->contact_person }}</span>
                            </div>
                        @endif
                        @if($jobVacancy->contact_phone)
                            <div class="flex items-center gap-2">
                                <span class="text-slate-600">Telp:</span>
                                <a href="tel:{{ $jobVacancy->contact_phone }}" class="font-semibold text-slate-900 hover:underline">{{ $jobVacancy->contact_phone }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($jobVacancy->blocks->count() > 0)
            <div class="space-y-10 sm:space-y-12">{!! $jobVacancy->renderBlocks() !!}</div>
        @endif

        @if($jobVacancy->hasImage('gallery'))
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm">
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-4">Galeri</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($jobVacancy->images('gallery', 'default') as $image)
                        <div class="aspect-video rounded-xl overflow-hidden bg-slate-100">
                            <img src="{{ $image }}" alt="Galeri {{ $title }}" loading="lazy" decoding="async" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(isset($relatedVacancies) && $relatedVacancies->count() > 0)
            <div class="pt-6 border-t border-slate-200">
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-4">Lowongan Lainnya</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($relatedVacancies as $related)
                        @php
                            $relatedSlug = $related->slug
                                ?? optional($related->slugs->where('locale', $locale)->first())->slug
                                ?? optional($related->slugs->first())->slug;
                        @endphp
                        <a href="{{ route('jobVacancy.show', $relatedSlug) }}"
                           class="group block bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-[#f0acf7]/50 transition-all">
                            @if($related->company_name)
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ $related->company_name }}</p>
                            @endif
                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-slate-950 transition line-clamp-2">{{ $related->title }}</h3>
                            @if($related->location)
                                <p class="text-xs text-slate-500 mt-2">{{ $related->location }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</main>
@endsection
