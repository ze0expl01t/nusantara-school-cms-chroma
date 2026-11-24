@php
    // ===== Helper: ambil field translatable =====
    $safeInput = function ($block, $field, $default = null) {
        $value = $block->input($field);
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? $default;
        }
        return filled($value) ? $value : $default;
    };

    // ===== Parse animated words =====
    $animatedWordsRaw = $block->translatedInput('animated_words')
        ?: "Workplace Safety.\nHealth Protection.\nEnvironmental Care.\nRisk Management.";

    $animatedWords = array_values(array_filter(
        array_map('trim', preg_split('/\r\n|\r|\n/', $animatedWordsRaw))
    ));

    if (empty($animatedWords)) {
        $animatedWords = [
            'Workplace Safety.',
            'Health Protection.',
            'Environmental Care.',
            'Risk Management.',
        ];
    }

    $animatedWordsJson = json_encode($animatedWords, JSON_UNESCAPED_UNICODE);

    $longestWord = collect($animatedWords)
        ->sortByDesc(fn ($w) => mb_strlen($w))
        ->first();

    // ===== Title =====
    $titleHtml = $safeInput($block, 'title', 'Empowering your');
    $titleHtml = preg_replace('/<br\s*\/?>/i', ' ', $titleHtml);
    $titleHtml = trim($titleHtml);

    // ===== Badge, Description, Buttons =====
    $badge = $safeInput($block, 'badge');
    $description = $safeInput($block, 'text');

    $primaryText   = $safeInput($block, 'btn_primary_text');
    $primaryUrl    = $safeInput($block, 'btn_primary_url');
    $secondaryText = $safeInput($block, 'btn_secondary_text');
    $secondaryUrl  = $safeInput($block, 'btn_secondary_url');

    // ===== Background image =====
    $hasBgImage = $block->hasImage('background_image', 'desktop') || $block->hasImage('background_image');

    $fallbackImage = 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1920&q=80';

    $bgAlt = trim(strip_tags($titleHtml)) ?: 'Hero background';

    // ═══ LCP Preload untuk background image ═══
    $bgMedia = $hasBgImage ? twill_media($block, 'background_image') : null;
    $bgMediaPath = $bgMedia ? twill_media_path($bgMedia) : null;
@endphp

{{-- ═══ LCP Preload ═══ --}}
@if($bgMediaPath)
    @push('head')
        <link rel="preload"
              as="image"
              href="{{ url('media/' . $bgMediaPath . '?format=webp&w=1920') }}"
              imagesrcset="{{ url('media/' . $bgMediaPath . '?format=webp&w=640') }} 640w,
                           {{ url('media/' . $bgMediaPath . '?format=webp&w=1024') }} 1024w,
                           {{ url('media/' . $bgMediaPath . '?format=webp&w=1920') }} 1920w"
              imagesizes="100vw"
              fetchpriority="high">
    @endpush
@endif

<section class="relative min-h-[70vh] sm:min-h-[80vh] flex items-center justify-center overflow-hidden px-4 sm:px-6 lg:px-8 py-12 sm:py-16 [contain:layout_style]" data-block-reveal>

    {{-- Background Layer --}}
    <div class="absolute inset-0 z-0">
        {{-- Background Image — LCP, eager + high priority, sync decode ═══ --}}
        @php
                $heroBgHtml = twill_proxy_img(
                    $block,
                    'background_image',
                    [640, 1024, 1920],
                    '100vw',
                    $bgAlt,
                    [],
                    $fallbackImage,
                    [
                        'class' => 'w-full h-full object-cover',
                        'width' => '1920',
                        'height' => '1080',
                        'loading' => 'eager',
                        'fetchpriority' => 'high',
                        'decoding' => 'sync',
                        'onerror' => "this.onerror=null;this.removeAttribute('srcset');this.src='" . $fallbackImage . "';",
                    ]
                );
            @endphp
            @if(filled(trim($heroBgHtml)))
                {!! $heroBgHtml !!}
            @else
                <img
                    src="{{ $fallbackImage }}"
                    alt="{{ $bgAlt }}"
                    class="w-full h-full object-cover"
                    width="1920"
                    height="1080"
                    loading="eager"
                    fetchpriority="high"
                    decoding="sync"
                    onerror="this.onerror=null;this.style.display='none';"
                >
            @endif

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-slate-950/80 mix-blend-multiply" aria-hidden="true"></div>


{{-- Glow decoratif — animasi DIPERTAHANKAN --}}
        <div class="absolute top-1/4 left-1/4 w-56 h-56 sm:w-96 sm:h-96 bg-[#acf7f0] rounded-full mix-blend-screen blur-[120px] opacity-20 animate-pulse motion-reduce:animate-none pointer-events-none" aria-hidden="true"></div>
    </div>

    {{-- Content --}}
    <div class="relative z-10 text-center w-full max-w-5xl mx-auto space-y-6 sm:space-y-8">

        {{-- Badge --}}
        @if($badge)
            <div class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-full bg-white border border-slate-200/90 text-slate-950 text-xs sm:text-sm font-bold shadow-sm">
                <span class="w-2 h-2 rounded-full bg-[#acf7f0]" aria-hidden="true"></span>
                {{ $badge }}
            </div>
        @endif

        {{-- Heading: Title + Animated Word --}}
        <div
            x-data="{
                words: {{ $animatedWordsJson }},
                index: 0,
                interval: null,
                reducedMotion: false,
                isTabVisible: true,

                init() {
                    this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    if (!this.reducedMotion && this.words.length > 1) {
                        this.startAutoplay();
                    }

                    document.addEventListener('visibilitychange', () => {
                        this.isTabVisible = !document.hidden;
                        if (document.hidden) {
                            this.stopAutoplay();
                        } else {
                            this.startAutoplay();
                        }
                    });
                },

                startAutoplay() {
                    if (this.reducedMotion || !this.isTabVisible) return;
                    this.stopAutoplay();
                    this.interval = setInterval(() => {
                        this.index = (this.index + 1) % this.words.length;
                    }, 2500);
                },

                stopAutoplay() {
                    if (this.interval) {
                        clearInterval(this.interval);
                        this.interval = null;
                    }
                }
            }"
            class="flex flex-col items-center gap-1 sm:gap-2"
        >
            {{-- Baris 1: Title --}}
            <h1 class="text-3xl sm:text-5xl md:text-6xl font-extrabold text-white tracking-tight leading-[1.15] px-2 [text-wrap:balance]">
                {!! safe_html($titleHtml) !!}
            </h1>

            {{-- Baris 2: Animated Word --}}
            <div class="text-3xl sm:text-5xl md:text-6xl font-extrabold tracking-tight leading-[1.15] text-[#acf7f0] px-2">
                <span class="inline-grid align-bottom text-center">
                    <template x-for="(word, i) in words" :key="i">
                        <span
                            x-show="index === i"
                            x-transition:enter="transition ease-out duration-500 motion-reduce:duration-0"
                            x-transition:enter-start="opacity-0 translate-y-6 motion-reduce:transform-none"
                            x-transition:enter-end="opacity-100 translate-y-0 motion-reduce:transform-none"
                            x-transition:leave="transition ease-in duration-500 motion-reduce:duration-0"
                            x-transition:leave-start="opacity-100 translate-y-0 motion-reduce:transform-none"
                            x-transition:leave-end="opacity-0 -translate-y-6 motion-reduce:transform-none"
                            class="col-start-1 row-start-1 text-transparent bg-clip-text bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] leading-[1.15] will-change-transform [transform:translateZ(0)]"
                            x-text="word"
                        ></span>
                    </template>

                    <span class="col-start-1 row-start-1 invisible pointer-events-none" aria-hidden="true">{{ $longestWord }}</span>
                </span>
            </div>
        </div>

        {{-- Description --}}
        @if($description)
            <div class="text-sm sm:text-base md:text-lg lg:text-xl text-slate-200 max-w-2xl mx-auto leading-relaxed px-2 font-medium prose prose-invert prose-sm sm:prose-base prose-p:my-0 prose-p:leading-relaxed prose-strong:text-white">
                {!! safe_html($description) !!}
            </div>
        @endif

        {{-- CTA Buttons --}}
        @if(($primaryText && $primaryUrl) || ($secondaryText && $secondaryUrl))
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 pt-2 sm:pt-4">

                {{-- Primary CTA --}}
                @if($primaryText && $primaryUrl)
                    <a
                        href="{{ $primaryUrl }}"
                        data-analytics="hero-cta-primary"
                        class="w-full sm:w-auto px-6 sm:px-8 py-3.5 sm:py-4 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold shadow-lg shadow-slate-950/20 transition-[transform,background-color,box-shadow] duration-300 transform hover:-translate-y-1 text-center text-sm sm:text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-[#f7f0ac] focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [transform:translateZ(0)]"
                    >
                        {{ $primaryText }}
                    </a>
                @endif

                {{-- Secondary CTA --}}
                @if($secondaryText && $secondaryUrl)
                    <a
                        href="{{ $secondaryUrl }}"
                        data-analytics="hero-cta-secondary"
                        class="w-full sm:w-auto px-6 sm:px-8 py-3.5 sm:py-4 rounded-full bg-white/10 hover:bg-white/20 text-white font-bold border border-white/20 backdrop-blur-md transition-[transform,background-color,border-color] duration-300 transform hover:-translate-y-1 text-center text-sm sm:text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [transform:translateZ(0)]"
                    >
                        {{ $secondaryText }}
                    </a>
                @endif

            </div>
        @endif

    </div>
</section>
