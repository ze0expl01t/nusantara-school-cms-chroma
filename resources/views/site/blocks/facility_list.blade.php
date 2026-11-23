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
    $titleMain      = $safeInput($block, 'title_main', 'Pendidikan');
    $titleHighlight = $safeInput($block, 'title_highlight', '& Fasilitas');
    $subtitle       = $safeInput($block, 'subtitle');
    $limit          = (int) $safeInput($block, 'limit', 6);
    $showBtn        = (bool) $safeInput($block, 'show_button', false);
    $btnText        = $safeInput($block, 'button_text', 'Lihat Semua Fasilitas');

    // Eager load slugs → hindari N+1 query
    $items = \App\Models\Facility::where('published', true)
        ->with(['slugs'])
        ->orderBy('position')
        ->limit($limit > 0 ? $limit : 6)
        ->get();
@endphp

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:700px]" data-block-reveal>

    {{-- Ambient glow — blur dikurangi untuk hemat GPU paint --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[80px] rounded-full pointer-events-none [transform:translate3d(-50%,-50%,0)]"></div>

    <div class="relative z-10 space-y-8 sm:space-y-10">

        {{-- Header Section --}}
        <div class="flex flex-col items-center text-center space-y-3 pb-4 sm:pb-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-medium text-slate-700">
                <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none" aria-hidden="true"></span>
                {{ $badgeText }}
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2">
                {{ $titleMain }}
                @if($titleHighlight)
                    <span class="text-slate-900">{{ $titleHighlight }}</span>
                @endif
            </h2>
            <div class="w-20 h-1 bg-gradient-to-r from-transparent via-[#acf7f0] to-transparent rounded-full" aria-hidden="true"></div>
            @if($subtitle)
                <p class="text-base md:text-lg text-slate-600 max-w-2xl px-2 pt-2">{{ $subtitle }}</p>
            @endif
        </div>

        @if($items->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach($items as $item)
                    @php
                        $slug = $item->slug
                            ?? optional($item->slugs->where('locale', app()->getLocale())->first())->slug
                            ?? optional($item->slugs->first())->slug;
                    @endphp

                    <a href="{{ route('facility.show', $slug) }}"
                       aria-label="Lihat detail fasilitas: {{ $item->title }}"
                       class="group relative bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-xl hover:border-[#f0acf7]/50 hover:-translate-y-1 transition-[box-shadow,border-color,transform] duration-300 flex flex-col h-full overflow-hidden motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style] [transform:translateZ(0)]">

                        <span class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-x-0" aria-hidden="true"></span>

                        <div class="flex items-start gap-4 mb-5">
                            <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-[#f0acf7] flex items-center justify-center text-white shadow-md shadow-slate-950/15 group-hover:scale-110 group-hover:rotate-3 transition-[transform] duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-100 motion-reduce:group-hover:rotate-0 [transform:translateZ(0)]">
                                @if($item->icon)
                                    <div class="w-7 h-7 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                        {!! safe_svg($item->icon) !!}
                                    </div>
                                @else
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 pt-1">
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mb-1.5 group-hover:text-slate-950 transition-colors duration-300 line-clamp-2">
                                    {{ $item->title }}
                                </h3>
                                @if($item->badge)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#acf7f0]/30 text-slate-900 text-xs font-semibold border border-[#acf7f0]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#acf7f0]" aria-hidden="true"></span>
                                        {{ $item->badge }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="border-t border-slate-100 mb-4"></div>

                        @if($item->stat_value !== null && $item->stat_value !== '')
                            <div class="flex items-baseline justify-between mb-3">
                                <span class="text-sm text-slate-500 font-medium">{{ $item->stat_label ?: 'Jumlah:' }}</span>
                                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none">
                                    {{ $item->stat_value }}
                                </span>
                            </div>
                        @endif

                        @if($item->capacity_value !== null && $item->capacity_value !== '')
                            <div class="flex items-baseline justify-between mb-3">
                                <span class="text-sm text-slate-500 font-medium">{{ $item->capacity_label ?: 'Daya Tampung:' }}</span>
                                <span class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-none">{{ $item->capacity_value }}</span>
                            </div>
                        @endif

                        @if($item->description)
                            <p class="text-sm text-slate-600 leading-relaxed mt-auto line-clamp-3">
                                {{ strip_tags($item->description) }}
                            </p>
                        @endif
                    </a>
                @endforeach
            </div>

            @if($showBtn)
                <div class="flex justify-center">
                    <a href="{{ url('/fasilitas') }}"
                       class="inline-flex items-center gap-2 px-6 sm:px-8 py-3.5 sm:py-4 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold shadow-lg shadow-slate-950/15 transition-[transform,background-color,box-shadow] duration-300 transform hover:-translate-y-1 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                        {{ $btnText }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            @endif
        @else
            <div class="w-full text-center py-8 sm:py-10 text-slate-500 bg-white rounded-3xl border border-slate-200 shadow-sm">
                <p class="text-slate-500 text-sm font-medium">Belum ada data fasilitas yang ditambahkan melalui panel admin.</p>
            </div>
        @endif

    </div>
</section>
