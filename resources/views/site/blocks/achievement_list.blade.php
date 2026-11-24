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

    $badgeText      = $safeInput($block, 'badge_text', 'Pencapaian');
    $titleMain      = $safeInput($block, 'title_main', 'Pencapaian');
    $titleHighlight = $safeInput($block, 'title_highlight', '& Penghargaan');
    $subtitle       = $safeInput($block, 'subtitle');
    $limit          = (int) $safeInput($block, 'limit', 6);
    $filterLevel    = $safeInput($block, 'filter_level', 'all');
    $showBtn        = (bool) $safeInput($block, 'show_button', false);
    $btnText        = $safeInput($block, 'button_text', 'Lihat Semua Pencapaian');

    $query = \App\Models\Achievement::where('published', true)->orderBy('achievement_date', 'desc');

    if ($filterLevel !== 'all') {
        $query->where('level', $filterLevel);
    }

    $items = $query->limit($limit > 0 ? $limit : 6)->get();
@endphp

{{-- ═══ Resource hint untuk image proxy (same-domain, tapi tetap berguna untuk early connection) ═══ --}}
@push('head')
    {{-- Preconnect ke domain sendiri untuk image proxy (HTTP/2 early hints) --}}
    <link rel="preconnect" href="{{ url('/') }}" crossorigin>
@endpush

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>

    {{-- Ambient glow — kurangi blur radius untuk hemat GPU paint --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[80px] rounded-full pointer-events-none [transform:translate3d(-50%,-50%,0)]"></div>

    <div class="relative z-10 space-y-8 sm:space-y-10">

        <div class="flex flex-col items-center text-center space-y-3 pb-4 sm:pb-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-medium text-slate-700">
                {{-- animate-pulse: motion-reduce untuk aksesibilitas --}}
                <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none" aria-hidden="true"></span>
                {{ $badgeText }}
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2">
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
                        $cover = ($item->hasImage('cover') ? $item->image('cover', 'default') : null) ?: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';
                        $slug = $item->slug
                            ?? optional($item->slugs->where('locale', app()->getLocale())->first())->slug
                            ?? optional($item->slugs->first())->slug;

                        $levelLabel = [
                            'perusahaan' => 'Perusahaan',
                            'kecamatan' => 'Kecamatan',
                            'kabupaten' => 'Kabupaten',
                            'provinsi' => 'Provinsi',
                            'nasional' => 'Nasional',
                            'internasional' => 'Internasional',
                        ][$item->level] ?? null;

                        $rankLabel = [
                            'juara-1' => 'Juara 1',
                            'juara-2' => 'Juara 2',
                            'juara-3' => 'Juara 3',
                            'harapan' => 'Juara Harapan',
                            'finalis' => 'Finalis',
                            'partisipasi' => 'Partisipasi',
                        ][$item->rank] ?? null;

                        // Card pertama = LCP element (eager load, high priority)
                        $isFirst = $loop->first;
                    @endphp

                    <a href="{{ route('achievement.show', $slug) }}"
                       class="group relative bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:border-[#f0acf7]/50 hover:-translate-y-1 transition-all duration-300 flex flex-col h-full [contain:layout_style] [transform:translateZ(0)]">

                        <span class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 z-10" aria-hidden="true"></span>

                        @if($cover)
                            <div class="aspect-video overflow-hidden bg-slate-100">
                                {!! twill_proxy_img(
                                    $item,
                                    'cover',
                                    [400, 800, 1200],
                                    '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
                                    $item->title,
                                    [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                    [
                                        'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500',
                                        'width' => '1200',
                                        'height' => '675',
                                        'decoding' => 'async',
                                        'loading' => $isFirst ? 'eager' : 'lazy',
                                        'fetchpriority' => $isFirst ? 'high' : 'auto',
                                          'onerror' => "this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}",
                                      ]
                                ) !!}
                            </div>
                        @else
                            <div class="aspect-video bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="p-5 sm:p-6 flex flex-col flex-grow">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                @if($rankLabel)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold uppercase tracking-wider border border-amber-200">
                                        🏆 {{ $rankLabel }}
                                    </span>
                                @endif

                                @if($levelLabel)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full bg-[#acf7f0]/30 text-slate-900 text-[10px] font-bold uppercase tracking-wider border border-[#acf7f0]">
                                        {{ $levelLabel }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-extrabold text-slate-900 leading-snug mb-2 group-hover:text-slate-950 transition-colors line-clamp-2">
                                {{ $item->title }}
                            </h3>

                            @if($item->student_name)
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-slate-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-xs font-semibold text-slate-700 truncate">{{ $item->student_name }}</span>
                                </div>
                            @endif

                            @if($item->excerpt)
                                <p class="text-sm text-slate-600 leading-relaxed line-clamp-3 flex-grow">
                                    {{ strip_tags($item->excerpt) }}
                                </p>
                            @endif

                            @if($item->achievement_date || $item->organizer)
                                <div class="flex items-center gap-2 pt-4 mt-4 border-t border-slate-100 text-xs text-slate-500">
                                    @if($item->achievement_date)
                                        <span class="font-semibold text-slate-700">
                                            {{ $item->achievement_date->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                    @if($item->organizer)
                                        <span>· {{ $item->organizer }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            @if($showBtn)
                <div class="flex justify-center">
                    <a href="{{ url('/pencapaian') }}"
                       class="inline-flex items-center gap-2 px-6 sm:px-8 py-3.5 sm:py-4 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold shadow-lg shadow-slate-950/15 transition-all transform hover:-translate-y-1 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                <p class="text-slate-500 text-sm font-medium">Belum ada pencapaian yang dipublikasikan.</p>
            </div>
        @endif

    </div>
</section>
