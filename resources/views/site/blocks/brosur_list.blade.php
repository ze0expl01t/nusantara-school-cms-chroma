@php
    use App\Models\Brosur;

    $safeInput = function ($block, $field, $default = null) {
        $value = $block->input($field);
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? $default;
        }
        return $value ?: $default;
    };

    $sectionTitle = $safeInput($block, 'title', 'Katalog & Brosur');
    $limit = (int) $safeInput($block, 'limit', 12);

    // Eager load slugs, medias & files (role: download_file)
    $brosurs = Brosur::where('published', true)
        ->with(['slugs', 'medias', 'files'])
        ->orderBy('brosur_date', 'desc')
        ->take($limit)
        ->get();

    $prefetchUrls = $brosurs->take(2)->map(function ($brosur) {
        $slug = $brosur->slug
            ?? optional($brosur->slugs->where('locale', app()->getLocale())->first())->slug
            ?? optional($brosur->slugs->first())->slug;
        return $slug ? url('/brosur/' . $slug) : null;
    })->filter()->values();
@endphp

@push('head')
    @foreach($prefetchUrls as $url)
        <link rel="prefetch" href="{{ $url }}">
    @endforeach
@endpush

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden" data-block-reveal>

    <!-- Efek Ambient Glow -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[600px] sm:h-[600px] bg-emerald-500/10 blur-[80px] rounded-full pointer-events-none"></div>

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
        class="relative z-10 space-y-8 sm:space-y-10"
    >

        <!-- Header Section -->
        <div class="relative flex flex-col items-center text-center space-y-3 pb-4 sm:pb-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-semibold text-slate-600">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse motion-reduce:animate-none" aria-hidden="true"></span>
                Informasi & Brosur
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2">
                {{ $sectionTitle }}
            </h2>
            <div class="w-20 h-1 bg-gradient-to-r from-transparent via-emerald-500 to-transparent rounded-full" aria-hidden="true"></div>
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
                        class="flex gap-4 sm:gap-6 overflow-x-auto pb-4 pt-2 snap-x snap-mandatory focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-4 focus-visible:rounded-2xl"
                        style="scrollbar-width: none; -ms-overflow-style: none; overscroll-behavior-x: contain;"
                    >
                        @foreach($brosurs as $brosur)
                            @php
                                $dateObj = $brosur->brosur_date ? \Carbon\Carbon::parse($brosur->brosur_date) : null;
                                $slugValue = $brosur->slug
                                    ?? optional($brosur->slugs->where('locale', app()->getLocale())->first())->slug
                                    ?? optional($brosur->slugs->first())->slug;
                                $brosurUrl = $slugValue ? url('/brosur/' . $slugValue) : '#';
                                $hasCover = $brosur->hasImage('cover');
                                $downloadUrl = $brosur->file('download_file');
                                $isFirst = $loop->first;
                            @endphp

                            <div class="w-[80vw] max-w-[320px] sm:w-auto sm:max-w-none sm:min-w-[calc(50%-12px)] md:min-w-[calc(33.333%-16px)] lg:min-w-[calc(25%-18px)] snap-start shrink-0">

                                <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-xl group/card flex flex-col justify-between h-[380px] sm:h-[420px] p-5 sm:p-6 border border-slate-200 hover:border-emerald-500 transition-all duration-500 hover:-translate-y-2 bg-slate-900 motion-reduce:transition-none motion-reduce:hover:translate-y-0">

                                    {{-- Layer 1: Cover Image --}}
                                    <div class="absolute inset-0 z-0 bg-slate-950">
                                        @if($hasCover)
                                            {!! twill_proxy_img(
                                                $brosur,
                                                'cover',
                                                [400, 800, 1200],
                                                '(max-width: 768px) 80vw, (max-width: 1024px) 33vw, 25vw',
                                                (string) ($brosur->title ?? ''),
                                                [],
                                                null,
                                                [
                                                    'class' => 'relative w-full h-full object-cover opacity-75 group-hover/card:scale-110 transition-transform duration-700 motion-reduce:transition-none motion-reduce:group-hover/card:scale-100',
                                                    'width' => '1200',
                                                    'height' => '1600',
                                                    'loading' => $isFirst ? 'eager' : 'lazy',
                                                    'fetchpriority' => $isFirst ? 'high' : 'auto',
                                                    'decoding' => 'async',
                                                ]
                                            ) !!}
                                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/65 to-transparent pointer-events-none"></div>
                                        @else
                                            <div class="absolute inset-0 bg-slate-800 flex items-center justify-center text-slate-400 text-xs">
                                                No Image Cover
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Badge tanggal --}}
                                    <div class="relative z-10 flex items-center justify-between">
                                        <span class="px-3 py-1 rounded-full bg-slate-900/60 border border-white/20 text-[10px] font-bold text-white uppercase tracking-wider backdrop-blur-md">
                                            {{ $dateObj ? $dateObj->translatedFormat('M d, Y') : 'DOWNLOAD' }}
                                        </span>
                                    </div>

                                    {{-- Layer 2: Konten Teks & Tombol --}}
                                    <div class="relative z-10 space-y-3 sm:space-y-4 text-center mt-auto">
                                        <div class="space-y-1">
                                            <h4 class="text-[10px] sm:text-xs font-semibold text-emerald-400 uppercase tracking-widest">
                                                Katalog Brosur
                                            </h4>
                                            <h3 class="text-base sm:text-xl font-extrabold text-white group-hover/card:text-emerald-300 transition-colors leading-snug line-clamp-2">
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
                                                    class="w-full py-3 px-5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider transition-all shadow-lg hover:shadow-emerald-600/25 flex items-center justify-center gap-2"
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
                                                class="w-full py-2.5 px-5 rounded-full {{ $downloadUrl ? 'bg-white/10 hover:bg-white/20 border border-white/20' : 'bg-emerald-600 hover:bg-emerald-500' }} text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2"
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
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white border border-slate-200 hover:border-emerald-500 hover:bg-slate-50 flex items-center justify-center text-slate-700 hover:text-emerald-600 transition-all duration-300 shadow-md cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                            aria-label="Brosur sebelumnya"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button
                            @click="next()"
                            type="button"
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white border border-slate-200 hover:border-emerald-500 hover:bg-slate-50 flex items-center justify-center text-slate-700 hover:text-emerald-600 transition-all duration-300 shadow-md cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
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
            <div class="text-center py-16 bg-white border border-slate-200 rounded-3xl shadow-sm">
                <p class="text-slate-500 text-sm font-medium">Belum ada brosur yang dipublikasikan saat ini.</p>
            </div>
        @endif

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