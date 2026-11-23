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

    // ===== Helper: extract YouTube video ID dari berbagai format URL =====
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

    $eyebrow        = $safeInput($block, 'eyebrow', 'Video Gallery');
    $titleMain      = $safeInput($block, 'title_main', "Watch our");
    $titleHighlight = $safeInput($block, 'title_highlight', 'latest videos');
    $subtitle       = $safeInput($block, 'subtitle', 'Kumpulan video dokumentasi kegiatan, pelatihan, dan informasi K3.');

    // ===== Ambil children dengan beberapa fallback =====
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

    // ===== Filter hanya block bertipe 'video_item' =====
    $videoBlocks = $children->filter(function ($child) {
        return ($child->type ?? null) === 'video_item';
    });

    if ($videoBlocks->isEmpty() && $children->isNotEmpty()) {
        $videoBlocks = $children;
    }

    // ===== Bangun array video =====
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
            'title'       => $safeInput($item, 'title', 'Video'),
            'description' => $safeInput($item, 'description', ''),
            'duration'    => $safeInput($item, 'duration', ''),
            'thumb'       => $customThumb ?: "https://i.ytimg.com/vi/{$ytId}/mqdefault.jpg",      // 320x180 (16:9)
            'thumb_md'    => $customThumb ?: "https://i.ytimg.com/vi/{$ytId}/hqdefault.jpg",      // 480x360 (4:3)
            'thumb_hd'    => $customThumb ?: "https://i.ytimg.com/vi/{$ytId}/maxresdefault.jpg",  // 1280x720 (16:9)
        ]);
    }

    $totalVideos = $videos->count();
    $perPage     = 2;
    $pages       = $totalVideos > 0 ? $videos->chunk($perPage) : collect();
    $totalPages  = $pages->count();

    // ═══ LCP: ambil thumbnail pertama untuk preload ═══
    $firstVideo = $videos->first();
    $firstThumbUrl = $firstVideo ? $firstVideo['thumb_hd'] : null;
@endphp

{{-- ═══ Resource Hints — HIGH IMPACT untuk LCP ═══ --}}
@push('head')
    <link rel="preconnect" href="https://i.ytimg.com" crossorigin>
    <link rel="preconnect" href="https://www.youtube-nocookie.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.youtube.com">
    @if($firstThumbUrl)
        {{-- Preload LCP image untuk video pertama --}}
        <link rel="preload" as="image" href="{{ $firstThumbUrl }}" fetchpriority="high">
    @endif
@endpush

{{-- Outer section --}}
<section class="py-8 sm:py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>

    {{-- Wrapper card gelap --}}
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
        aria-label="Galeri Video"
        aria-roledescription="carousel"
        class="relative bg-slate-900 text-slate-100 rounded-3xl px-5 sm:px-8 lg:px-10 py-8 sm:py-10 lg:py-16"
    >
        {{-- Ambient glow — kurangi blur radius untuk hemat GPU paint --}}
        <div class="absolute inset-0 overflow-hidden rounded-3xl pointer-events-none">
            <div class="absolute top-1/2 left-0 -translate-y-1/2 w-64 h-64 sm:w-[500px] sm:h-[500px] bg-red-600/10 blur-[80px] rounded-full"></div>
            <div class="absolute top-1/3 right-0 w-56 h-56 sm:w-[400px] sm:h-[400px] bg-[#f0acf7]/10 blur-[70px] rounded-full"></div>
        </div>

        <div class="relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 sm:gap-12 lg:gap-8 items-center">

                {{-- Kolom Kiri --}}
                <div class="lg:col-span-4 space-y-5 sm:space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-3">
                        <span class="w-8 h-0.5 bg-red-500"></span>
                        <span class="text-[11px] sm:text-xs font-bold tracking-widest text-red-400 uppercase">
                            {{ $eyebrow }}
                        </span>
                    </div>

                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.1]">
                        {{ $titleMain }}
                        @if($titleHighlight)
                            <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 via-amber-400 to-[#acf7f0]">
                                {{ $titleHighlight }}
                            </span>
                        @endif
                    </h2>

                    @if($subtitle)
                        <p class="text-slate-400 text-sm sm:text-base leading-relaxed max-w-md mx-auto lg:mx-0">
                            {{ $subtitle }}
                        </p>
                    @endif

                    @if($totalPages > 1)
                        <div class="flex items-center gap-3 sm:gap-4 pt-2 sm:pt-4 justify-center lg:justify-start">
                            <button @click="prev(); startAutoplay()" type="button" aria-label="Video sebelumnya"
                                class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-300 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 motion-reduce:transition-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button @click="next(); startAutoplay()" type="button" aria-label="Video selanjutnya"
                                class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-300 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 motion-reduce:transition-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Kolom Kanan: Slider --}}
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
                                         aria-label="Halaman {{ $pageIndex + 1 }} dari {{ $totalPages }}">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 items-stretch">
                                            @foreach($pageVideos as $videoIndex => $video)
                                                @php
                                                    // Video pertama di halaman pertama = LCP → eager + high
                                                    $isFirst = ($pageIndex === 0 && $videoIndex === 0);
                                                @endphp
                                                <article
                                                    x-data="{
                                                        playing: false,
                                                        play() {
                                                            this.playing = true;
                                                            this.$nextTick(() => this.$refs.iframe?.focus());
                                                        }
                                                    }"
                                                    class="group relative bg-slate-800/95 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl flex flex-col h-full"
                                                >
                                                    {{-- ═══ Thumbnail: 16:9 aspect-ratio untuk cegah CLS ═══ --}}
                                                    <div class="relative aspect-video bg-slate-950 overflow-hidden">
                                                        <template x-if="!playing">
                                                            <button type="button" @click="play()"
                                                                class="absolute inset-0 w-full h-full group/play focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-inset"
                                                                aria-label="Putar video: {{ $video['title'] }}">
                                                                {{-- width/height explicit = no CLS. srcset = responsif. --}}
                                                                <img
                                                                    src="{{ $video['thumb_hd'] }}"
                                                                    srcset="{{ $video['thumb'] }} 320w, {{ $video['thumb_md'] }} 480w, {{ $video['thumb_hd'] }} 1280w"
                                                                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 40vw"
                                                                    width="1280"
                                                                    height="720"
                                                                    alt="Thumbnail: {{ $video['title'] }}"
                                                                    class="w-full h-full object-cover transition-transform duration-500 group-hover/play:scale-105"
                                                                    @if($isFirst)
                                                                        loading="eager"
                                                                        fetchpriority="high"
                                                                        decoding="sync"
                                                                    @else
                                                                        loading="lazy"
                                                                        decoding="async"
                                                                    @endif
                                                                    onerror="this.onerror=null;this.srcset='';this.src='{{ $video['thumb_md'] }}';"
                                                                >
                                                                <span class="absolute inset-0 bg-slate-950/40 group-hover/play:bg-slate-950/20 transition-colors duration-300"></span>
                                                                <span class="absolute inset-0 flex items-center justify-center">
                                                                    <span class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-red-600/95 group-hover/play:bg-red-500 flex items-center justify-center text-white shadow-2xl shadow-red-600/40 transition-all duration-300 group-hover/play:scale-110">
                                                                        <svg class="w-7 h-7 sm:w-8 sm:h-8 ml-1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                            <path d="M8 5v14l11-7z"/>
                                                                        </svg>
                                                                    </span>
                                                                </span>
                                                                @if($video['duration'])
                                                                    <span class="absolute bottom-2 right-2 px-2 py-1 rounded-md bg-slate-950/90 text-white text-xs font-semibold tracking-wide">
                                                                        {{ $video['duration'] }}
                                                                    </span>
                                                                @endif
                                                            </button>
                                                        </template>

                                                        <template x-if="playing">
                                                            {{-- width/height + loading lazy untuk iframe — cegah CLS saat load --}}
                                                            <iframe
                                                                x-ref="iframe"
                                                                src="https://www.youtube-nocookie.com/embed/{{ $video['id'] }}?autoplay=1&rel=0&modestbranding=1"
                                                                title="{{ $video['title'] }}"
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
                                                        <h3 class="text-white font-bold text-base sm:text-lg leading-snug mb-2 line-clamp-2">
                                                            {{ $video['title'] }}
                                                        </h3>
                                                        @if($video['description'])
                                                            <p class="text-slate-400 text-sm leading-relaxed line-clamp-3 flex-grow">
                                                                {{ $video['description'] }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    <div class="h-1 flex overflow-hidden">
                                                        <div class="w-1/3 h-full bg-red-600"></div>
                                                        <div class="w-1/3 h-full bg-amber-400"></div>
                                                        <div class="w-1/3 h-full bg-[#acf7f0]"></div>
                                                    </div>
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
                            <div class="flex justify-center gap-2 mt-6" role="tablist" aria-label="Pilih halaman video">
                                @foreach($pages as $index => $page)
                                    <button
                                        @click="goTo({{ $index }})"
                                        type="button"
                                        :class="activeSlide === {{ $index + 1 }} ? 'w-8 bg-red-500' : 'w-2 bg-slate-700 hover:bg-slate-600'"
                                        class="h-2 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 motion-reduce:transition-none"
                                        :aria-selected="activeSlide === {{ $index + 1 }} ? 'true' : 'false'"
                                        aria-label="Buka halaman {{ $index + 1 }}"
                                        role="tab"
                                    ></button>
                                @endforeach
                            </div>
                        @endif

                    @else
                        {{-- Empty state --}}
                        <div class="w-full text-center py-12 sm:py-16 px-6 rounded-2xl bg-slate-800/80 border-2 border-dashed border-slate-600">
                            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-slate-700/60 flex items-center justify-center">
                                <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-slate-200 text-sm sm:text-base font-semibold mb-1">
                                Belum ada video yang ditambahkan.
                            </p>
                            <p class="text-slate-400 text-xs sm:text-sm">
                                Tambahkan item di repeater <span class="font-mono text-amber-300">video_item</span> melalui editor.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
