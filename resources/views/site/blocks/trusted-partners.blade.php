@php
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

    $extractText = function ($block, array $fields) use ($safeInput) {
        foreach ($fields as $field) {
            $val = $safeInput($block, $field);
            if (filled($val)) {
                $clean = trim(preg_replace('/\s+/', ' ', strip_tags((string) $val)));
                if (filled($clean)) {
                    return $clean;
                }
            }
        }
        return null;
    };

    $safeUrl = function ($url) {
        if (empty($url) || !is_string($url)) {
            return null;
        }
        $url = trim($url);
        foreach (['javascript:', 'data:', 'vbscript:', 'file:'] as $scheme) {
            if (stripos($url, $scheme) === 0) {
                return null;
            }
        }
        if (preg_match('#^(https?:|mailto:|tel:|/|\#)#i', $url)) {
            return $url;
        }
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return null;
        }
        return $url;
    };

    $label          = $safeInput($block, 'label');
    $titleMain      = $safeInput($block, 'title_main');
    $titleHighlight = $safeInput($block, 'title_highlight');
    $titleSuffix    = $safeInput($block, 'title_suffix');
    $description    = $extractText($block, ['description', 'subtitle', 'desc', 'caption', 'text', 'body']);

    $perPage    = max(1, (int) $safeInput($block, 'per_page', 2));
    $autoplayMs = max(0, (int) $safeInput($block, 'autoplay_delay', 5000));

    $items = $block->children ?? collect();
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

    $checkHasLogo = function ($item) {
        if (!$item || !method_exists($item, 'hasImage')) {
            return false;
        }
        return $item->hasImage('logo', 'default')
            || $item->hasImage('logo', 'desktop')
            || $item->hasImage('logo', 'mobile')
            || $item->hasImage('logo');
    };
@endphp

<section class="py-10 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 [content-visibility:auto] [contain-intrinsic-size:700px]" data-block-reveal>

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
        aria-label="{{ $label ?? 'Partners' }}"
        aria-roledescription="carousel"
        class="relative bg-gradient-to-br from-[#090d1a] via-[#0c1324] to-[#120d22] text-slate-100 border border-slate-800/90 rounded-3xl px-5 sm:px-8 lg:px-10 py-8 sm:py-10 lg:py-14 shadow-2xl [contain:layout_style]"
    >
        {{-- Top 3-color global accent bar --}}
        <div class="pointer-events-none absolute top-0 inset-x-8 h-1 rounded-b-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>

        {{-- Gradient washes & subtle geometric dot ornaments using #f7f0ac, #acf7f0, #f0acf7 --}}
        <div class="absolute inset-0 overflow-hidden rounded-3xl pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 -left-20 w-72 h-72 sm:w-[420px] sm:h-[420px] bg-gradient-to-br from-[#f7f0ac]/14 via-[#acf7f0]/10 to-transparent blur-[85px] rounded-full"></div>
            <div class="absolute top-1/2 left-1/3 -translate-y-1/2 w-64 h-64 sm:w-[360px] sm:h-[360px] bg-[#acf7f0]/10 blur-[90px] rounded-full"></div>
            <div class="absolute -bottom-24 -right-16 w-72 h-72 sm:w-[440px] sm:h-[440px] bg-gradient-to-tl from-[#f0acf7]/16 via-[#acf7f0]/10 to-transparent blur-[85px] rounded-full"></div>

            <div
                class="absolute inset-0 opacity-30"
                style="background-image: radial-gradient(rgba(172, 247, 240, 0.22) 1px, transparent 1px); background-size: 24px 24px; -webkit-mask-image: radial-gradient(ellipse at 70% 50%, #000 10%, transparent 72%); mask-image: radial-gradient(ellipse at 70% 50%, #000 10%, transparent 72%);"
            ></div>

            <svg class="hidden sm:block absolute top-7 right-9 w-24 h-24 text-[#acf7f0]/20" viewBox="0 0 96 96" fill="none">
                <circle cx="48" cy="48" r="44" stroke="currentColor" stroke-width="1" stroke-dasharray="4 6"/>
                <circle cx="48" cy="48" r="28" stroke="url(#tpRingGrad)" stroke-width="1" stroke-opacity="0.45"/>
                <defs>
                    <linearGradient id="tpRingGrad" x1="0" y1="0" x2="96" y2="96" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#f7f0ac"/>
                        <stop offset="50%" stop-color="#acf7f0"/>
                        <stop offset="100%" stop-color="#f0acf7"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <div class="relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-10 lg:gap-8 items-center">

                {{-- Left Column: Header --}}
                <div class="lg:col-span-4 space-y-5 sm:space-y-6 text-center lg:text-left">
                    @if(filled($label))
                        <div class="inline-flex items-center gap-3">
                            <span class="w-8 h-0.5 bg-gradient-to-r from-[#f7f0ac] to-[#acf7f0]" aria-hidden="true"></span>
                            <span class="text-xs font-bold tracking-widest text-[#acf7f0] uppercase">{{ $label }}</span>
                        </div>
                    @endif

                    @if(filled($titleMain) || filled($titleHighlight) || filled($titleSuffix))
                        <h2 class="text-3xl sm:text-4xl lg:text-[2.85rem] font-extrabold text-white tracking-tight leading-[1.08] [text-wrap:balance]">
                            @if(filled($titleMain))<span>{{ $titleMain }}</span>@endif
                            @if(filled($titleHighlight))
                                <span class="block mt-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] bg-clip-text text-transparent">
                                    {{ $titleHighlight }}
                                </span>
                            @endif
                            @if(filled($titleSuffix))
                                <span class="block mt-1">{{ $titleSuffix }}</span>
                            @endif
                        </h2>
                    @endif

                    @if(filled($description))
                        <p class="text-slate-300/90 text-sm sm:text-base leading-relaxed max-w-md mx-auto lg:mx-0">
                            {{ $description }}
                        </p>
                    @endif

                    @if($totalPages > 1)
                        <div class="flex items-center gap-3 pt-2 justify-center lg:justify-start">
                            <button
                                @click="prev(); startAutoplay()"
                                type="button"
                                aria-label="Previous"
                                class="w-11 h-11 rounded-full bg-slate-900/90 border border-slate-700/90 flex items-center justify-center text-slate-200 hover:bg-gradient-to-r hover:from-[#f7f0ac] hover:via-[#acf7f0] hover:to-[#f0acf7] hover:text-slate-950 hover:border-transparent transition-all duration-300 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] motion-reduce:transition-none"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button
                                @click="next(); startAutoplay()"
                                type="button"
                                aria-label="Next"
                                class="w-11 h-11 rounded-full bg-slate-900/90 border border-slate-700/90 flex items-center justify-center text-slate-200 hover:bg-gradient-to-r hover:from-[#f7f0ac] hover:via-[#acf7f0] hover:to-[#f0acf7] hover:text-slate-950 hover:border-transparent transition-all duration-300 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] motion-reduce:transition-none"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Slider --}}
                <div class="lg:col-span-8 w-full">

                    @if($totalItems > 0)

                        <div class="overflow-hidden py-3" aria-live="polite" aria-atomic="false">
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
                                        aria-label="{{ $pageIndex + 1 }} / {{ $totalPages }}"
                                    >
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 items-stretch">
                                            @foreach($pageItems as $item)
                                                @php
                                                    $hasLogo  = $checkHasLogo($item);
                                                    $name     = $safeInput($item, 'name');
                                                    $url      = $safeUrl($safeInput($item, 'url'));
                                                    $initials = filled($name) ? mb_strtoupper(mb_substr(trim($name), 0, 2)) : '';
                                                @endphp

                                                @if($url)
                                                    <a href="{{ $url }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       @if(filled($name)) aria-label="{{ $name }}" @endif
                                                       class="group/card relative bg-gradient-to-b from-slate-900/95 via-[#0d1426]/95 to-slate-950/95 border border-slate-800/90 hover:border-[#acf7f0]/55 rounded-2xl p-6 flex flex-col items-center justify-center gap-4 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl motion-reduce:transition-none motion-reduce:hover:translate-y-0"
                                                    >
                                                        <span class="pointer-events-none absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-[#f7f0ac]/60 via-[#acf7f0]/60 to-[#f0acf7]/60 opacity-70 group-hover/card:opacity-100 transition-opacity duration-300" aria-hidden="true"></span>

                                                        <div class="relative p-[2px] rounded-full bg-gradient-to-br from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] transition-transform duration-300 group-hover/card:scale-105 motion-reduce:transition-none motion-reduce:group-hover/card:scale-100 [transform:translateZ(0)]">
                                                            <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden p-3">
                                                                @if($hasLogo)
                                                                    {!! twill_proxy_img(
                                                                        $item,
                                                                        'logo',
                                                                        [128, 256],
                                                                        '112px',
                                                                        $name ?? '',
                                                                        [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                                                        [
                                                                            'class' => 'relative z-10 w-full h-full object-contain',
                                                                            'width' => '128',
                                                                            'height' => '128',
                                                                            'loading' => 'lazy',
                                                                            'decoding' => 'async',
                                                                            'onerror' => "this.style.display='none'",
                                                                        ]
                                                                    ) !!}
                                                                @endif
                                                                <span class="absolute inset-0 z-0 flex items-center justify-center text-slate-950 font-black text-xl tracking-tight select-none">{{ $initials }}</span>
                                                            </div>
                                                        </div>

                                                        @if(filled($name))
                                                            <h4 class="text-white font-extrabold text-sm sm:text-base leading-snug text-center group-hover/card:text-[#acf7f0] transition-colors duration-200 motion-reduce:transition-none">
                                                                {{ $name }}
                                                            </h4>
                                                        @endif
                                                    </a>
                                                @else
                                                    <div class="group/card relative bg-gradient-to-b from-slate-900/95 via-[#0d1426]/95 to-slate-950/95 border border-slate-800/90 hover:border-[#acf7f0]/40 rounded-2xl p-6 flex flex-col items-center justify-center gap-4 overflow-hidden transition-all duration-300 [contain:layout_style]">
                                                        <span class="pointer-events-none absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-[#f7f0ac]/50 via-[#acf7f0]/50 to-[#f0acf7]/50 opacity-60 group-hover/card:opacity-100 transition-opacity duration-300" aria-hidden="true"></span>

                                                        <div class="relative p-[2px] rounded-full bg-gradient-to-br from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] transition-transform duration-300 group-hover/card:scale-105 motion-reduce:transition-none motion-reduce:group-hover/card:scale-100 [transform:translateZ(0)]">
                                                            <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden p-3">
                                                                @if($hasLogo)
                                                                    {!! twill_proxy_img(
                                                                        $item,
                                                                        'logo',
                                                                        [128, 256],
                                                                        '112px',
                                                                        $name ?? '',
                                                                        [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                                                        [
                                                                            'class' => 'relative z-10 w-full h-full object-contain',
                                                                            'width' => '128',
                                                                            'height' => '128',
                                                                            'loading' => 'lazy',
                                                                            'decoding' => 'async',
                                                                            'onerror' => "this.style.display='none'",
                                                                        ]
                                                                    ) !!}
                                                                @endif
                                                                <span class="absolute inset-0 z-0 flex items-center justify-center text-slate-950 font-black text-xl tracking-tight select-none">{{ $initials }}</span>
                                                            </div>
                                                        </div>

                                                        @if(filled($name))
                                                            <h4 class="text-white font-extrabold text-sm sm:text-base leading-snug text-center group-hover/card:text-[#acf7f0] transition-colors duration-200 motion-reduce:transition-none">
                                                                {{ $name }}
                                                            </h4>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach

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

                        @if($totalPages > 1)
                            <div class="flex justify-center gap-2 mt-6" role="tablist" aria-label="Pagination">
                                @foreach($pages as $index => $page)
                                    <button
                                        @click="goTo({{ $index }})"
                                        type="button"
                                        :class="activeSlide === {{ $index + 1 }} ? 'w-8 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]' : 'w-2.5 bg-slate-700 hover:bg-slate-600'"
                                        class="h-2 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] motion-reduce:transition-none"
                                        :aria-selected="activeSlide === {{ $index + 1 }} ? 'true' : 'false'"
                                        aria-label="{{ $index + 1 }}"
                                        role="tab"
                                    ></button>
                                @endforeach
                            </div>
                        @endif

                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
