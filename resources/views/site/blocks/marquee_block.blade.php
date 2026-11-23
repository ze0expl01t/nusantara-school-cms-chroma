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

    $titleMain = $safeInput($block, 'title_main');
    $items     = $block->children;
    $totalItems = $items ? $items->count() : 0;
@endphp

<section class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto overflow-hidden [content-visibility:auto] [contain-intrinsic-size:150px]" data-block-reveal>
    <div class="space-y-5 sm:space-y-6">

        {{-- Judul Section --}}
        @if($titleMain)
            <div class="text-center">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight px-2 [text-wrap:balance]">
                    {{ $titleMain }}
                </h2>
            </div>
        @endif

        {{-- Marquee Container --}}
        <div
            x-data="{
                paused: false,
                reducedMotion: false,
                isTabVisible: true,
                initialized: false,

                init() {
                    this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    this.$nextTick(() => this.setupMarquee());

                    document.addEventListener('visibilitychange', () => {
                        this.isTabVisible = !document.hidden;
                    });
                },

                setupMarquee() {
                    if (this.initialized) return;
                    const track = this.$refs.track;
                    if (!track) return;

                    const originals = Array.from(track.children);
                    if (originals.length === 0) return;

                    for (let i = 0; i < 2; i++) {
                        originals.forEach(el => {
                            const clone = el.cloneNode(true);
                            clone.setAttribute('aria-hidden', 'true');

                            clone.setAttribute('inert', '');
                            clone.setAttribute('tabindex', '-1');
                            clone.querySelectorAll('a').forEach(a => a.setAttribute('tabindex', '-1'));
                            track.appendChild(clone);
                        });
                    }

                    this.initialized = true;
                },

                isRunning() {
                    return !this.paused && !this.reducedMotion && this.isTabVisible;
                }
            }"
            @mouseenter="paused = true"
            @mouseleave="paused = false"
            @touchstart.passive="paused = true"
            @touchend.passive="paused = false"
            @focusin="paused = true"
            @focusout="paused = false"
            class="relative w-full bg-white rounded-2xl sm:rounded-3xl py-4 sm:py-5 shadow-sm overflow-hidden transition-[box-shadow,border-color] duration-500 border border-slate-200 hover:shadow-xl hover:shadow-slate-950/5 hover:border-[#f0acf7]/50 motion-reduce:transition-none [contain:layout_style_paint]"
            role="region"
            aria-label="Marquee informasi"
        >
            {{-- Gradient Fade Sisi Kiri & Kanan --}}
            <div class="absolute top-0 bottom-0 left-0 w-10 sm:w-16 bg-gradient-to-r from-white via-white/60 to-transparent z-20 pointer-events-none" aria-hidden="true"></div>
            <div class="absolute top-0 bottom-0 right-0 w-10 sm:w-16 bg-gradient-to-l from-white via-white/60 to-transparent z-20 pointer-events-none" aria-hidden="true"></div>

            @if($items && $totalItems > 0)
                {{-- Wrapper Utama --}}
                <div class="flex w-full overflow-hidden select-none">

                    {{-- Track Marquee — GPU accelerated ═══ --}}
                    <div
                        x-ref="track"
                        class="flex w-max items-center will-change-transform marquee-track [transform:translateZ(0)] [backface-visibility:hidden]"
                        :class="isRunning() ? 'is-running' : 'is-paused'"
                        style="animation-duration: {{ max(15, min(40, $totalItems * 4)) }}s;"
                    >
                        {{-- Item Asli dari Database --}}
                        <div class="flex items-center gap-4 sm:gap-6 px-3 sm:px-4 shrink-0">
                            @foreach($items as $item)
                                @php
                                    $text = $safeInput($item, 'text', 'EXCELLENCE');
                                    $url  = $safeInput($item, 'url');
                                @endphp

                                <div class="flex items-center gap-4 sm:gap-6 shrink-0 group/item">
                                    @if($url)
                                        <a
                                            href="{{ $url }}"
                                            data-analytics="marquee-click"
                                            class="text-xl sm:text-2xl md:text-4xl font-black uppercase tracking-wider text-slate-700 hover:text-slate-950 transition-[color,transform] duration-300 transform hover:scale-105 whitespace-nowrap focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 rounded motion-reduce:transition-none motion-reduce:hover:scale-100 [transform:translateZ(0)]"
                                        >
                                            {{ $text }}
                                        </a>
                                    @else
                                        <span class="text-xl sm:text-2xl md:text-4xl font-black uppercase tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-slate-800 via-slate-600 to-[#f0acf7] transition-transform duration-300 transform group-hover/item:scale-105 whitespace-nowrap motion-reduce:transition-none motion-reduce:group-hover/item:scale-100 [transform:translateZ(0)]">
                                            {{ $text }}
                                        </span>
                                    @endif

                                    {{-- Separator Dot Bercahaya ═══ --}}
                                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-[#acf7f0] shadow-[0_0_10px_rgba(16,185,129,0.9)] shrink-0 transition-transform duration-300 group-hover/item:scale-125 motion-reduce:transition-none motion-reduce:group-hover/item:scale-100 [transform:translateZ(0)]" aria-hidden="true"></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-6 text-slate-400 text-xs italic">
                    Belum ada item marquee yang ditambahkan di panel admin.
                </div>
            @endif

        </div>
    </div>
</section>

@pushOnce('styles')
    <style>
        /* Marquee: 3x konten → geser -33.333% agar seamless.
           Pakai translate3d → GPU accelerated, no paint cost. */
        @keyframes tallMarquee {
            0%   { transform: translate3d(0, 0, 0); }
            100% { transform: translate3d(-33.3333%, 0, 0); }
        }

        .marquee-track {
            animation-name: tallMarquee;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
            will-change: transform;
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        .marquee-track.is-running {
            animation-play-state: running;
        }

        .marquee-track.is-paused {
            animation-play-state: paused;
        }

        /* Respect reduced motion: stop marquee total */
        @media (prefers-reduced-motion: reduce) {
            .marquee-track {
                animation: none !important;
                transform: translateX(0) !important;
                will-change: auto;
            }
        }
    </style>
@endPushOnce
