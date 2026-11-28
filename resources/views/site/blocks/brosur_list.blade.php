@php
    use App\Models\Brosur;

    $safeInput = function ($block, $field, $default = null) {
        if (!$block || !method_exists($block, 'input')) {
            return $default;
        }
        $value = $block->input($field);
        if (is_array($value)) {
            $picked = $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? (count($value) ? reset($value) : null);
            return filled($picked) ? $picked : $default;
        }
        return filled($value) ? $value : $default;
    };

    $sectionTitle = $safeInput($block, 'title', 'Katalog & Brosur');
    $subtitle     = $safeInput($block, 'subtitle');
    $limit        = max(1, (int) $safeInput($block, 'limit', 12));

    $brosurs = Brosur::where('published', true)
        ->with(['slugs', 'medias', 'files'])
        ->orderBy('brosur_date', 'desc')
        ->take($limit)
        ->get();

    $locale = app()->getLocale();
    $prefetchUrls = $brosurs->take(2)->map(function ($brosur) use ($locale) {
        $slug = $brosur->slug
            ?? optional($brosur->slugs->where('locale', $locale)->first())->slug
            ?? optional($brosur->slugs->first())->slug;
        return $slug ? url('/brosur/' . $slug) : null;
    })->filter()->values();
@endphp

@push('head')
    @foreach($prefetchUrls as $url)
        <link rel="prefetch" href="{{ $url }}">
    @endforeach
@endpush

<section class="py-10 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 [content-visibility:auto] [contain-intrinsic-size:750px]" data-block-reveal>

    <div
        x-data="{
            autoplayInterval: null,
            autoplayDelay: 4000,
            originalCount: 0,
            initialized: false,
            reducedMotion: false,
            isTabVisible: true,
            isAnimating: false,
            step: 0,
            scrollTimer: null,
            animTimer: null,
            scrollEndTimer: null,

            init() {
                this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                this.$nextTick(() => this.setupCarousel());

                document.addEventListener('visibilitychange', () => {
                    this.isTabVisible = !document.hidden;
                    this.isTabVisible ? this.startAutoplay() : this.stopAutoplay();
                });

                let resizeTimer;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(() => {
                        this.measureStep();
                        this.snapToNearest();
                    }, 200);
                }, { passive: true });
            },

            destroy() {
                this.stopAutoplay();
                clearTimeout(this.scrollTimer);
                clearTimeout(this.animTimer);
                clearTimeout(this.scrollEndTimer);
            },

            setupCarousel() {
                if (this.initialized) return;
                const container = this.$refs.carousel;
                if (!container) return;

                const originals = Array.from(container.children);
                this.originalCount = originals.length;
                this.initialized = true;

                if (this.originalCount < 2) return;

                originals.forEach(card => {
                    const clone = card.cloneNode(true);
                    clone.setAttribute('aria-hidden', 'true');
                    clone.setAttribute('inert', '');
                    clone.setAttribute('tabindex', '-1');
                    clone.querySelectorAll('a, button, input, select, textarea').forEach(el => {
                        el.setAttribute('tabindex', '-1');
                        el.setAttribute('aria-hidden', 'true');
                        el.setAttribute('inert', '');
                    });
                    container.appendChild(clone);
                });

                this.$nextTick(() => {
                    this.measureStep();
                    container.scrollLeft = 0;

                    container.addEventListener('scroll', () => this.onScroll(), { passive: true });

                    if ('onscrollend' in window) {
                        container.addEventListener('scrollend', () => this.resetIfNeeded());
                    }

                    if (!this.reducedMotion) this.startAutoplay();
                });
            },

            measureStep() {
                const container = this.$refs.carousel;
                if (!container) return;
                const cards = container.children;
                if (cards.length < 2) return;

                const a = cards[0].getBoundingClientRect();
                const b = cards[1].getBoundingClientRect();
                this.step = Math.round(b.left - a.left);
            },

            snapToNearest() {
                const container = this.$refs.carousel;
                if (!container || !this.step) return;
                const idx = Math.round(container.scrollLeft / this.step);
                container.scrollLeft = idx * this.step;
            },

            onScroll() {
                const container = this.$refs.carousel;
                if (!container || !this.step) return;

                if (!this.reducedMotion && this.isTabVisible) {
                    this.stopAutoplay();
                    clearTimeout(this.scrollTimer);
                    this.scrollTimer = setTimeout(() => this.startAutoplay(), 1500);
                }

                if (!('onscrollend' in window)) {
                    clearTimeout(this.scrollEndTimer);
                    this.scrollEndTimer = setTimeout(() => this.resetIfNeeded(), 200);
                }
            },

            resetIfNeeded() {
                if (this.isAnimating) return;
                const container = this.$refs.carousel;
                if (!container || !this.step) return;

                const cycleWidth = this.step * this.originalCount;
                if (container.scrollLeft >= cycleWidth) {
                    container.scrollLeft -= cycleWidth;
                }
            },

            next() {
                const container = this.$refs.carousel;
                if (!container || !this.step || this.originalCount < 2) return;

                const cycleWidth = this.step * this.originalCount;
                if (container.scrollLeft >= cycleWidth) {
                    container.scrollLeft -= cycleWidth;
                }

                this.isAnimating = true;
                container.scrollBy({
                    left: this.step,
                    behavior: this.reducedMotion ? 'auto' : 'smooth'
                });

                clearTimeout(this.animTimer);
                this.animTimer = setTimeout(() => {
                    this.isAnimating = false;
                    this.resetIfNeeded();
                }, 600);
            },

            prev() {
                const container = this.$refs.carousel;
                if (!container || !this.step || this.originalCount < 2) return;

                const cycleWidth = this.step * this.originalCount;

                if (container.scrollLeft <= 1) {
                    container.scrollLeft = cycleWidth;
                    requestAnimationFrame(() => {
                        this.isAnimating = true;
                        container.scrollBy({
                            left: -this.step,
                            behavior: this.reducedMotion ? 'auto' : 'smooth'
                        });
                        clearTimeout(this.animTimer);
                        this.animTimer = setTimeout(() => {
                            this.isAnimating = false;
                            this.resetIfNeeded();
                        }, 600);
                    });
                    return;
                }

                this.isAnimating = true;
                container.scrollBy({
                    left: -this.step,
                    behavior: this.reducedMotion ? 'auto' : 'smooth'
                });

                clearTimeout(this.animTimer);
                this.animTimer = setTimeout(() => {
                    this.isAnimating = false;
                    this.resetIfNeeded();
                }, 600);
            },

            stopAutoplay() {
                if (this.autoplayInterval) {
                    clearInterval(this.autoplayInterval);
                    this.autoplayInterval = null;
                }
            },

            startAutoplay() {
                if (this.reducedMotion || !this.isTabVisible) return;
                this.stopAutoplay();
                this.autoplayInterval = setInterval(() => this.next(), this.autoplayDelay);
            }
        }"
        @mouseenter="stopAutoplay()"
        @mouseleave="startAutoplay()"
        @touchstart.passive="stopAutoplay()"
        @touchend.passive="startAutoplay()"
        @focusin="stopAutoplay()"
        @focusout="startAutoplay()"
        class="relative bg-gradient-to-br from-[#fffef9] via-[#f3fbfa] to-[#fcf7fc] text-slate-900 border border-slate-200/90 rounded-2xl px-5 sm:px-8 lg:px-10 py-8 sm:py-10 lg:py-14 shadow-lg overflow-hidden [contain:layout_style]"
    >
        {{-- Top 3-color global accent line --}}
        <div class="pointer-events-none absolute top-0 inset-x-8 h-1 rounded-b-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>

        {{-- Soft global color washes & subtle geometric ornaments --}}
        <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 -left-20 w-72 h-72 sm:w-[400px] sm:h-[400px] bg-[#f7f0ac]/45 blur-[80px] rounded-full"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 sm:w-[420px] sm:h-[420px] bg-[#acf7f0]/40 blur-[90px] rounded-full"></div>
            <div class="absolute -bottom-24 -right-16 w-72 h-72 sm:w-[400px] sm:h-[400px] bg-[#f0acf7]/35 blur-[80px] rounded-full"></div>

            <div
                class="absolute inset-0 opacity-45"
                style="background-image: radial-gradient(rgba(15, 23, 42, 0.11) 1px, transparent 1px); background-size: 24px 24px; -webkit-mask-image: radial-gradient(ellipse at 50% 35%, #000 10%, transparent 72%); mask-image: radial-gradient(ellipse at 50% 35%, #000 10%, transparent 72%);"
            ></div>

            <svg class="hidden sm:block absolute top-7 right-9 w-24 h-24 text-slate-900/15" viewBox="0 0 96 96" fill="none">
                <circle cx="48" cy="48" r="44" stroke="currentColor" stroke-width="1" stroke-dasharray="4 6"/>
                <circle cx="48" cy="48" r="28" stroke="url(#brLightRingGrad)" stroke-width="1.5" stroke-opacity="0.7"/>
                <defs>
                    <linearGradient id="brLightRingGrad" x1="0" y1="0" x2="96" y2="96" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#e6d965"/>
                        <stop offset="50%" stop-color="#58d6cb"/>
                        <stop offset="100%" stop-color="#d875e3"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <div class="relative z-10 space-y-8 sm:space-y-10">

            {{-- Header Section --}}
            <div class="relative flex flex-col items-center text-center space-y-3 max-w-2xl mx-auto">
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-white/90 border border-slate-200/90 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-[#acf7f0]" aria-hidden="true"></span>
                    <span class="text-xs font-extrabold tracking-widest text-slate-700 uppercase">Informasi &amp; Brosur</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-[2.65rem] font-extrabold text-slate-900 tracking-tight leading-[1.1] [text-wrap:balance]">
                    {{ $sectionTitle }}
                </h2>
                <div class="w-24 h-1 rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>
                @if(filled($subtitle))
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            @if($brosurs->count() > 0)
                <div class="relative">
                    <div class="overflow-hidden -mx-4 sm:mx-0 px-4 sm:px-0">
                        <div
                            x-ref="carousel"
                            tabindex="0"
                            role="region"
                            aria-label="Carousel brosur"
                            @keydown.arrow-left.prevent="prev()"
                            @keydown.arrow-right.prevent="next()"
                            class="flex gap-4 sm:gap-6 overflow-x-auto pb-4 pt-2 snap-x snap-mandatory focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-4 focus-visible:rounded-2xl"
                            style="scrollbar-width: none; -ms-overflow-style: none; overscroll-behavior-x: contain;"
                        >
                            @foreach($brosurs as $brosur)
                                @php
                                    $dateObj = $brosur->brosur_date ? \Carbon\Carbon::parse($brosur->brosur_date) : null;
                                    $slugValue = $brosur->slug
                                        ?? optional($brosur->slugs->where('locale', $locale)->first())->slug
                                        ?? optional($brosur->slugs->first())->slug;
                                    $brosurUrl = $slugValue ? url('/brosur/' . $slugValue) : '#';
                                    $hasCover = method_exists($brosur, 'hasImage') && $brosur->hasImage('cover');
                                    $downloadUrl = method_exists($brosur, 'file') ? $brosur->file('download_file') : null;
                                    $isFirst = $loop->first;
                                    $cardTitle = (string) ($brosur->title ?? '');
                                @endphp

                                <div class="w-[80vw] max-w-[320px] sm:w-auto sm:max-w-none sm:min-w-[calc(50%-12px)] md:min-w-[calc(33.333%-16px)] lg:min-w-[calc(25%-18px)] snap-start shrink-0">

                                    <div class="relative rounded-2xl sm:rounded-2xl overflow-hidden shadow-lg group/card flex flex-col justify-between h-[390px] sm:h-[430px] p-5 sm:p-6 border border-slate-800/80 hover:border-[#acf7f0] transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl bg-gradient-to-br from-[#090d1a] via-[#0c1324] to-[#120d22] motion-reduce:transition-none motion-reduce:hover:translate-y-0">

                                        <span class="pointer-events-none absolute top-0 inset-x-0 z-20 h-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></span>

                                        {{-- Layer 1: Cover Image + Free Web Image Fallback --}}
                                        <div class="absolute inset-0 z-0 bg-slate-950">
                                            <img
                                                src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80"
                                                width="1200"
                                                height="1600"
                                                alt=""
                                                loading="lazy"
                                                decoding="async"
                                                onerror="if (!this.dataset.fbAlt) { this.dataset.fbAlt='1'; this.src='https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1200&q=80'; }"
                                                class="absolute inset-0 z-0 w-full h-full object-cover opacity-75 group-hover/card:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover/card:scale-100"
                                            >

                                            @if($hasCover)
                                                {!! twill_proxy_img(
                                                    $brosur,
                                                    'cover',
                                                    [400, 800, 1200],
                                                    '(max-width: 768px) 80vw, (max-width: 1024px) 33vw, 25vw',
                                                    $cardTitle,
                                                    [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                                    [
                                                        'class' => 'absolute inset-0 z-10 w-full h-full object-cover opacity-80 group-hover/card:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover/card:scale-100',
                                                        'width' => '1200',
                                                        'height' => '1600',
                                                        'loading' => $isFirst ? 'eager' : 'lazy',
                                                        'fetchpriority' => $isFirst ? 'high' : 'auto',
                                                        'decoding' => 'async',
                                                        'onerror' => "this.style.display='none'",
                                                    ]
                                                ) !!}
                                            @endif
                                            <div
                                                class="absolute inset-0 z-10 opacity-35 pointer-events-none"
                                                style="background-image: radial-gradient(rgba(172, 247, 240, 0.24) 1px, transparent 1px); background-size: 18px 18px;"
                                                aria-hidden="true"
                                            ></div>
                                            <div class="absolute inset-0 z-10 bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-950/25 pointer-events-none"></div>
                                        </div>

                                        {{-- Badge tanggal --}}
                                        <div class="relative z-20 flex items-center justify-between">
                                            <span class="px-3 py-1 rounded-full bg-[#f7f0ac] border border-[#f7f0ac] text-[10px] font-extrabold text-slate-950 uppercase tracking-wider shadow-sm">
                                                {{ $dateObj ? $dateObj->translatedFormat('M d, Y') : 'DOWNLOAD' }}
                                            </span>
                                        </div>

                                        {{-- Layer 2: Konten Teks & Tombol --}}
                                        <div class="relative z-20 space-y-3 sm:space-y-4 text-center mt-auto">
                                            <div class="space-y-1">
                                                <h4 class="text-[10px] sm:text-xs font-bold text-[#acf7f0] uppercase tracking-widest">
                                                    Katalog Brosur
                                                </h4>
                                                <h3 class="text-base sm:text-xl font-extrabold text-white group-hover/card:text-[#f7f0ac] transition-colors leading-snug line-clamp-2">
                                                    <a href="{{ $brosurUrl }}" class="focus:outline-none">
                                                        {{ $brosur->title }}
                                                    </a>
                                                </h3>
                                            </div>

                                            <div class="flex flex-col gap-2">
                                                @if($downloadUrl)
                                                    <a
                                                        href="{{ $downloadUrl }}"
                                                        download
                                                        target="_blank"
                                                        rel="noopener"
                                                        data-analytics="brosur-download"
                                                        data-brosur-id="{{ $brosur->id }}"
                                                        data-brosur-title="{{ $brosur->title }}"
                                                        class="w-full py-3 px-5 rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] hover:brightness-105 text-slate-950 font-extrabold text-[11px] sm:text-xs uppercase tracking-wider transition-all duration-200 shadow-lg flex items-center justify-center gap-2"
                                                    >
                                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                        <span>Download Brosur</span>
                                                    </a>
                                                @endif

                                                <a
                                                    href="{{ $brosurUrl }}"
                                                    data-analytics="brosur-click"
                                                    data-brosur-id="{{ $brosur->id }}"
                                                    data-brosur-title="{{ $brosur->title }}"
                                                    aria-label="Lihat detail brosur: {{ $brosur->title }}"
                                                    class="w-full py-2.5 px-5 rounded-full {{ $downloadUrl ? 'bg-white/95 hover:bg-[#f0acf7]' : 'bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] hover:brightness-105' }} text-slate-950 font-extrabold text-[11px] sm:text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-sm"
                                                >
                                                    <span>Lihat Detail</span>
                                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 transform group-hover/card:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($brosurs->count() > 1)
                        <div class="flex items-center justify-center gap-3 mt-6">
                            <button
                                @click="prev()"
                                type="button"
                                class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white border border-slate-200 hover:bg-[#acf7f0] hover:border-[#acf7f0] flex items-center justify-center text-slate-700 hover:text-slate-950 transition-all duration-200 shadow-sm cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2"
                                aria-label="Brosur sebelumnya"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button
                                @click="next()"
                                type="button"
                                class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white border border-slate-200 hover:bg-[#acf7f0] hover:border-[#acf7f0] flex items-center justify-center text-slate-700 hover:text-slate-950 transition-all duration-200 shadow-sm cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2"
                                aria-label="Brosur selanjutnya"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-16 bg-white/90 border border-slate-200 rounded-2xl shadow-sm">
                    <p class="text-slate-600 text-sm font-medium">Belum ada brosur yang dipublikasikan saat ini.</p>
                </div>
            @endif

        </div>
    </div>
</section>

@pushOnce('styles')
    <style>
        @media (prefers-reduced-motion: reduce) {
            [x-transition],
            .transition-all,
            .transition-transform,
            .transition-colors {
                transition-duration: 0.01ms !important;
            }
            .animate-pulse,
            .animate-ping {
                animation: none !important;
            }
        }

        [x-ref="carousel"] {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        [x-ref="carousel"]::-webkit-scrollbar {
            display: none;
        }
    </style>
@endpushOnce
