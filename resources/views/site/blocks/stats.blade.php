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

    $badgeText      = $safeInput($block, 'badge_text', 'Statistik Sekolah');
    $titleMain      = $safeInput($block, 'title_main');
    $titleHighlight = $safeInput($block, 'title_highlight');
    $subtitle       = $safeInput($block, 'subtitle');

    $stats = $block->children;

    $uniformBadgeClass = 'bg-[#acf7f0]/55 text-slate-950 border-[#acf7f0]';
@endphp

<section class="py-8 sm:py-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto [content-visibility:auto] [contain-intrinsic-size:420px]" data-block-reveal>
    <div class="bg-white border border-slate-200/90 rounded-2xl sm:rounded-3xl overflow-hidden shadow-xs hover:shadow-md transition-shadow duration-300 relative [contain:layout_style]">

        {{-- Top 3-color global accent strip --}}
        <div class="h-1.5 w-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>

        <div class="relative z-10 p-6 sm:p-8 md:p-12 space-y-8 sm:space-y-10">

            @if(filled($badgeText) || filled($titleMain) || filled($titleHighlight) || filled($subtitle))
                <div class="text-center space-y-3 max-w-3xl mx-auto">
                    @if(filled($badgeText))
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white border border-slate-200/90 text-xs font-bold text-slate-950 shadow-2xs">
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
                        <p class="text-sm sm:text-base text-slate-600 px-2 leading-relaxed">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            @if($stats && $stats->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    @foreach($stats as $stat)
                        @php
                            $number = $safeInput($stat, 'number');
                            $suffix = $safeInput($stat, 'suffix');
                            $label  = $safeInput($stat, 'label');
                        @endphp

                        <div class="relative rounded-2xl bg-slate-50/75 border border-slate-200/90 hover:border-slate-950/20 p-5 sm:p-6 flex flex-col justify-between gap-3 overflow-hidden group transition-all duration-300 hover:-translate-y-1 shadow-2xs hover:shadow-lg motion-reduce:transition-none motion-reduce:hover:translate-y-0">

                            {{-- Smooth 3-color global pastel gradient on hover (#f7f0ac -> #acf7f0 -> #f0acf7) --}}
                            <span class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#f7f0ac]/80 via-[#acf7f0]/80 to-[#f0acf7]/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300 motion-reduce:transition-none" aria-hidden="true"></span>

                            <div class="relative z-10 flex items-baseline gap-2 transform transition-transform duration-300 group-hover:translate-x-0.5 motion-reduce:transition-none motion-reduce:group-hover:translate-x-0">
                                @if(filled($number))
                                    <span class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-950 tabular-nums leading-none tracking-tight">
                                        {{ $number }}
                                    </span>
                                @endif

                                @if(filled($suffix))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs sm:text-sm font-extrabold uppercase tracking-wider group-hover:bg-white/90 group-hover:border-slate-950/15 transition-colors duration-200 {{ $uniformBadgeClass }}">
                                        {{ $suffix }}
                                    </span>
                                @endif
                            </div>

                            @if(filled($label))
                                <p class="relative z-10 text-xs sm:text-sm font-bold tracking-[0.12em] text-slate-800 group-hover:text-slate-950 uppercase leading-snug transition-colors duration-200">
                                    {{ $label }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</section>
