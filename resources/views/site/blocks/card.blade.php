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

    $badgeText      = $safeInput($block, 'badge_text', 'Fasilitas & Sarana');
    $titleMain      = $safeInput($block, 'title_main');
    $titleHighlight = $safeInput($block, 'title_highlight');
    $subtitle       = $safeInput($block, 'subtitle');

    $items = $block->children;

    $iconStyles = [
        'bg-[#acf7f0]/45 border-[#acf7f0] text-slate-950',
        'bg-[#f0acf7]/40 border-[#f0acf7] text-slate-950',
        'bg-[#f7f0ac]/60 border-[#f7f0ac] text-slate-950',
    ];
@endphp

<section class="py-10 sm:py-14 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>
    <div class="space-y-10 sm:space-y-12">

        @if(filled($badgeText) || filled($titleMain) || filled($titleHighlight) || filled($subtitle))
            <div class="flex flex-col items-center text-center max-w-3xl mx-auto space-y-3">
                @if(filled($badgeText))
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white border border-slate-200/90 shadow-2xs text-xs font-extrabold text-slate-950 tracking-wide uppercase">
                        <span class="w-2 h-2 rounded-full bg-[#acf7f0]" aria-hidden="true"></span>
                        <span>{{ $badgeText }}</span>
                    </div>
                @endif

                @if(filled($titleMain) || filled($titleHighlight))
                    <h2 class="text-3xl sm:text-4xl lg:text-[2.65rem] font-extrabold text-slate-900 tracking-tight leading-[1.1] px-2 [text-wrap:balance]">
                        @if(filled($titleMain)){{ $titleMain }}@endif
                        @if(filled($titleHighlight))
                            <span class="text-slate-900">
                                {{ $titleHighlight }}
                            </span>
                        @endif
                    </h2>
                    <div class="w-20 h-1 mx-auto rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>
                @endif

                @if(filled($subtitle))
                    <p class="text-sm sm:text-base md:text-lg text-slate-600 px-2 leading-relaxed">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
        @endif

        @if($items && $items->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                @foreach($items as $item)
                    @php
                        $icon       = $safeInput($item, 'icon');
                        $title      = $safeInput($item, 'title');
                        $desc       = $safeInput($item, 'description');
                        $buttonUrl  = $safeInput($item, 'button_url');
                        $buttonText = $safeInput($item, 'button_text');
                        $iconStyle  = $iconStyles[$loop->index % count($iconStyles)];
                    @endphp

                    <div class="group relative bg-white border border-slate-200/90 hover:border-slate-950/20 rounded-3xl p-6 sm:p-7 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col h-full overflow-hidden motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style] [transform:translateZ(0)]">

                        {{-- Smooth 3-color pastel gradient reveal on hover --}}
                        <span class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#f7f0ac]/75 via-[#acf7f0]/75 to-[#f0acf7]/75 opacity-0 group-hover:opacity-100 transition-opacity duration-300 motion-reduce:transition-none" aria-hidden="true"></span>

                        {{-- Crisp top accent line --}}
                        <span class="pointer-events-none absolute top-0 inset-x-6 h-0.5 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] opacity-75 group-hover:opacity-100 group-hover:inset-x-0 group-hover:h-1 transition-all duration-300" aria-hidden="true"></span>

                        <div class="relative z-10 flex justify-center mb-5">
                            <div class="w-16 h-16 rounded-2xl border {{ $iconStyle }} group-hover:bg-white/95 group-hover:border-slate-950/15 flex items-center justify-center shadow-2xs group-hover:shadow-md group-hover:scale-105 transition-all duration-300 motion-reduce:transition-none motion-reduce:group-hover:scale-100 [transform:translateZ(0)]">
                                @if(filled($icon))
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                        {!! safe_svg($icon) !!}
                                    </div>
                                @else
                                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>

                        <div class="relative z-10 text-center flex-grow flex flex-col">
                            @if(filled($title))
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-950 mb-2.5 leading-snug line-clamp-2">
                                    {{ $title }}
                                </h3>
                            @endif

                            @if(filled($desc))
                                <p class="text-xs sm:text-sm text-slate-600 group-hover:text-slate-900 leading-relaxed mb-5 line-clamp-4 transition-colors duration-200">
                                    {{ $desc }}
                                </p>
                            @endif
                        </div>

                        @if(filled($buttonUrl) && filled($buttonText))
                            <div class="relative z-10 mt-auto flex justify-center">
                                <a
                                    href="{{ $buttonUrl }}"
                                    data-analytics="feature-click"
                                    data-feature-title="{{ $title }}"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-950 group-hover:bg-white text-white group-hover:text-slate-950 border border-slate-950/15 text-xs sm:text-sm font-bold rounded-full transition-all duration-300 shadow-2xs focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 motion-reduce:transition-none"
                                >
                                    <span>{{ $buttonText }}</span>
                                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-300 motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</section>
