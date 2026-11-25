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
    $titleMain      = $safeInput($block, 'title_main');
    $titleHighlight = $safeInput($block, 'title_highlight');
    $description    = $safeInput($block, 'description');

    $children   = $block->children;
    $statItems  = $children ? $children->where('type', 'stat_2_item')       : collect();
    $imageItems = $children ? $children->where('type', 'stat_2_image_item') : collect();

    $imageCount = $imageItems->count();
    $pageTitle  = trim(strip_tags(($titleMain ?? '') . ' ' . ($titleHighlight ?? ''))) ?: ($badgeText ?? '');

    $fallbackCards = [
        'from-[#acf7f0]/55 via-[#f7f0ac]/45 to-[#f0acf7]/55',
        'from-[#f0acf7]/55 via-[#acf7f0]/45 to-[#f7f0ac]/55',
        'from-[#f7f0ac]/60 via-[#f0acf7]/45 to-[#acf7f0]/55',
    ];
@endphp

<section class="py-10 sm:py-14 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto overflow-hidden [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>
    <div class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-xs hover:shadow-md transition-shadow duration-300 relative [contain:layout_style]">

        {{-- Top 3-color global accent strip --}}
        <div class="h-1.5 w-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>

        {{-- Ornamen Overlay Pojok Kanan Atas --}}
        <div class="pointer-events-none select-none absolute -top-16 -right-16 z-0 w-56 h-56 sm:w-72 sm:h-72 rounded-full bg-gradient-to-bl from-[#f0acf7]/30 via-[#acf7f0]/25 to-transparent blur-2xl" aria-hidden="true"></div>
        <svg class="pointer-events-none select-none absolute top-3 right-3 z-0 w-24 h-24 sm:w-28 sm:h-28 text-slate-900/[0.06]" viewBox="0 0 120 120" fill="none" aria-hidden="true">
            <circle cx="88" cy="32" r="44" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4" />
            <circle cx="96" cy="24" r="18" fill="url(#stat2TopRightGrad)" fill-opacity="0.35" />
            <defs>
                <linearGradient id="stat2TopRightGrad" x1="78" y1="6" x2="114" y2="42" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#acf7f0" />
                    <stop offset="1" stop-color="#f0acf7" />
                </linearGradient>
            </defs>
        </svg>

        {{-- Ornamen Overlay Pojok Kanan Bawah --}}
        <div class="pointer-events-none select-none absolute -bottom-16 -right-14 z-0 w-56 h-56 sm:w-72 sm:h-72 rounded-full bg-gradient-to-tl from-[#f7f0ac]/35 via-[#acf7f0]/25 to-transparent blur-2xl" aria-hidden="true"></div>
        <svg class="pointer-events-none select-none absolute bottom-3 right-3 z-0 w-24 h-24 sm:w-28 sm:h-28 text-slate-900/[0.06]" viewBox="0 0 120 120" fill="none" aria-hidden="true">
            <path d="M28 104C28 65.34 59.34 34 98 34" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 5" />
            <circle cx="92" cy="92" r="22" fill="url(#stat2BottomRightGrad)" fill-opacity="0.35" />
            <defs>
                <linearGradient id="stat2BottomRightGrad" x1="70" y1="70" x2="114" y2="114" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#f7f0ac" />
                    <stop offset="1" stop-color="#acf7f0" />
                </linearGradient>
            </defs>
        </svg>

        <div class="relative z-10 p-6 sm:p-8 md:p-10 lg:p-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">

                {{-- Left Column: Header & Progress Metrics --}}
                <div class="lg:col-span-7 space-y-5">
                    @if(filled($badgeText))
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200/90 shadow-2xs text-xs font-extrabold tracking-wider text-slate-950 uppercase">
                            <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></span>
                            <span>{{ $badgeText }}</span>
                        </div>
                    @endif

                    @if(filled($titleMain) || filled($titleHighlight))
                        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-[2.65rem] font-black text-slate-950 tracking-tight leading-[1.14] [text-wrap:balance]">
                            @if(filled($titleMain))<span>{{ $titleMain }}</span>@endif
                            @if(filled($titleHighlight))
                                <span class="block mt-1.5">
                                    <span class="inline-block px-3 py-0.5 rounded-xl bg-gradient-to-r from-[#f7f0ac]/65 via-[#acf7f0]/65 to-[#f0acf7]/65 text-slate-950">
                                        {{ $titleHighlight }}
                                    </span>
                                </span>
                            @endif
                        </h2>
                    @endif

                    @if(filled($description))
                        <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl">
                            {{ $description }}
                        </p>
                    @endif

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
                                    $percentage = min(max((int) $safeInput($item, 'percentage', 0), 0), 100);
                                    $label      = $safeInput($item, 'label');
                                @endphp

                                <div class="group rounded-2xl bg-slate-50/80 hover:bg-white p-4 border border-slate-200/85 hover:border-slate-950/20 shadow-2xs hover:shadow-md transition-all duration-300 motion-reduce:transition-none">
                                    <div class="flex items-center justify-between gap-3 mb-2.5">
                                        @if(filled($label))
                                            <span class="text-xs sm:text-sm font-bold text-slate-950 truncate">
                                                {{ $label }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#acf7f0]/55 border border-[#acf7f0] text-xs sm:text-sm font-black text-slate-950 tabular-nums shrink-0">
                                            {{ $percentage }}%
                                        </span>
                                    </div>

                                    <div class="w-full h-2.5 bg-slate-200/80 rounded-full overflow-hidden p-0.5 border border-slate-200/80">
                                        <div
                                            class="h-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] rounded-full transition-[width,opacity] duration-1000 ease-out motion-reduce:transition-none [transform:translateZ(0)]"
                                            :class="animated ? 'opacity-100' : 'opacity-0'"
                                            :style="animated ? 'width: {{ $percentage }}%' : 'width: 0%'"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Right Column: Gallery Collage with Free Public Image Fallback --}}
                <div class="lg:col-span-5">
                    @if($imageCount > 0)
                        @if($imageCount === 1)
                            @php
                                $firstImg = $imageItems->first();
                                $hasFirst = $firstImg->hasImage('gallery_image', 'desktop') || $firstImg->hasImage('gallery_image');
                                $cardGrad = $fallbackCards[0];
                                $imgKey   = $firstImg->id ?? 1;
                                $fbUrl    = 'https://picsum.photos/seed/smkmuh1-stat2-' . $imgKey . '/1200/900.webp';
                            @endphp
                            <div class="relative rounded-3xl overflow-hidden border border-slate-200/90 shadow-sm hover:shadow-xl transition-all duration-300 group aspect-[4/3] bg-gradient-to-br {{ $cardGrad }} motion-reduce:transition-none">
                                <img
                                    src="{{ $fbUrl }}"
                                    alt="{{ $pageTitle }}"
                                    width="1200"
                                    height="900"
                                    loading="eager"
                                    decoding="async"
                                    onerror="this.style.display='none'"
                                    class="absolute inset-0 z-0 w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                                >
                                @if($hasFirst)
                                    {!! twill_proxy_img(
                                        $firstImg,
                                        'gallery_image',
                                        [640, 1024, 1536],
                                        '(max-width: 1024px) 100vw, 50vw',
                                        $pageTitle,
                                        [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                        [
                                            'class' => 'relative z-10 w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                            'width' => '1536',
                                            'height' => '1152',
                                            'loading' => 'eager',
                                            'fetchpriority' => 'high',
                                            'decoding' => 'async',
                                            'onerror' => "this.style.display='none'",
                                        ]
                                    ) !!}
                                @endif
                            </div>

                        @elseif($imageCount === 2)
                            <div class="space-y-3.5 sm:space-y-4">
                                @foreach($imageItems as $index => $imgItem)
                                    @php
                                        $hasImg   = $imgItem->hasImage('gallery_image', 'desktop') || $imgItem->hasImage('gallery_image');
                                        $cardGrad = $fallbackCards[$index % count($fallbackCards)];
                                        $imgKey   = $imgItem->id ?? ($index + 1);
                                        $fbUrl    = 'https://picsum.photos/seed/smkmuh1-stat2-' . $imgKey . '/1200/675.webp';
                                    @endphp
                                    <div class="relative rounded-3xl overflow-hidden border border-slate-200/90 shadow-sm hover:shadow-xl transition-all duration-300 group aspect-[16/9] bg-gradient-to-br {{ $cardGrad }} motion-reduce:transition-none">
                                        <img
                                            src="{{ $fbUrl }}"
                                            alt="{{ trim($pageTitle . ' ' . ($index + 1)) }}"
                                            width="1200"
                                            height="675"
                                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                            decoding="async"
                                            onerror="this.style.display='none'"
                                            class="absolute inset-0 z-0 w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                                        >
                                        @if($hasImg)
                                            {!! twill_proxy_img(
                                                $imgItem,
                                                'gallery_image',
                                                [400, 800, 1200],
                                                '(max-width: 768px) 100vw, 50vw',
                                                trim($pageTitle . ' ' . ($index + 1)),
                                                [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                                [
                                                    'class' => 'relative z-10 w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                                    'width' => '1200',
                                                    'height' => '675',
                                                    'loading' => $index === 0 ? 'eager' : 'lazy',
                                                    'fetchpriority' => $index === 0 ? 'high' : 'auto',
                                                    'decoding' => 'async',
                                                    'onerror' => "this.style.display='none'",
                                                ]
                                            ) !!}
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                        @else
                            <div class="grid grid-cols-2 gap-3.5 sm:gap-4">
                                @foreach($imageItems as $index => $imgItem)
                                    @php
                                        $hasImg   = $imgItem->hasImage('gallery_image', 'desktop') || $imgItem->hasImage('gallery_image');
                                        $isLarge  = ($index === 0);
                                        $cardGrad = $fallbackCards[$index % count($fallbackCards)];
                                        $imgKey   = $imgItem->id ?? ($index + 1);
                                        $wPx      = 1200;
                                        $hPx      = $isLarge ? 675 : 900;
                                        $fbUrl    = 'https://picsum.photos/seed/smkmuh1-stat2-' . $imgKey . '/' . $wPx . '/' . $hPx . '.webp';
                                    @endphp

                                    <div class="relative rounded-3xl overflow-hidden border border-slate-200/90 shadow-sm hover:shadow-xl transition-all duration-300 group w-full h-full {{ $isLarge ? 'col-span-2 aspect-[16/9]' : 'col-span-1 aspect-[4/3]' }} bg-gradient-to-br {{ $cardGrad }} motion-reduce:transition-none">
                                        <img
                                            src="{{ $fbUrl }}"
                                            alt="{{ trim($pageTitle . ' ' . ($index + 1)) }}"
                                            width="{{ $wPx }}"
                                            height="{{ $hPx }}"
                                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                            decoding="async"
                                            onerror="this.style.display='none'"
                                            class="absolute inset-0 z-0 w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                                        >
                                        @if($hasImg)
                                            {!! twill_proxy_img(
                                                $imgItem,
                                                'gallery_image',
                                                [400, 800, 1200],
                                                '(max-width: 640px) 100vw, 50vw',
                                                trim($pageTitle . ' ' . ($index + 1)),
                                                [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                                [
                                                    'class' => 'relative z-10 w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                                    'width' => (string) $wPx,
                                                    'height' => (string) $hPx,
                                                    'loading' => $index === 0 ? 'eager' : 'lazy',
                                                    'fetchpriority' => $index === 0 ? 'high' : 'auto',
                                                    'decoding' => 'async',
                                                    'onerror' => "this.style.display='none'",
                                                ]
                                            ) !!}
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </div>
</section>
