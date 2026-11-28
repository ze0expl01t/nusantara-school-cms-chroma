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

    $extractYouTubeId = function ($url) {
        if (empty($url) || !is_string($url)) return null;
        $url = trim($url);

        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{11})~', $url, $m))         return $m[1];
        if (preg_match('~[?&]v=([A-Za-z0-9_-]{11})~', $url, $m))             return $m[1];
        if (preg_match('~youtube\.com/embed/([A-Za-z0-9_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~youtube\.com/shorts/([A-Za-z0-9_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~youtube\.com/live/([A-Za-z0-9_-]{11})~', $url, $m))  return $m[1];
        if (preg_match('~^[A-Za-z0-9_-]{11}$~', $url))                       return $url;

        return null;
    };

    $eyebrow        = $safeInput($block, 'eyebrow');
    $titleMain      = $safeInput($block, 'title_main');
    $titleHighlight = $safeInput($block, 'title_highlight');
    $subtitle       = $safeInput($block, 'subtitle');

    $children = $block->children ?? null;

    if (!$children || $children->isEmpty()) {
        try {
            $children = method_exists($block, 'children') ? $block->children()->get() : collect();
        } catch (\Throwable $e) {
            $children = collect();
        }
    }

    if (!$children instanceof \Illuminate\Support\Collection) {
        $children = collect($children ?? []);
    }

    $videoBlocks = $children->filter(function ($child) {
        return ($child->type ?? null) === 'video_item';
    });

    if ($videoBlocks->isEmpty() && $children->isNotEmpty()) {
        $videoBlocks = $children;
    }

    $videos = collect();
    foreach ($videoBlocks as $item) {
        $ytUrl = $safeInput($item, 'youtube_url');
        $ytId  = $extractYouTubeId($ytUrl);
        if (!$ytId) continue;

        $customThumb = null;
        try {
            if ($item->hasImage('custom_thumbnail', 'desktop')) {
                $customThumb = $item->image('custom_thumbnail', 'desktop');
            } elseif ($item->hasImage('custom_thumbnail')) {
                $customThumb = $item->image('custom_thumbnail');
            }
        } catch (\Throwable $e) {
            $customThumb = null;
        }

        $videos->push([
            'id'          => $ytId,
            'title'       => $safeInput($item, 'title'),
            'description' => $safeInput($item, 'description'),
            'duration'    => $safeInput($item, 'duration'),
            'thumb'       => $customThumb ?: "https://i.ytimg.com/vi/{$ytId}/mqdefault.jpg",
            'thumb_md'    => $customThumb ?: "https://i.ytimg.com/vi/{$ytId}/hqdefault.jpg",
            'thumb_hd'    => $customThumb ?: "https://i.ytimg.com/vi/{$ytId}/maxresdefault.jpg",
        ]);
    }

    $totalVideos = $videos->count();
    $perPage     = 2;
    $pages       = $totalVideos > 0 ? $videos->chunk($perPage) : collect();
    $totalPages  = $pages->count();

    $firstVideo    = $videos->first();
    $firstThumbUrl = $firstVideo ? $firstVideo['thumb_hd'] : null;
@endphp

@push('head')
    <link rel="preconnect" href="https://i.ytimg.com" crossorigin>
    <link rel="preconnect" href="https://www.youtube-nocookie.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.youtube.com">
    @if($firstThumbUrl)
        <link rel="preload" as="image" href="{{ $firstThumbUrl }}" fetchpriority="high">
    @endif
@endpush

<section class="py-10 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>

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

            init() {
                this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                this.isTabVisible = !document.hidden;

                this.$nextTick(() => {
                    this.setupClone();

                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => { this.isJumping = false; });
                    });

                    if (!this.reducedMotion && this.totalSlides > 1) {
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
                if (this.reducedMotion || !this.isTabVisible || this.totalSlides < 2) return;
                this.stopAutoplay();
                this.autoplay = setInterval(() => this.next(), 7000);
            },

            stopAutoplay() {
                if (this.autoplay) {
                    clearInterval(this.autoplay);
                    this.autoplay = null;
                }
            },

            next() {
                if (this.isJumping || this.isNavigating || this.totalSlides < 2) return;
                this.isNavigating = true;
                this.activeSlide++;
                if (this.activeSlide > this.totalSlides) {
                    setTimeout(() => this.resetPosition(1), this.transitionMs);
                }
                setTimeout(() => { this.isNavigating = false; }, this.transitionMs);
            },

            prev() {
                if (this.isJumping || this.isNavigating || this.totalSlides < 2) return;
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
                    requestAnimationFrame(() => { this.isJumping = false; });
                });
            },

            goTo(index) {
                const target = index + 1;
                if (this.totalSlides < 2) return;
                if (this.activeSlide === target || this.isJumping || this.isNavigating) return;
                this.isNavigating = true;
                this.activeSlide = target;
                this.startAutoplay();
                setTimeout(() => { this.isNavigating = false; }, this.transitionMs);
            },

            trackTransform() {
                if (this.totalSlides < 2) return 'translateX(0%)';
                return 'translateX(-' + (this.activeSlide * 100) + '%)';
            }
        }"
        @mouseenter="stopAutoplay()"
        @mouseleave="startAutoplay()"
        @touchstart.passive="stopAutoplay()"
        @touchend.passive="startAutoplay()"
        @focusin="stopAutoplay()"
        @focusout="startAutoplay()"
        role="region"
        aria-label="{{ $eyebrow ?? 'Video Gallery' }}"
        aria-roledescription="carousel"
        class="relative bg-gradient-to-br from-[#090d1a] via-[#0c1324] to-[#120d22] text-slate-100 border border-slate-800/90 rounded-2xl px-5 sm:px-8 lg:px-10 py-8 sm:py-10 lg:py-14 shadow-2xl"
    >
        {{-- Top 3-color global accent line --}}
        <div class="pointer-events-none absolute top-0 inset-x-8 h-1 rounded-b-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>

        {{-- Gradient washes & subtle geometric dot ornaments using #f7f0ac, #acf7f0, #f0acf7 --}}
        <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 -left-20 w-72 h-72 sm:w-[420px] sm:h-[420px] bg-gradient-to-br from-[#f7f0ac]/14 via-[#acf7f0]/10 to-transparent blur-[85px] rounded-full"></div>
            <div class="absolute top-1/2 left-1/3 -translate-y-1/2 w-64 h-64 sm:w-[360px] sm:h-[360px] bg-[#acf7f0]/10 blur-[90px] rounded-full"></div>
            <div class="absolute -bottom-24 -right-16 w-72 h-72 sm:w-[440px] sm:h-[440px] bg-gradient-to-tl from-[#f0acf7]/16 via-[#acf7f0]/10 to-transparent blur-[85px] rounded-full"></div>

            <div
                class="absolute inset-0 opacity-30"
                style="background-image: radial-gradient(rgba(172, 247, 240, 0.22) 1px, transparent 1px); background-size: 24px 24px; -webkit-mask-image: radial-gradient(ellipse at 70% 50%, #000 10%, transparent 72%); mask-image: radial-gradient(ellipse at 70% 50%, #000 10%, transparent 72%);"
            ></div>

            {{-- Scattered ring ornaments placed across open spaces without crowding left controls --}}
            <svg class="hidden sm:block absolute top-7 right-9 w-24 h-24 text-[#4ef5e6]/22" viewBox="0 0 96 96" fill="none">
                <circle cx="48" cy="48" r="44" stroke="currentColor" stroke-width="1" stroke-dasharray="4 6"/>
                <circle cx="48" cy="48" r="28" stroke="url(#vgRingGrad)" stroke-width="1.2" stroke-opacity="0.45"/>
                <defs>
                    <linearGradient id="vgRingGrad" x1="0" y1="0" x2="96" y2="96" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#ffe659"/>
                        <stop offset="50%" stop-color="#4ef5e6"/>
                        <stop offset="100%" stop-color="#ff7cf2"/>
                    </linearGradient>
                </defs>
            </svg>

            <svg class="hidden lg:block absolute top-6 left-[35%] w-20 h-20 text-[#ffe659]/20" viewBox="0 0 80 80" fill="none">
                <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="1" stroke-dasharray="4 6"/>
                <circle cx="40" cy="40" r="18" stroke="#ff7cf2" stroke-width="1" stroke-opacity="0.3"/>
            </svg>

            <svg class="hidden md:block absolute bottom-3 left-[47%] w-20 h-20 text-[#4ef5e6]/20" viewBox="0 0 80 80" fill="none">
                <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="1" stroke-dasharray="3 6"/>
                <circle cx="40" cy="40" r="18" stroke="#ffe659" stroke-width="1" stroke-opacity="0.3"/>
            </svg>

            <svg class="hidden sm:block absolute bottom-4 right-10 w-22 h-22 text-[#ff7cf2]/20" viewBox="0 0 88 88" fill="none">
                <circle cx="44" cy="44" r="38" stroke="currentColor" stroke-width="1" stroke-dasharray="4 6"/>
                <circle cx="44" cy="44" r="20" stroke="url(#vgRingGrad)" stroke-width="1.1" stroke-opacity="0.38"/>
            </svg>
        </div>

        <div class="relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-10 lg:gap-8 items-center">

                {{-- Left Column --}}
                <div class="lg:col-span-4 space-y-5 sm:space-y-6 text-center lg:text-left">
                    @if(filled($eyebrow))
                        <div class="inline-flex items-center gap-3">
                            <span class="w-8 h-0.5 bg-gradient-to-r from-[#f7f0ac] to-[#acf7f0]" aria-hidden="true"></span>
                            <span class="text-xs font-bold tracking-widest text-[#acf7f0] uppercase">
                                {{ $eyebrow }}
                            </span>
                        </div>
                    @endif

                    @if(filled($titleMain) || filled($titleHighlight))
                        <h2 class="text-3xl sm:text-4xl lg:text-[2.85rem] font-extrabold text-white tracking-tight leading-[1.08] [text-wrap:balance]">
                            @if(filled($titleMain))<span>{{ $titleMain }}</span>@endif
                            @if(filled($titleHighlight))
                                <span class="block mt-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] bg-clip-text text-transparent">
                                    {{ $titleHighlight }}
                                </span>
                            @endif
                        </h2>
                    @endif

                    @if(filled($subtitle))
                        <p class="text-slate-300/90 text-sm sm:text-base leading-relaxed max-w-md mx-auto lg:mx-0">
                            {{ $subtitle }}
                        </p>
                    @endif

                    @if($totalPages > 1)
                        <div class="flex items-center gap-3 pt-2 justify-center lg:justify-start">
                            <button @click="prev(); startAutoplay()" type="button" aria-label="Previous"
                                class="w-11 h-11 rounded-full bg-slate-900/90 border border-slate-700/90 flex items-center justify-center text-slate-200 hover:bg-[#4ef5e6] hover:text-slate-950 hover:border-[#4ef5e6] transition-all duration-300 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] motion-reduce:transition-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button @click="next(); startAutoplay()" type="button" aria-label="Next"
                                class="w-11 h-11 rounded-full bg-slate-900/90 border border-slate-700/90 flex items-center justify-center text-slate-200 hover:bg-[#4ef5e6] hover:text-slate-950 hover:border-[#4ef5e6] transition-all duration-300 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] motion-reduce:transition-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Video Slider --}}
                <div class="lg:col-span-8 w-full">

                    @if($totalVideos > 0)

                        <div class="overflow-hidden" aria-live="polite" aria-atomic="false">
                            <div
                                x-ref="track"
                                class="flex will-change-transform"
                                :class="isJumping ? '' : 'transition-transform duration-700 ease-out motion-reduce:transition-none'"
                                :style="'transform: ' + trackTransform()"
                            >
                                @foreach($pages as $pageIndex => $pageVideos)
                                    <div class="w-full flex-shrink-0" role="group" aria-roledescription="slide"
                                         aria-label="{{ $pageIndex + 1 }} / {{ $totalPages }}">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 items-stretch">
                                            @foreach($pageVideos as $videoIndex => $video)
                                                @php
                                                    $isFirst = ($pageIndex === 0 && $videoIndex === 0);
                                                    $vTitle  = $video['title'] ?? '';
                                                @endphp
                                                <article
                                                    x-data="{
                                                        playing: false,
                                                        play() {
                                                            this.playing = true;
                                                            this.$nextTick(() => this.$refs.iframe?.focus());
                                                        }
                                                    }"
                                                    class="group relative bg-gradient-to-b from-slate-900/95 via-[#0d1426]/95 to-slate-950/95 border border-slate-800/90 hover:border-[#acf7f0]/55 rounded-2xl overflow-hidden shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full motion-reduce:transition-none motion-reduce:hover:translate-y-0"
                                                >
                                                    <div class="relative aspect-video bg-slate-900 overflow-hidden">
                                                        <template x-if="!playing">
                                                            <button type="button" @click="play()"
                                                                class="absolute inset-0 w-full h-full group/play focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-inset"
                                                                aria-label="{{ $vTitle }}">
                                                                <img
                                                                    src="{{ $video['thumb_hd'] }}"
                                                                    srcset="{{ $video['thumb'] }} 320w, {{ $video['thumb_md'] }} 480w, {{ $video['thumb_hd'] }} 1280w"
                                                                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 40vw"
                                                                    width="1280"
                                                                    height="720"
                                                                    alt="{{ $vTitle }}"
                                                                    class="w-full h-full object-cover transition-transform duration-500 group-hover/play:scale-105 motion-reduce:transition-none motion-reduce:group-hover/play:scale-100"
                                                                    @if($isFirst)
                                                                        loading="eager"
                                                                        fetchpriority="high"
                                                                        decoding="sync"
                                                                    @else
                                                                        loading="lazy"
                                                                        decoding="async"
                                                                    @endif
                                                                    onerror="if (!this.dataset.fbStep) { this.dataset.fbStep='1'; this.srcset=''; this.src='{{ $video['thumb_md'] }}'; } else if (this.dataset.fbStep==='1') { this.dataset.fbStep='2'; this.src='{{ $video['thumb'] }}'; } else { this.style.display='none'; }"
                                                                >
                                                                <span class="absolute inset-0 bg-slate-950/35 group-hover/play:bg-slate-950/20 transition-colors duration-300"></span>
                                                                <span class="absolute inset-0 flex items-center justify-center">
                                                                    <span class="w-15 h-15 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] flex items-center justify-center text-slate-950 shadow-xl transition-transform duration-300 group-hover/play:scale-110 motion-reduce:transition-none motion-reduce:group-hover/play:scale-100">
                                                                        <svg class="w-6 h-6 sm:w-7 sm:h-7 ml-0.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                            <path d="M8 5v14l11-7z"/>
                                                                        </svg>
                                                                    </span>
                                                                </span>
                                                                @if(filled($video['duration']))
                                                                    <span class="absolute bottom-2.5 right-2.5 px-2.5 py-0.5 rounded-full bg-slate-950/85 border border-white/15 text-[#acf7f0] text-xs font-bold tracking-wide">
                                                                        {{ $video['duration'] }}
                                                                    </span>
                                                                @endif
                                                            </button>
                                                        </template>

                                                        <template x-if="playing">
                                                            <iframe
                                                                x-ref="iframe"
                                                                src="https://www.youtube-nocookie.com/embed/{{ $video['id'] }}?autoplay=1&rel=0&modestbranding=1"
                                                                title="{{ $vTitle }}"
                                                                width="1280"
                                                                height="720"
                                                                loading="lazy"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                                allowfullscreen
                                                                referrerpolicy="strict-origin-when-cross-origin"
                                                                class="absolute inset-0 w-full h-full border-0"
                                                            ></iframe>
                                                        </template>
                                                    </div>

                                                    <div class="p-5 sm:p-6 flex-grow flex flex-col">
                                                        @if(filled($vTitle))
                                                            <h3 class="text-white font-extrabold text-base sm:text-lg leading-snug mb-2 line-clamp-2">
                                                                {{ $vTitle }}
                                                            </h3>
                                                        @endif
                                                        @if(filled($video['description']))
                                                            <p class="text-slate-300/90 text-sm leading-relaxed line-clamp-3 flex-grow">
                                                                {{ $video['description'] }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    <div class="h-1 w-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>
                                                </article>
                                            @endforeach

                                            @if($pageVideos->count() < $perPage)
                                                @for($i = $pageVideos->count(); $i < $perPage; $i++)
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
