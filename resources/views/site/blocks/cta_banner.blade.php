@php
    // ===== Helper: Safe URL (cegah javascript:, data:, vbscript:) =====
    $safeUrl = function ($url, $fallback = '#') {
        if (empty($url) || !is_string($url)) {
            return $fallback;
        }

        $url = trim($url);

        // Tolak scheme berbahaya
        $dangerous = ['javascript:', 'data:', 'vbscript:', 'file:'];
        foreach ($dangerous as $scheme) {
            if (stripos($url, $scheme) === 0) {
                return $fallback;
            }
        }

        // Allow scheme aman: http, https, mailto, tel, relative path, anchor
        if (preg_match('#^(https?:|mailto:|tel:|/|\#)#i', $url)) {
            return $url;
        }

        // Tolak scheme lain yang tidak dikenal
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return $fallback;
        }

        // Relative path
        return $url;
    };

    // ===== Helper: Safe Input (translatable) =====
    $safeInput = function ($block, $field, $default = null) {
        if (!method_exists($block, 'input')) {
            return $default;
        }

        $value = $block->input($field);

        if (is_array($value)) {
            $locale   = app()->getLocale();
            $fallback = config('app.fallback_locale', 'en');

            $picked = $value[$locale]
                ?? $value[$fallback]
                ?? (count($value) ? reset($value) : null);

            return filled($picked) ? $picked : $default;
        }

        return filled($value) ? $value : $default;
    };

    // ===== Ambil Data =====
    $badgeText           = $safeInput($block, 'badge_text');
    $title               = $safeInput($block, 'title');
    $highlightText       = $safeInput($block, 'highlight_text');
    $subtitle            = $safeInput($block, 'subtitle');
    $buttonPrimaryText   = $safeInput($block, 'button_primary_text', 'Daftar Sekarang');
    $buttonSecondaryText = $safeInput($block, 'button_secondary_text');
    $style               = $block->input('style') ?: 'gradient-emerald';
    $alignment           = $block->input('alignment') ?: 'center';
    $size                = $block->input('size') ?: 'normal';
    $showPattern         = (bool) $block->input('show_pattern');

    // ===== ✅ SANITASI URL =====
    $buttonPrimaryUrl   = $safeUrl($block->input('button_primary_url'), '#');
    $buttonSecondaryUrl = $safeUrl($block->input('button_secondary_url'), '#');

    // ===== Background Image (aman dari Twill) =====
    $backgroundImage = null;
    try {
        if (method_exists($block, 'hasImage') && $block->hasImage('background_image')) {
            $img = $block->image('background_image', 'default');
            if (is_string($img) && $img !== '') {
                $backgroundImage = $img;
            }
        }
    } catch (\Throwable $e) {
        $backgroundImage = null;
    }

    // ===== Style Mapping =====
    $styles = [
        'gradient-emerald' => [
            'bg' => 'bg-gradient-to-br from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]',
            'text' => 'text-white',
            'subtext' => 'text-[#f7f0ac]',
            'badge_bg' => 'bg-white/20',
            'badge_text' => 'text-white',
            'badge_border' => 'border-white/30',
            'highlight' => 'text-amber-300',
        ],
        'gradient-blue' => [
            'bg' => 'bg-gradient-to-br from-[#acf7f0] via-[#acf7f0] to-[#f0acf7]',
            'text' => 'text-white',
            'subtext' => 'text-blue-50',
            'badge_bg' => 'bg-white/20',
            'badge_text' => 'text-white',
            'badge_border' => 'border-white/30',
            'highlight' => 'text-amber-300',
        ],
        'gradient-amber' => [
            'bg' => 'bg-gradient-to-br from-amber-500 via-orange-500 to-amber-600',
            'text' => 'text-white',
            'subtext' => 'text-amber-50',
            'badge_bg' => 'bg-white/20',
            'badge_text' => 'text-white',
            'badge_border' => 'border-white/30',
            'highlight' => 'text-white',
        ],
        'gradient-dark' => [
            'bg' => 'bg-gradient-to-br from-slate-800 via-slate-900 to-black',
            'text' => 'text-white',
            'subtext' => 'text-slate-300',
            'badge_bg' => 'bg-white/10',
            'badge_text' => 'text-white',
            'badge_border' => 'border-white/20',
            'highlight' => 'text-[#acf7f0]',
        ],
        'solid-white' => [
            'bg' => 'bg-white border border-slate-200',
            'text' => 'text-slate-900',
            'subtext' => 'text-slate-600',
            'badge_bg' => 'bg-[#acf7f0]/30',
            'badge_text' => 'text-slate-900',
            'badge_border' => 'border-[#f0acf7]',
            'highlight' => 'text-slate-900',
        ],
    ];

    // Fallback kalau style tidak dikenal
    $s = $styles[$style] ?? $styles['gradient-emerald'];

    // ===== Inner Padding =====
    $innerPadding = match($size) {
        'compact' => 'py-8 sm:py-10',
        'large' => 'py-16 sm:py-24 lg:py-28',
        default => 'py-12 sm:py-16 lg:py-20',
    };

    // ===== Alignment =====
    $isSplit = $alignment === 'split';
    $alignClass = match($alignment) {
        'left' => 'text-left items-start',
        'split' => 'text-left items-start md:text-left',
        default => 'text-center items-center',
    };
@endphp

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden" data-block-reveal>

    <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl shadow-slate-900/20 {{ $s['bg'] }} {{ $innerPadding }}">

        {{-- Background Image --}}
        @if($backgroundImage)
            <div class="absolute inset-0 z-0">
                {!! twill_proxy_img($block, 'background_image', [640, 1024, 1920], '100vw', strip_tags($title ?? ''), [], null, ['class' => 'w-full h-full object-cover']) !!}
                <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-black/50 to-black/70"></div>
            </div>
        @endif

        {{-- Pattern Dekoratif --}}
        @if($showPattern)
            <div class="absolute inset-0 z-0 pointer-events-none opacity-20" aria-hidden="true">
                <svg class="absolute top-0 right-0 w-64 h-64 text-white" fill="currentColor" viewBox="0 0 200 200">
                    <circle cx="150" cy="50" r="80" />
                </svg>
                <svg class="absolute bottom-0 left-0 w-80 h-80 text-white" fill="currentColor" viewBox="0 0 200 200">
                    <circle cx="50" cy="150" r="100" />
                </svg>
            </div>

            <div class="absolute inset-0 z-0 opacity-5"
                 style="background-size: 32px 32px; background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px);"></div>
        @endif

        <div class="relative z-10 px-5 sm:px-8 lg:px-12">
            @if($isSplit)
                {{-- ===== Layout Split: Text kiri, Tombol kanan ===== --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 sm:gap-8">

                    <div class="flex-1 space-y-3 sm:space-y-4">
                        @if($badgeText)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full {{ $s['badge_bg'] }} {{ $s['badge_text'] }} {{ $s['badge_border'] }} border text-xs font-bold uppercase tracking-wider">
                                {{ $badgeText }}
                            </div>
                        @endif

                        @if($title)
                            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold {{ $s['text'] }} tracking-tight leading-tight">
                                {{ $title }}
                            </h2>
                        @endif

                        @if($highlightText)
                            <p class="text-lg sm:text-xl font-bold {{ $s['highlight'] }}">
                                {{ $highlightText }}
                            </p>
                        @endif

                        @if($subtitle)
                            <p class="text-sm sm:text-base {{ $s['subtext'] }} leading-relaxed">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 sm:flex-shrink-0">
                        @if($buttonPrimaryText)
                            <a href="{{ $buttonPrimaryUrl }}"
                               class="inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3.5 sm:py-4 rounded-full bg-white text-slate-900 hover:bg-slate-100 font-bold shadow-lg transition-all transform hover:-translate-y-1 text-sm sm:text-base">
                                {{ $buttonPrimaryText }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        @endif

                        @if($buttonSecondaryText && $buttonSecondaryUrl && $buttonSecondaryUrl !== '#')
                            <a href="{{ $buttonSecondaryUrl }}"
                               class="inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3.5 sm:py-4 rounded-full border-2 border-white/30 hover:bg-white/10 {{ $s['text'] }} font-bold backdrop-blur-sm transition-all transform hover:-translate-y-1 text-sm sm:text-base">
                                {{ $buttonSecondaryText }}
                            </a>
                        @endif
                    </div>
                </div>
            @else
                {{-- ===== Layout Center / Left ===== --}}
                <div class="flex flex-col {{ $alignClass }} gap-4 sm:gap-5 max-w-3xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">

                    @if($badgeText)
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full {{ $s['badge_bg'] }} {{ $s['badge_text'] }} {{ $s['badge_border'] }} border text-xs font-bold uppercase tracking-wider shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-current animate-pulse motion-reduce:animate-none"></span>
                            {{ $badgeText }}
                        </div>
                    @endif

                    @if($title)
                        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold {{ $s['text'] }} tracking-tight leading-tight">
                            {{ $title }}
                        </h2>
                    @endif

                    @if($highlightText)
                        <p class="text-lg sm:text-xl md:text-2xl font-bold {{ $s['highlight'] }}">
                            {{ $highlightText }}
                        </p>
                    @endif

                    @if($subtitle)
                        <p class="text-sm sm:text-base md:text-lg {{ $s['subtext'] }} leading-relaxed">
                            {{ $subtitle }}
                        </p>
                    @endif

                    @if($buttonPrimaryText || $buttonSecondaryText)
                        <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 pt-2 {{ $alignment === 'center' ? 'justify-center' : '' }}">

                            @if($buttonPrimaryText)
                                <a href="{{ $buttonPrimaryUrl }}"
                                   class="inline-flex items-center justify-center gap-2 px-7 sm:px-9 py-4 rounded-full bg-white text-slate-900 hover:bg-slate-100 font-extrabold shadow-xl shadow-black/10 transition-all transform hover:-translate-y-1 hover:scale-[1.02] text-sm sm:text-base">
                                    {{ $buttonPrimaryText }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            @endif

                            @if($buttonSecondaryText && $buttonSecondaryUrl && $buttonSecondaryUrl !== '#')
                                <a href="{{ $buttonSecondaryUrl }}"
                                   class="inline-flex items-center justify-center gap-2 px-7 sm:px-9 py-4 rounded-full border-2 border-white/40 hover:bg-white/10 {{ $s['text'] }} font-bold backdrop-blur-sm transition-all transform hover:-translate-y-1 text-sm sm:text-base">
                                    {{ $buttonSecondaryText }}
                                </a>
                            @endif

                        </div>
                    @endif

                </div>
            @endif
        </div>

    </div>

</section>
