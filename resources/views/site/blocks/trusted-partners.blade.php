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

    $label          = $safeInput($block, 'label', 'PARTNERS');
    $titleMain      = $safeInput($block, 'title_main', 'Our');
    $titleHighlight = $safeInput($block, 'title_highlight', 'Trusted');
    $titleSuffix    = $safeInput($block, 'title_suffix', 'Partners');
    $subtitle       = $safeInput($block, 'subtitle', 'Partner dan mitra industri yang bekerja sama dengan PT. Nama Perusahaan.');

    $perPage    = max(1, (int) $safeInput($block, 'per_page', 2));
    $autoplayMs = max(0, (int) $safeInput($block, 'autoplay_delay', 5000));

    // Ambil items & sort
    $items = $block->children ?? collect();

    // Handle kalau children return relation atau plain array
    if ($items instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
        $items = $items->get();
    } elseif (!$items instanceof \Illuminate\Support\Collection) {
        $items = collect($items);
    }

    $items = $items->sortBy(function ($item) use ($safeInput) {
        return (int) $safeInput($item, 'order', 0);
    })->values();

    $totalItems = $items->count();
    $pages      = $totalItems > 0 ? $items->chunk($perPage) : collect();
    $totalPages = $pages->count();

    // ═══ Helper: cek ketersediaan logo (RENAMED — hindari bentrok nama variable) ═══
    $checkHasLogo = function ($item) {
        return $item->hasImage('logo', 'default')
            || $item->hasImage('logo', 'desktop')
            || $item->hasImage('logo', 'mobile')
            || $item->hasImage('logo');
    };
@endphp

{{-- Outer section --}}
<section class="py-8 sm:py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 [content-visibility:auto] [contain-intrinsic-size:700px]" data-block-reveal>

    {{-- Wrapper card: background gelap + rounded --}}
    <div
        x-data="{
            activeSlide: 1,
            totalSlides: {{ $totalPages }},
            isJumping: true,
            isNavigating: false,
            autoplay: null,
            reducedMotion: false,
            isTabVisible: true,
            initialized: false,
            transitionMs: 700,
            autoplayMs: {{ $autoplayMs }},

            init() {
                this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                this.isTabVisible = !document.hidden;

                this.$nextTick(() => {
                    this.setupClone();
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            this.isJumping = false;
                        });
                    });
                    if (!this.reducedMotion && this.totalSlides > 1 && this.autoplayMs > 0) {
                        this.startAutoplay();
                    }
                });

                document.addEventListener('visibilitychange', () => {
                    this.isTabVisible = !document.hidden;
                    if (document.hidden) this.stopAutoplay();
                    else this.startAutoplay();
                });
            },

            setupClone() {
                if (this.initialized || this.totalSlides < 2) return;
                const track = this.$refs.track;
                if (!track) return;
                const slides = Array.from(track.children);
                if (slides.length !== this.totalSlides) return;

                const lastClone = slides[slides.length - 1].cloneNode(true);
                lastClone.setAttribute('aria-hidden', 'true');
                lastClone.setAttribute('inert', '');
                lastClone.querySelectorAll('a, button').forEach(el => el.setAttribute('tabindex', '-1'));

                const firstClone = slides[0].cloneNode(true);
                firstClone.setAttribute('aria-hidden', 'true');
                firstClone.setAttribute('inert', '');
                firstClone.querySelectorAll('a, button').forEach(el => el.setAttribute('tabindex', '-1'));

                track.insertBefore(lastClone, track.firstChild);
                track.appendChild(firstClone);

                this.initialized = true;
            },

            startAutoplay() {
                if (this.reducedMotion || !this.isTabVisible || this.totalSlides < 2 || this.autoplayMs <= 0) return;
                this.stopAutoplay();
                this.autoplay = setInterval(() => this.next(), this.autoplayMs);
            },

            stopAutoplay() {
                if (this.autoplay) {
                    clearInterval(this.autoplay);
                    this.autoplay = null;
                }
            },

            next() {
                if (this.isJumping || this.isNavigating) return;
                this.isNavigating = true;
                this.activeSlide++;
                if (this.activeSlide > this.totalSlides) {
                    setTimeout(() => this.resetPosition(1), this.transitionMs);
                }
                setTimeout(() => { this.isNavigating = false; }, this.transitionMs);
            },

            prev() {
                if (this.isJumping || this.isNavigating) return;
                this.isNavigating = true;
                this.activeSlide--;
                if (this.activeSlide < 1) {
                    setTimeout(() => this.resetPosition(this.totalSlides), this.transitionMs);
                }
                setTimeout(() => { this.isNavigating = false; }, this.transitionMs);
            },

            resetPosition(index) {
                this.isJumping = true;
                this.activeSlide = index;
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        this.isJumping = false;
                    });
                });
            },

            goTo(index) {
                const target = index + 1;
                if (this.activeSlide === target || this.isJumping || this.isNavigating) return;
                this.isNavigating = true;
                this.activeSlide = target;
                this.startAutoplay();
                setTimeout(() => { this.isNavigating = false; }, this.transitionMs);
            }
        }"
        @mouseenter="stopAutoplay()"
        @mouseleave="startAutoplay()"
        @touchstart.passive="stopAutoplay()"
        @touchend.passive="startAutoplay()"
        @focusin="stopAutoplay()"
        @focusout="startAutoplay()"
        role="region"
        aria-label="Trusted Partners"
        aria-roledescription="carousel"
        class="relative bg-slate-900 text-slate-100 rounded-3xl px-5 sm:px-8 lg:px-10 py-8 sm:py-10 lg:py-16 [contain:layout_style]"
    >
        {{-- Ambient Glow — blur dikurangi untuk hemat GPU paint --}}
        <div class="absolute inset-0 overflow-hidden rounded-3xl pointer-events-none" aria-hidden="true">
            <div class="absolute top-1/2 left-0 -translate-y-1/2 w-64 h-64 sm:w-[500px] sm:h-[500px] bg-[#f0acf7]/10 blur-[80px] rounded-full [transform:translate3d(0,-50%,0)]"></div>
        </div>

        <div class="relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 sm:gap-12 lg:gap-8 items-center">

                {{-- Kolom Kiri: Header --}}
                <div class="lg:col-span-4 space-y-5 sm:space-y-6 text-center lg:text-left">
                    @if($label)
                        <div class="inline-flex items-center gap-3">
                            <span class="w-8 h-0.5 bg-[#acf7f0]" aria-hidden="true"></span>
                            <span class="text-[11px] sm:text-xs font-bold tracking-widest text-[#acf7f0] uppercase">{{ $label }}</span>
                        </div>
                    @endif

                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.1]">
                        {{ $titleMain }}
                        @if($titleHighlight)
                            <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-amber-400">
                                {{ $titleHighlight }}
                            </span>
                        @endif
                        @if($titleSuffix)
                            <br>
                            {{ $titleSuffix }}
                        @endif
                    </h2>

                    @if($subtitle)
                        <p class="text-slate-400 text-sm sm:text-base leading-relaxed max-w-md mx-auto lg:mx-0">
                            {{ $subtitle }}
                        </p>
                    @endif

                    @if($totalPages > 1)
                        <div class="flex items-center gap-3 sm:gap-4 pt-2 sm:pt-4 justify-center lg:justify-start">
                            <button
                                @click="prev(); startAutoplay()"
                                type="button"
                                aria-label="Partner sebelumnya"
                                class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:bg-[#f7f0ac] hover:text-white hover:border-[#f0acf7] transition-[background-color,border-color,color] duration-300 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 motion-reduce:transition-none"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button
                                @click="next(); startAutoplay()"
                                type="button"
                                aria-label="Partner selanjutnya"
                                class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:bg-[#f7f0ac] hover:text-white hover:border-[#f0acf7] transition-[background-color,border-color,color] duration-300 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 motion-reduce:transition-none"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Kolom Kanan: Slider --}}
                <div class="lg:col-span-8 w-full">

                    @if($totalItems > 0)

                        <div class="overflow-hidden py-4" aria-live="polite" aria-atomic="false">
                            <div
                                x-ref="track"
                                class="flex will-change-transform [transform:translateZ(0)]"
                                :class="isJumping ? '' : 'transition-transform duration-700 ease-out motion-reduce:transition-none'"
                                :style="'transform: translateX(-' + (activeSlide * 100) + '%)'"
                            >
                                @foreach($pages as $pageIndex => $pageItems)
                                    <div
                                        class="w-full flex-shrink-0"
                                        role="group"
                                        aria-roledescription="slide"
                                        aria-label="Halaman {{ $pageIndex + 1 }} dari {{ $totalPages }}"
                                    >
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 items-stretch">
                                            @foreach($pageItems as $item)
                                                @php
                                                    // ═══ FIX: pakai $checkHasLogo, bukan $hasLogo ═══
                                                    $hasLogo = $checkHasLogo($item);
                                                    $name    = $safeInput($item, 'name', 'Partner');
                                                    $url     = $safeInput($item, 'url', null);
                                                @endphp

                                                {{-- Card: Logo dalam Lingkaran + Nama --}}
                                                @if($url)
                                                    <a href="{{ $url }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       aria-label="{{ $name }}"
                                                       class="group/card flex flex-col items-center justify-center gap-4 py-6 transition-[transform] duration-300 motion-reduce:transition-none"
                                                    >
                                                        {{-- Logo dalam Lingkaran ═══ --}}
                                                        <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center overflow-hidden transition-[border-color,box-shadow,transform] duration-300 group-hover/card:border-[#acf7f0] group-hover/card:scale-105 group-hover/card:shadow-lg group-hover/card:shadow-slate-950/20 motion-reduce:transition-none motion-reduce:group-hover/card:scale-100 [transform:translateZ(0)]">
                                                            @if($hasLogo)
                                                                {!! twill_proxy_img(
                                                                    $item,
                                                                    'logo',
                                                                    [128, 256],
                                                                    '96px',
                                                                    $name,
                                                                    [],
                                                                    null,
                                                                    [
                                                                        'class' => 'w-14 h-14 sm:w-16 sm:h-16 object-contain',
                                                                        'width' => '128',
                                                                        'height' => '128',
                                                                        'loading' => 'lazy',
                                                                        'decoding' => 'async',
                                                                    ]
                                                                ) !!}
                                                            @else
                                                                <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                </svg>
                                                            @endif
                                                        </div>

                                                        {{-- Nama Partner --}}
                                                        <h4 class="text-white font-bold text-sm sm:text-base leading-snug text-center group-hover/card:text-[#acf7f0] transition-colors duration-300 motion-reduce:transition-none">
                                                            {{ $name }}
                                                        </h4>
                                                    </a>
                                                @else
                                                    <div class="flex flex-col items-center justify-center gap-4 py-6 [contain:layout_style]">
                                                        {{-- Logo dalam Lingkaran --}}
                                                        <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center overflow-hidden [transform:translateZ(0)]">
                                                            @if($hasLogo)
                                                                {!! twill_proxy_img(
                                                                    $item,
                                                                    'logo',
                                                                    [128, 256],
                                                                    '96px',
                                                                    $name,
                                                                    [],
                                                                    null,
                                                                    [
                                                                        'class' => 'w-14 h-14 sm:w-16 sm:h-16 object-contain',
                                                                        'width' => '128',
                                                                        'height' => '128',
                                                                        'loading' => 'lazy',
                                                                        'decoding' => 'async',
                                                                    ]
                                                                ) !!}
                                                            @else
                                                                <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                </svg>
                                                            @endif
                                                        </div>

                                                        {{-- Nama Partner --}}
                                                        <h4 class="text-white font-bold text-sm sm:text-base leading-snug text-center">
                                                            {{ $name }}
                                                        </h4>
                                                    </div>
                                                @endif
                                            @endforeach

                                            {{-- Placeholder kalau item kurang dari perPage --}}
                                            @if($pageItems->count() < $perPage)
                                                @for($i = $pageItems->count(); $i < $perPage; $i++)
                                                    <div class="hidden md:block" aria-hidden="true"></div>
                                                @endfor
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Pagination Dots --}}
                        @if($totalPages > 1)
                            <div class="flex justify-center gap-2 mt-6" role="tablist" aria-label="Pilih halaman partner">
                                @foreach($pages as $index => $page)
                                    <button
                                        @click="goTo({{ $index }})"
                                        type="button"
                                        :class="activeSlide === {{ $index + 1 }} ? 'w-8 bg-[#acf7f0]' : 'w-2 bg-slate-700 hover:bg-slate-600'"
                                        class="h-2 rounded-full transition-[width,background-color] duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 motion-reduce:transition-none"
                                        :aria-selected="activeSlide === {{ $index + 1 }} ? 'true' : 'false'"
                                        aria-label="Buka halaman {{ $index + 1 }}"
                                        role="tab"
                                    ></button>
                                @endforeach
                            </div>
                        @endif

                    @else
                        <div class="w-full text-center py-8 sm:py-10 bg-slate-800/40 rounded-2xl border border-dashed border-slate-700">
                            <svg class="w-10 h-10 mx-auto text-slate-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-slate-400 text-sm font-medium">
                                Belum ada partner yang ditambahkan.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
