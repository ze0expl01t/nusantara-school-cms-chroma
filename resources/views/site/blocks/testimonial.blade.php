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

    $titleMain      = $safeInput($block, 'title_main', "What they're");
    $titleHighlight = $safeInput($block, 'title_highlight', 'talking about');
    $subtitle       = $safeInput($block, 'subtitle', 'Pendapat dan pengalaman dari para pengguna mengenai Portal HSE PT. Nama Perusahaan.');

    $items      = $block->children;
    $totalItems = $items ? $items->count() : 0;

    $perPage    = 2;
    $pages      = $totalItems > 0 ? $items->chunk($perPage) : collect();
    $totalPages = $pages->count();
@endphp

<section class="py-8 sm:py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 [content-visibility:auto] [contain-intrinsic-size:700px]" data-block-reveal>

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
                        requestAnimationFrame(() => {
                            this.isJumping = false;
                        });
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
                this.autoplay = setInterval(() => this.next(), 5000);
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
        aria-label="Testimoni"
        aria-roledescription="carousel"
        class="relative bg-slate-900 text-slate-100 rounded-3xl px-5 sm:px-8 lg:px-10 py-8 sm:py-10 lg:py-16 [contain:layout_style]"
    >
        {{-- Ambient Glow — blur dikurangi untuk hemat GPU paint --}}
        <div class="absolute inset-0 overflow-hidden rounded-3xl pointer-events-none" aria-hidden="true">
            <div class="absolute top-1/2 left-0 -translate-y-1/2 w-64 h-64 sm:w-[500px] sm:h-[500px] bg-[#f0acf7]/10 blur-[80px] rounded-full [transform:translate3d(0,-50%,0)]"></div>
        </div>

        <div class="relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 sm:gap-12 lg:gap-8 items-center">

                {{-- Kolom Kiri --}}
                <div class="lg:col-span-4 space-y-5 sm:space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-3">
                        <span class="w-8 h-0.5 bg-[#acf7f0]" aria-hidden="true"></span>
                        <span class="text-[11px] sm:text-xs font-bold tracking-widest text-[#acf7f0] uppercase">Testimonials</span>
                    </div>

                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.1]">
                        {{ $titleMain }}
                        @if($titleHighlight)
                            <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-amber-400">
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
                            <button
                                @click="prev(); startAutoplay()"
                                type="button"
                                aria-label="Testimoni sebelumnya"
                                class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:bg-[#f7f0ac] hover:text-white hover:border-[#f0acf7] transition-[background-color,border-color,color] duration-300 shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 motion-reduce:transition-none"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button
                                @click="next(); startAutoplay()"
                                type="button"
                                aria-label="Testimoni selanjutnya"
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

                        <div class="overflow-hidden pt-10 pb-4" aria-live="polite" aria-atomic="false">
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
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-14 md:gap-y-6 gap-x-6 items-stretch">
                                            @foreach($pageItems as $item)
                                                @php
                                                    $hasPhoto = $item->hasImage('avatar', 'desktop')
                                                             || $item->hasImage('avatar', 'default')
                                                             || $item->hasImage('avatar');

                                                    $name    = $safeInput($item, 'name', 'Nama Pemberi Testimoni');
                                                    $role    = $safeInput($item, 'role', 'Profesi');
                                                    $rating  = min(max((int) $safeInput($item, 'rating', 5), 1), 5);
                                                    $content = $safeInput($item, 'content', 'Isi testimoni...');

                                                    $imageAlt = trim(strip_tags($name)) ?: 'Avatar';
                                                    $initial  = mb_substr($name, 0, 1);
                                                @endphp

                                                <div class="relative bg-slate-800/95 border border-slate-700/80 rounded-2xl px-5 pb-5 pt-20 sm:px-6 sm:pb-6 sm:pt-24 shadow-xl flex flex-col h-full [contain:layout_style] [transform:translateZ(0)]">

                                                    <div class="absolute bottom-0 left-0 right-0 h-1 flex overflow-hidden rounded-b-2xl opacity-90" aria-hidden="true">
                                                        <div class="w-1/3 h-full bg-[#f0acf7]"></div>
                                                        <div class="w-1/3 h-full bg-[#acf7f0]"></div>
                                                        <div class="w-1/3 h-full bg-amber-400"></div>
                                                    </div>

                                                    <div class="absolute -top-7 left-5 sm:left-6">
                                                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-1 bg-slate-800 border border-slate-700 shadow-lg overflow-hidden">
                                                            @if($hasPhoto)
                                                                {!! twill_proxy_img(
                                                                    $item,
                                                                    'avatar',
                                                                    [64, 128],
                                                                    '64px',
                                                                    $imageAlt,
                                                                    [],
                                                                    null,
                                                                    [
                                                                        'class' => 'w-full h-full object-cover rounded-full',
                                                                        'width' => '128',
                                                                        'height' => '128',
                                                                        'loading' => 'lazy',
                                                                        'decoding' => 'async',
                                                                    ]
                                                                ) !!}
                                                            @else
                                                                <div class="w-full h-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold text-xl select-none rounded-full" aria-hidden="true">
                                                                    {{ $initial }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <svg class="hidden sm:block absolute top-5 right-5 w-12 h-12 text-slate-700/30 pointer-events-none" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                                                    </svg>

                                                    <div class="space-y-3 relative z-10 flex-grow flex flex-col">
                                                        <div class="flex items-center gap-1" role="img" aria-label="Rating {{ $rating }} dari 5 bintang">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <svg class="w-4 h-4 {{ $i <= $rating ? 'text-amber-400' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path d="M12 .587l3.668 7.431 8.2-1.192-5.934 5.787 1.399 8.168-7.333-3.854-7.333 3.854 1.399-8.168-5.934-5.787 8.2 1.192z"/>
                                                                </svg>
                                                            @endfor
                                                        </div>

                                                        <div>
                                                            <h4 class="text-white font-bold text-base sm:text-lg leading-snug">
                                                                {{ $name }}
                                                            </h4>
                                                            @if($role)
                                                                <p class="text-xs sm:text-sm text-[#acf7f0] font-medium">
                                                                    {{ $role }}
                                                                </p>
                                                            @endif
                                                        </div>

                                                        <p class="text-slate-300 text-sm leading-relaxed flex-grow">
                                                            "{!! nl2br(e($content)) !!}"
                                                        </p>
                                                    </div>
                                                </div>
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
                            <div class="flex justify-center gap-2 mt-4" role="tablist" aria-label="Pilih halaman testimoni">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <p class="text-slate-400 text-sm font-medium">
                                Belum ada item testimoni yang ditambahkan.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
