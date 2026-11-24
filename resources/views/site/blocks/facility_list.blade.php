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
    $titleMain      = $safeInput($block, 'title_main', 'Sarana');
    $titleHighlight = $safeInput($block, 'title_highlight', '& Prasarana');
    $subtitle       = $safeInput($block, 'subtitle', 'Berbagai fasilitas pendukung kegiatan belajar mengajar dan praktik siswa.');
    $limit          = (int) $safeInput($block, 'limit', 6);
    $showBtn        = (bool) $safeInput($block, 'show_button', false);
    $btnText        = $safeInput($block, 'button_text');

    $items = \App\Models\Facility::where('published', true)
        ->with(['slugs'])
        ->orderBy('position')
        ->limit($limit > 0 ? $limit : 6)
        ->get();

    $iconStyles = [
        'bg-[#acf7f0]/45 border-[#acf7f0] text-slate-950',
        'bg-[#f0acf7]/40 border-[#f0acf7] text-slate-950',
        'bg-[#f7f0ac]/60 border-[#f7f0ac] text-slate-950',
    ];
@endphp

<section class="py-10 sm:py-14 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:700px]" data-block-reveal>
    <div class="relative z-10 space-y-10 sm:space-y-12">

        @if(filled($badgeText) || filled($titleMain) || filled($titleHighlight) || filled($subtitle))
            <div class="flex flex-col items-center text-center space-y-3">
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
                    <p class="text-sm sm:text-base md:text-lg text-slate-600 max-w-2xl px-2 leading-relaxed">{{ $subtitle }}</p>
                @endif
            </div>
        @endif

        @if($items->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                @foreach($items as $item)
                    @php
                        $slug = $item->slug
                            ?? optional($item->slugs->where('locale', app()->getLocale())->first())->slug
                            ?? optional($item->slugs->first())->slug;
                        $iconStyle = $iconStyles[$loop->index % count($iconStyles)];
                    @endphp

                    <a href="{{ route('facility.show', $slug) }}"
                       aria-label="{{ $item->title }}"
                       class="group relative bg-white border border-slate-200/90 hover:border-slate-950/20 rounded-3xl p-6 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col h-full overflow-hidden motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style] [transform:translateZ(0)]">

                        <span class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#f7f0ac]/75 via-[#acf7f0]/75 to-[#f0acf7]/75 opacity-0 group-hover:opacity-100 transition-opacity duration-300 motion-reduce:transition-none" aria-hidden="true"></span>
                        <span class="pointer-events-none absolute top-0 inset-x-6 h-0.5 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] opacity-75 group-hover:opacity-100 group-hover:inset-x-0 group-hover:h-1 transition-all duration-300" aria-hidden="true"></span>

                        <div class="relative z-10 flex flex-col items-center text-center mb-4">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl border {{ $iconStyle }} group-hover:bg-white/95 group-hover:border-slate-950/15 flex items-center justify-center shadow-2xs group-hover:shadow-md group-hover:scale-105 transition-all duration-300 motion-reduce:transition-none motion-reduce:group-hover:scale-100 mb-3.5">
                                @if($item->icon)
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                        {!! safe_svg($item->icon) !!}
                                    </div>
                                @else
                                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                @endif
                            </div>

                            <h3 class="text-base sm:text-lg font-extrabold text-slate-950 leading-snug line-clamp-2">
                                {{ $item->title }}
                            </h3>

                            @if(filled($item->badge))
                                <span class="mt-2 inline-flex items-center px-3 py-0.5 rounded-full bg-slate-100/90 group-hover:bg-white/90 text-slate-900 text-xs font-bold border border-slate-200/80 transition-colors duration-200">
                                    {{ $item->badge }}
                                </span>
                            @endif
                        </div>

                        @if($item->stat_value !== null && $item->stat_value !== '')
                            <div class="relative z-10 flex items-baseline justify-between px-3.5 py-2 rounded-2xl bg-slate-50/90 group-hover:bg-white/90 border border-slate-200/70 mb-2 transition-colors duration-200">
                                @if(filled($item->stat_label))
                                    <span class="text-xs text-slate-700 group-hover:text-slate-950 font-semibold">{{ $item->stat_label }}</span>
                                @endif
                                <span class="text-lg font-black text-slate-950 tabular-nums leading-none">
                                    {{ $item->stat_value }}
                                </span>
                            </div>
                        @endif

                        @if($item->capacity_value !== null && $item->capacity_value !== '')
                            <div class="relative z-10 flex items-baseline justify-between px-3.5 py-2 rounded-2xl bg-slate-50/90 group-hover:bg-white/90 border border-slate-200/70 mb-2 transition-colors duration-200">
                                @if(filled($item->capacity_label))
                                    <span class="text-xs text-slate-700 group-hover:text-slate-950 font-semibold">{{ $item->capacity_label }}</span>
                                @endif
                                <span class="text-base font-black text-slate-950 tabular-nums leading-none">{{ $item->capacity_value }}</span>
                            </div>
                        @endif

                        @if(filled($item->description))
                            <p class="relative z-10 text-xs sm:text-sm text-slate-600 group-hover:text-slate-900 text-center leading-relaxed line-clamp-3 mt-auto transition-colors duration-200">
                                {{ strip_tags($item->description) }}
                            </p>
                        @endif
                    </a>
                @endforeach
            </div>

            @if($showBtn && filled($btnText))
                <div class="flex justify-center pt-2">
                    <a href="{{ url('/fasilitas') }}"
                       class="inline-flex items-center gap-2 px-6 sm:px-8 py-3.5 rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] hover:brightness-105 text-slate-950 font-extrabold shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                        <span>{{ $btnText }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            @endif
        @endif

    </div>
</section>
