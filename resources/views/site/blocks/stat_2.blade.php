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

    $badgeText      = $safeInput($block, 'badge_text');
    $titleMain      = $safeInput($block, 'title_main', 'Kompetensi Pedagogik dan');
    $titleHighlight = $safeInput($block, 'title_highlight', 'Profesional Staf');
    $description    = $safeInput($block, 'description');

    $children    = $block->children;
    $statItems   = $children ? $children->where('type', 'stat_2_item')       : collect();
    $imageItems  = $children ? $children->where('type', 'stat_2_image_item') : collect();

    $images = $imageItems->map(function ($imgItem) {
        if ($imgItem->hasImage('gallery_image', 'desktop')) {
            return $imgItem->image('gallery_image', 'desktop');
        }
        if ($imgItem->hasImage('gallery_image')) {
            return $imgItem->image('gallery_image');
        }
        return null;
    })->filter()->values();

    $imageCount = $images->count();
    $pageTitle = trim(strip_tags($titleMain . ' ' . $titleHighlight)) ?: 'Galeri';
@endphp

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto overflow-hidden [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>
    <div class="space-y-6">

        <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-6 md:p-10 shadow-sm relative overflow-hidden [contain:layout_style]">

            {{-- Ambient glow — blur dikurangi untuk hemat GPU paint --}}
            <div class="absolute top-0 right-0 w-64 h-64 sm:w-96 sm:h-96 bg-[#acf7f0]/5 blur-[60px] rounded-full pointer-events-none" aria-hidden="true"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 sm:w-80 sm:h-80 bg-[#f0acf7]/5 blur-[60px] rounded-full pointer-events-none" aria-hidden="true"></div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 lg:gap-12 items-center relative z-10">

                {{-- ═══ KOLOM KIRI: Teks & Progress Bar ═══ --}}
                <div class="lg:col-span-7 space-y-4">

                    @if($badgeText)
                        <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-[11px] sm:text-xs md:text-sm font-semibold tracking-wider text-slate-700 uppercase shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none" aria-hidden="true"></span>
                            {{ $badgeText }}
                        </div>
                    @endif

                    @if($titleMain || $titleHighlight)
                        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-[1.15] [text-wrap:balance]">
                            {{ $titleMain }}
                            @if($titleHighlight)
                                <span class="block text-slate-900 mt-1 sm:mt-1.5">{{ $titleHighlight }}</span>
                            @endif
                        </h2>
                    @endif

                    @if($description)
                        <p class="text-sm md:text-base text-slate-600 leading-relaxed">
                            {{ $description }}
                        </p>
                    @endif

                    {{-- Progress Bar Statistik --}}
                    @if($statItems->count() > 0)
                        <div
                            x-data="{
                                animated: false,
                                reducedMotion: false,
                                init() {
                                    this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                                    if (this.reducedMotion) {
                                        this.animated = true;
                                        return;
                                    }
                                    const observer = new IntersectionObserver((entries) => {
                                        entries.forEach((entry) => {
                                            if (entry.isIntersecting) {
                                                this.animated = true;
                                                observer.disconnect();
                                            }
                                        });
                                    }, { threshold: 0.2 });
                                    observer.observe(this.$el);
                                }
                            }"
                            class="space-y-3 pt-1"
                        >
                            @foreach($statItems as $item)
                                @php
                                    $percentage = min(max((int) $safeInput($item, 'percentage', 90), 0), 100);
                                    $label      = $safeInput($item, 'label', 'Jurusan');
                                @endphp

                                <div class="space-y-1.5 bg-slate-50/80 p-3.5 rounded-2xl border border-slate-200/80 hover:border-[#f0acf7]/40 transition-colors duration-300 shadow-sm motion-reduce:transition-none">
                                    <div class="flex justify-between text-xs md:text-sm font-bold text-slate-800">
                                        <span class="truncate pr-2">{{ $label }}</span>
                                        <span class="text-slate-900 font-extrabold shrink-0">{{ $percentage }}%</span>
                                    </div>

                                    <div class="w-full h-2.5 bg-slate-200/80 rounded-full overflow-hidden p-0.5 border border-slate-200">
                                        <div
                                            class="h-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] rounded-full shadow-[0_0_12px_rgba(172,247,240,0.45)] transition-[width,opacity] duration-1000 ease-out motion-reduce:transition-none [transform:translateZ(0)]"
                                            :class="animated ? 'opacity-100' : 'opacity-0'"
                                            :style="animated ? 'width: {{ $percentage }}%' : 'width: 0%'"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-xs text-slate-400 italic p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50">
                            Belum ada item statistik yang ditambahkan.
                        </div>
                    @endif

                </div>

                {{-- ═══ KOLOM KANAN: Kolase Gambar ═══ --}}
                <div class="lg:col-span-5">
                    @if($imageCount > 0)

                        @if($imageCount === 1)
                            <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-xl shadow-slate-950/10 hover:shadow-slate-950/15 transition-[box-shadow] duration-500 group aspect-[4/3] motion-reduce:transition-none [contain:layout_style] [transform:translateZ(0)]">
                                {!! twill_proxy_img(
                                    $imageItems->first(),
                                    'gallery_image',
                                    [640, 1024, 1536],
                                    '(max-width: 1024px) 100vw, 50vw',
                                    $pageTitle,
                                    [],
                                    null,
                                    [
                                        'class' => 'w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                        'width' => '1536',
                                        'height' => '1152',
                                        'loading' => 'eager',
                                        'fetchpriority' => 'high',
                                        'decoding' => 'async',
                                    ]
                                ) !!}
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/25 via-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none motion-reduce:transition-none" aria-hidden="true"></div>
                            </div>

                        @elseif($imageCount === 2)
                            <div class="space-y-3 sm:space-y-4 pr-4 sm:pr-6">
                                @foreach($imageItems as $index => $imgItem)
                                    <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg shadow-slate-950/10 hover:shadow-slate-950/15 transition-[box-shadow] duration-500 group aspect-[16/9] {{ $index === 1 ? 'translate-x-4 sm:translate-x-6' : '' }} motion-reduce:transition-none motion-reduce:translate-x-0 [contain:layout_style] [transform:translateZ(0)]">
                                        {!! twill_proxy_img(
                                            $imgItem,
                                            'gallery_image',
                                            [400, 800, 1200],
                                            '(max-width: 768px) 100vw, 50vw',
                                            $pageTitle . ' — Gambar ' . ($index + 1),
                                            [],
                                            null,
                                            [
                                                'class' => 'w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                                'width' => '1200',
                                                'height' => '675',
                                                'loading' => $index === 0 ? 'eager' : 'lazy',
                                                'fetchpriority' => $index === 0 ? 'high' : 'auto',
                                                'decoding' => 'async',
                                            ]
                                        ) !!}
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/25 via-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none motion-reduce:transition-none" aria-hidden="true"></div>
                                    </div>
                                @endforeach
                            </div>

                        @else
                            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                @foreach($imageItems as $index => $imgItem)
                                    @php $isLarge = ($index === 0); @endphp

                                    <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg shadow-slate-950/10 hover:shadow-slate-950/15 transition-[box-shadow] duration-500 group w-full h-full {{ $isLarge ? 'col-span-2 aspect-[16/9]' : 'col-span-1 aspect-[4/3]' }} motion-reduce:transition-none [contain:layout_style] [transform:translateZ(0)]">
                                        {!! twill_proxy_img(
                                            $imgItem,
                                            'gallery_image',
                                            [400, 800, 1200],
                                            '(max-width: 640px) 100vw, 50vw',
                                            $pageTitle . ' — Gambar ' . ($index + 1),
                                            [],
                                            null,
                                            [
                                                'class' => 'w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                                'width' => '1200',
                                                'height' => $isLarge ? '675' : '900',
                                                'loading' => $index === 0 ? 'eager' : 'lazy',
                                                'fetchpriority' => $index === 0 ? 'high' : 'auto',
                                                'decoding' => 'async',
                                            ]
                                        ) !!}
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/25 via-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none motion-reduce:transition-none" aria-hidden="true"></div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    @else
                        <div class="grid grid-cols-1 gap-4">
                            <div class="w-full aspect-[4/3] rounded-2xl sm:rounded-3xl bg-slate-50 border border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 text-xs p-6 text-center space-y-2 shadow-sm">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Tambahkan foto melalui galeri repeater admin</span>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</section>
