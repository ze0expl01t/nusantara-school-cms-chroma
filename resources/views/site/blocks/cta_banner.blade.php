@php
    $safeUrl = function ($url, $fallback = '#') {
        if (empty($url) || !is_string($url)) {
            return $fallback;
        }

        $url = trim($url);

        $dangerous = ['javascript:', 'data:', 'vbscript:', 'file:'];
        foreach ($dangerous as $scheme) {
            if (stripos($url, $scheme) === 0) {
                return $fallback;
            }
        }

        if (preg_match('#^(https?:|mailto:|tel:|/|\#)#i', $url)) {
            return $url;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return $fallback;
        }

        return $url;
    };

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

    $badgeText           = $safeInput($block, 'badge_text');
    $title               = $safeInput($block, 'title');
    $highlightText       = $safeInput($block, 'highlight_text');
    $subtitle            = $safeInput($block, 'subtitle');
    $buttonPrimaryText   = $safeInput($block, 'button_primary_text');
    $buttonSecondaryText = $safeInput($block, 'button_secondary_text');
    $style               = $safeInput($block, 'style', 'gradient-emerald');
    $alignment           = $safeInput($block, 'alignment', 'center');
    $size                = $safeInput($block, 'size', 'normal');
    $showPattern         = (bool) $block->input('show_pattern');

    $buttonPrimaryUrl   = $safeUrl($block->input('button_primary_url'), '#');
    $buttonSecondaryUrl = $safeUrl($block->input('button_secondary_url'), '#');

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

    $pastelPreset = [
        'bg' => 'bg-gradient-to-br from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] border border-slate-950/15',
        'text' => 'text-slate-950',
        'subtext' => 'text-slate-800',
        'badge_bg' => 'bg-white/85',
        'badge_text' => 'text-slate-950',
        'badge_border' => 'border-slate-950/15',
        'highlight' => 'inline-block px-3 py-0.5 rounded-xl bg-white/80 text-slate-950 border border-slate-950/10',
        'btn_primary' => 'bg-slate-950 text-white hover:bg-white hover:text-slate-950 border border-slate-950/20',
        'btn_secondary' => 'bg-white/75 hover:bg-white text-slate-950 border border-slate-950/20',
    ];

    $styles = [
        'gradient-emerald' => $pastelPreset,
        'gradient-blue' => [
            'bg' => 'bg-gradient-to-tr from-[#acf7f0] via-[#f7f0ac] to-[#f0acf7] border border-slate-950/15',
            'text' => 'text-slate-950',
            'subtext' => 'text-slate-800',
            'badge_bg' => 'bg-white/85',
            'badge_text' => 'text-slate-950',
            'badge_border' => 'border-slate-950/15',
            'highlight' => 'inline-block px-3 py-0.5 rounded-xl bg-white/80 text-slate-950 border border-slate-950/10',
            'btn_primary' => 'bg-slate-950 text-white hover:bg-white hover:text-slate-950 border border-slate-950/20',
            'btn_secondary' => 'bg-white/75 hover:bg-white text-slate-950 border border-slate-950/20',
        ],
        'gradient-amber' => [
            'bg' => 'bg-gradient-to-br from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] border border-slate-950/15',
            'text' => 'text-slate-950',
            'subtext' => 'text-slate-800',
            'badge_bg' => 'bg-white/85',
            'badge_text' => 'text-slate-950',
            'badge_border' => 'border-slate-950/15',
            'highlight' => 'inline-block px-3 py-0.5 rounded-xl bg-white/80 text-slate-950 border border-slate-950/10',
            'btn_primary' => 'bg-slate-950 text-white hover:bg-white hover:text-slate-950 border border-slate-950/20',
            'btn_secondary' => 'bg-white/75 hover:bg-white text-slate-950 border border-slate-950/20',
        ],
        'gradient-dark' => [
            'bg' => 'bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 border border-slate-800',
            'text' => 'text-white',
            'subtext' => 'text-slate-300',
            'badge_bg' => 'bg-white/10',
            'badge_text' => 'text-[#acf7f0]',
            'badge_border' => 'border-white/20',
            'highlight' => 'inline-block px-3 py-0.5 rounded-xl bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] text-slate-950',
            'btn_primary' => 'bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] text-slate-950 hover:brightness-105',
            'btn_secondary' => 'bg-white/10 hover:bg-white/20 text-white border border-white/25',
        ],
        'solid-white' => [
            'bg' => 'bg-white border border-slate-200/90',
            'text' => 'text-slate-950',
            'subtext' => 'text-slate-600',
            'badge_bg' => 'bg-gradient-to-r from-[#f7f0ac]/60 via-[#acf7f0]/60 to-[#f0acf7]/60',
            'badge_text' => 'text-slate-950',
            'badge_border' => 'border-slate-950/15',
            'highlight' => 'inline-block px-3 py-0.5 rounded-xl bg-gradient-to-r from-[#f7f0ac]/65 via-[#acf7f0]/65 to-[#f0acf7]/65 text-slate-950',
            'btn_primary' => 'bg-slate-950 text-white hover:bg-slate-800',
            'btn_secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-950 border border-slate-200',
        ],
    ];

    $s = $styles[$style] ?? $pastelPreset;

    $innerPadding = match($size) {
        'compact' => 'py-8 sm:py-10',
        'large' => 'py-14 sm:py-20 lg:py-24',
        default => 'py-12 sm:py-16 lg:py-20',
    };

    $isSplit = $alignment === 'split';
    $alignClass = match($alignment) {
        'left' => 'text-left items-start',
        'split' => 'text-left items-start md:text-left',
        default => 'text-center items-center',
    };
@endphp

<section class="py-8 sm:py-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:460px]" data-block-reveal>

    <div class="relative rounded-3xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 {{ $s['bg'] }} {{ $innerPadding }} motion-reduce:transition-none">

        @if($backgroundImage)
            <div class="absolute inset-0 z-0">
                {!! twill_proxy_img($block, 'background_image', [640, 1024, 1920], '100vw', strip_tags($title ?? ''), [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80', ['class' => 'w-full h-full object-cover']) !!}
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950/75 via-slate-950/55 to-slate-950/75"></div>
            </div>
        @endif
        {{-- Unified Chroma soft glow & subtle corner ornaments --}}
        <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden" aria-hidden="true">
            <div class="pointer-events-none absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-[#ffe659] via-[#4ef5e6] to-[#ff7cf2] opacity-80 group-hover:h-1.5 group-hover:opacity-100 transition-all duration-300 motion-reduce:transition-none"></div>
            <div class="pointer-events-none select-none absolute -top-16 -right-16 w-56 h-56 sm:w-72 sm:h-72 rounded-full bg-gradient-to-bl from-[#f0acf7]/30 via-[#acf7f0]/25 to-transparent blur-2xl"></div>
            <div class="pointer-events-none select-none absolute -bottom-16 -left-16 w-56 h-56 sm:w-72 sm:h-72 rounded-full bg-gradient-to-tr from-[#f7f0ac]/35 via-[#acf7f0]/25 to-transparent blur-2xl"></div>
            <svg class="pointer-events-none select-none hidden sm:block absolute top-3 right-3 w-24 h-24 sm:w-28 sm:h-28 text-slate-900/[0.06]" viewBox="0 0 120 120" fill="none">
                <path d="M24 12H108V96" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M52 24H96V68" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <svg class="pointer-events-none select-none hidden sm:block absolute bottom-3 left-3 w-24 h-24 sm:w-28 sm:h-28 text-slate-900/[0.06]" viewBox="0 0 120 120" fill="none">
                <path d="M12 24V108H96" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M24 52V96H68" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </div>

        @if($showPattern)
            <div class="pointer-events-none absolute inset-0 z-0 opacity-20"
                 style="background-size: 32px 32px; background-image: linear-gradient(to right, rgba(15, 23, 42, 0.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(15, 23, 42, 0.08) 1px, transparent 1px);"
                 aria-hidden="true"></div>
        @endif

        <div class="relative z-10 px-5 sm:px-8 lg:px-12">
            @if($isSplit)
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 sm:gap-8">
                    <div class="flex-1 space-y-3 sm:space-y-4">
                        @if(filled($badgeText))
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full {{ $s['badge_bg'] }} {{ $s['badge_text'] }} {{ $s['badge_border'] }} border text-xs font-extrabold uppercase tracking-wider shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-current" aria-hidden="true"></span>
                                <span>{{ $badgeText }}</span>
                            </div>
                        @endif

                        @if(filled($title))
                            <h2 class="text-2xl sm:text-3xl md:text-4xl font-black {{ $s['text'] }} tracking-tight leading-tight [text-wrap:balance]">
                                {{ $title }}
                            </h2>
                        @endif

                        @if(filled($highlightText))
                            <p class="text-base sm:text-lg md:text-xl font-extrabold {{ $s['highlight'] }}">
                                {{ $highlightText }}
                            </p>
                        @endif

                        @if(filled($subtitle))
                            <p class="text-sm sm:text-base {{ $s['subtext'] }} leading-relaxed max-w-2xl">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </div>

                    @if(filled($buttonPrimaryText) || (filled($buttonSecondaryText) && $buttonSecondaryUrl !== '#'))
                        <div class="flex flex-col sm:flex-row gap-3 sm:flex-shrink-0">
                            @if(filled($buttonPrimaryText))
                                <a href="{{ $buttonPrimaryUrl }}"
                                   class="inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3.5 rounded-full {{ $s['btn_primary'] }} font-extrabold shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                                    <span>{{ $buttonPrimaryText }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            @endif

                            @if(filled($buttonSecondaryText) && $buttonSecondaryUrl !== '#')
                                <a href="{{ $buttonSecondaryUrl }}"
                                   class="inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3.5 rounded-full {{ $s['btn_secondary'] }} font-bold transition-all duration-200 transform hover:-translate-y-0.5 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                                    <span>{{ $buttonSecondaryText }}</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @else
                <div class="flex flex-col {{ $alignClass }} gap-4 sm:gap-5 max-w-3xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">
                    @if(filled($badgeText))
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full {{ $s['badge_bg'] }} {{ $s['badge_text'] }} {{ $s['badge_border'] }} border text-xs font-extrabold uppercase tracking-wider shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-current" aria-hidden="true"></span>
                            <span>{{ $badgeText }}</span>
                        </div>
                    @endif

                    @if(filled($title))
                        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black {{ $s['text'] }} tracking-tight leading-tight [text-wrap:balance]">
                            {{ $title }}
                        </h2>
                    @endif

                    @if(filled($highlightText))
                        <p class="text-base sm:text-lg md:text-xl font-extrabold {{ $s['highlight'] }}">
                            {{ $highlightText }}
                        </p>
                    @endif

                    @if(filled($subtitle))
                        <p class="text-sm sm:text-base md:text-lg {{ $s['subtext'] }} leading-relaxed">
                            {{ $subtitle }}
                        </p>
                    @endif

                    @if(filled($buttonPrimaryText) || (filled($buttonSecondaryText) && $buttonSecondaryUrl !== '#'))
                        <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 pt-2 {{ $alignment === 'center' ? 'justify-center' : '' }}">
                            @if(filled($buttonPrimaryText))
                                <a href="{{ $buttonPrimaryUrl }}"
                                   class="inline-flex items-center justify-center gap-2 px-7 sm:px-9 py-3.5 sm:py-4 rounded-full {{ $s['btn_primary'] }} font-extrabold shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                                    <span>{{ $buttonPrimaryText }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            @endif

                            @if(filled($buttonSecondaryText) && $buttonSecondaryUrl !== '#')
                                <a href="{{ $buttonSecondaryUrl }}"
                                   class="inline-flex items-center justify-center gap-2 px-7 sm:px-9 py-3.5 sm:py-4 rounded-full {{ $s['btn_secondary'] }} font-bold transition-all duration-200 transform hover:-translate-y-0.5 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                                    <span>{{ $buttonSecondaryText }}</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>

    </div>

</section>
