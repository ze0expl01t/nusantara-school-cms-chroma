@php
    $safeInput = function ($block, $field, $default = null) {
        $value = $block->input($field);
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? $default;
        }
        return filled($value) ? $value : $default;
    };

    $badgeText      = $safeInput($block, 'badge_text', 'Bursa Kerja');
    $titleMain      = $safeInput($block, 'title_main', 'Lowongan Kerja');
    $titleHighlight = $safeInput($block, 'title_highlight', '& Karier');
    $subtitle       = $safeInput($block, 'subtitle');
    $limit          = (int) $safeInput($block, 'limit', 6);
    $filterType     = $safeInput($block, 'filter_type', 'all');
    $onlyActive     = (bool) $safeInput($block, 'only_active', true);
    $showBtn        = (bool) $safeInput($block, 'show_button', false);
    $btnText        = $safeInput($block, 'button_text', 'Lihat Semua Lowongan');

    $query = \App\Models\JobVacancy::where('published', true)
        ->with(['slugs', 'medias'])
        ->orderBy('position');

    if ($filterType !== 'all') {
        $query->where('job_type', $filterType);
    }

    if ($onlyActive) {
        $query->where(function ($q) {
            $q->whereNull('deadline')
              ->orWhere('deadline', '>=', now()->toDateString());
        });
    }

    $items = $query->limit($limit > 0 ? $limit : 6)->get();

    $typeLabels = [
        'full-time' => 'Full Time',
        'part-time' => 'Part Time',
        'kontrak' => 'Kontrak',
        'magang' => 'Magang',
        'freelance' => 'Freelance',
    ];

    // ═══ Prefetch URL lowongan terdekat (native) ═══
    $prefetchUrls = $items->take(2)->map(function ($item) {
        $slug = $item->slug
            ?? optional($item->slugs->where('locale', app()->getLocale())->first())->slug
            ?? optional($item->slugs->first())->slug;
        return $slug ? route('jobVacancy.show', $slug) : null;
    })->filter()->values();
@endphp

{{-- ═══ Native prefetch untuk lowongan populer ═══ --}}
@push('head')
    @foreach($prefetchUrls as $url)
        <link rel="prefetch" href="{{ $url }}">
    @endforeach
@endpush

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>

    {{-- Ambient glow — blur dikurangi untuk hemat GPU paint --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[80px] rounded-full pointer-events-none [transform:translate3d(-50%,-50%,0)]" aria-hidden="true"></div>

    <div class="relative z-10 space-y-8 sm:space-y-10">
        <div class="flex flex-col items-center text-center space-y-3 pb-4 sm:pb-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-medium text-slate-700">
                <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none" aria-hidden="true"></span>
                {{ $badgeText }}
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2 [text-wrap:balance]">
                {{ $titleMain }}
                @if($titleHighlight)
                    <span class="text-slate-900">{{ $titleHighlight }}</span>
                @endif
            </h2>
            <div class="w-20 h-1 bg-gradient-to-r from-transparent via-[#acf7f0] to-transparent rounded-full" aria-hidden="true"></div>
            @if($subtitle)
                <p class="text-base md:text-lg text-slate-600 max-w-2xl px-2 pt-2">{{ $subtitle }}</p>
            @endif
        </div>

        @if($items->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach($items as $item)
                    @php
                        $hasLogo  = $item->hasImage('company_logo');
                        $hasCover = $item->hasImage('cover');

                        $slug = $item->slug
                            ?? optional($item->slugs->where('locale', app()->getLocale())->first())->slug
                            ?? optional($item->slugs->first())->slug;

                        $typeLabel = $typeLabels[$item->job_type] ?? null;
                        $isExpired = $item->deadline && $item->deadline->isPast();
                        $isFirst   = $loop->first;
                    @endphp

                    <a href="{{ route('jobVacancy.show', $slug) }}"
                       aria-label="Lihat detail lowongan: {{ $item->title }}"
                       class="group relative bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:border-[#f0acf7]/50 hover:-translate-y-1 transition-[box-shadow,border-color,transform] duration-300 flex flex-col h-full motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style] [transform:translateZ(0)]">

                        <span class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 z-10 motion-reduce:transition-none motion-reduce:group-hover:scale-x-0" aria-hidden="true"></span>

                        @if($hasCover)
                            <div class="aspect-video overflow-hidden bg-slate-100">
                                {!! twill_proxy_img(
                                    $item,
                                    'cover',
                                    [400, 800, 1200],
                                    '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
                                    $item->title,
                                    [],
                                    null,
                                    [
                                        'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                        'width' => '1200',
                                        'height' => '675',
                                        'loading' => $isFirst ? 'eager' : 'lazy',
                                        'fetchpriority' => $isFirst ? 'high' : 'auto',
                                        'decoding' => 'async',
                                    ]
                                ) !!}
                            </div>
                        @else
                            <div class="aspect-video bg-gradient-to-br from-[#acf7f0] to-[#f0acf7] flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="p-5 sm:p-6 flex flex-col flex-grow">
                            <div class="flex items-start gap-3 mb-3">
                                @if($hasLogo)
                                    {!! twill_proxy_img(
                                        $item,
                                        'company_logo',
                                        [96, 192],
                                        '48px',
                                        $item->company_name ?: 'Logo perusahaan',
                                        [],
                                        null,
                                        [
                                            'class' => 'w-12 h-12 rounded-xl object-contain bg-slate-50 border border-slate-200 p-1 flex-shrink-0',
                                            'width' => '96',
                                            'height' => '96',
                                            'loading' => 'lazy',
                                            'decoding' => 'async',
                                        ]
                                    ) !!}
                                @endif
                                <div class="flex-1 min-w-0">
                                    @if($item->company_name)
                                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">
                                            {{ $item->company_name }}
                                        </p>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        @if($typeLabel)
                                            <span class="inline-flex px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold uppercase tracking-wider border border-blue-100">{{ $typeLabel }}</span>
                                        @endif
                                        @if($isExpired)
                                            <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-[10px] font-bold uppercase tracking-wider border border-red-100">Ditutup</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <h3 class="text-lg font-extrabold text-slate-900 leading-snug mb-2 group-hover:text-slate-950 transition-colors duration-300 line-clamp-2">
                                {{ $item->title }}
                            </h3>

                            @if($item->location)
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    <span class="text-xs text-slate-600 truncate">{{ $item->location }}</span>
                                </div>
                            @endif

                            @if($item->excerpt)
                                <p class="text-sm text-slate-600 leading-relaxed line-clamp-3 flex-grow">
                                    {{ strip_tags($item->excerpt) }}
                                </p>
                            @endif

                            @if($item->salary_range || $item->deadline)
                                <div class="flex flex-wrap items-center gap-3 pt-4 mt-4 border-t border-slate-100 text-xs">
                                    @if($item->salary_range)
                                        <span class="font-semibold text-slate-900">{{ $item->salary_range }}</span>
                                    @endif
                                    @if($item->deadline)
                                        <span class="text-slate-500">Deadline: <strong class="{{ $isExpired ? 'text-red-600' : 'text-slate-700' }}">{{ $item->deadline->translatedFormat('d M Y') }}</strong></span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            @if($showBtn)
                <div class="flex justify-center">
                    <a href="{{ url('/bursa-kerja') }}"
                       class="inline-flex items-center gap-2 px-6 sm:px-8 py-3.5 sm:py-4 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold shadow-lg shadow-slate-950/15 transition-[transform,background-color,box-shadow] duration-300 transform hover:-translate-y-1 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                        {{ $btnText }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            @endif
        @else
            <div class="text-center py-8 sm:py-10 bg-white border border-slate-200 rounded-3xl shadow-sm">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <p class="text-slate-500 text-sm font-medium">Belum ada lowongan kerja yang dipublikasikan.</p>
            </div>
        @endif
    </div>
</section>
