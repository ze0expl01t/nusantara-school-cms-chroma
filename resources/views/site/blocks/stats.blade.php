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

    $titleMain      = $safeInput($block, 'title_main', 'Pengalaman');
    $titleHighlight = $safeInput($block, 'title_highlight', '& Mutu Pendidikan');

    $stats = $block->children;
@endphp

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto [content-visibility:auto] [contain-intrinsic-size:400px]" data-block-reveal>
    <div class="space-y-6 sm:space-y-8">

        <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-12 shadow-sm relative overflow-hidden [contain:layout_style]">

            {{-- Cahaya Latar — blur dikurangi --}}
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-3/4 h-20 sm:h-24 bg-[#acf7f0]/5 blur-[50px] rounded-full pointer-events-none [transform:translate3d(-50%,0,0)]" aria-hidden="true"></div>

            <div class="relative z-10">

                @if($titleMain || $titleHighlight)
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-center mb-8 sm:mb-10 tracking-tight text-slate-900 px-2 [text-wrap:balance]">
                        {{ $titleMain }}
                        @if($titleHighlight)
                            <span class="text-slate-900">{{ $titleHighlight }}</span>
                        @endif
                    </h2>
                @endif

                @if($stats && $stats->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-y-6 sm:gap-y-8 gap-x-0 md:divide-x md:divide-slate-200">
                        @foreach($stats as $stat)
                            @php
                                $number = $safeInput($stat, 'number');
                                $suffix = $safeInput($stat, 'suffix');
                                $label  = $safeInput($stat, 'label');
                            @endphp

                            <div class="flex flex-col items-center justify-center text-center group cursor-default px-2 sm:px-4">
                                <div class="flex items-baseline gap-1 transform transition-transform duration-300 group-hover:-translate-y-1.5 motion-reduce:transition-none motion-reduce:group-hover:translate-y-0 [transform:translateZ(0)]">

                                    @if($number)
                                        <span class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 drop-shadow-sm tabular-nums leading-none">
                                            {{ $number }}
                                        </span>
                                    @endif

                                    @if($suffix)
                                        <span class="text-lg sm:text-xl md:text-2xl font-bold text-slate-500 leading-none">
                                            {{ $suffix }}
                                        </span>
                                    @endif

                                </div>

                                @if($label)
                                    <p class="mt-2.5 sm:mt-3 text-[10px] sm:text-xs md:text-sm font-bold tracking-[0.15em] sm:tracking-[0.2em] text-slate-500 uppercase group-hover:text-slate-800 transition-colors duration-300 leading-snug motion-reduce:transition-none">
                                        {{ $label }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="w-full text-center py-8 sm:py-10 text-slate-400 bg-slate-50 rounded-2xl sm:rounded-3xl border border-dashed border-slate-200">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="text-sm font-medium">Belum ada data statistik yang ditambahkan melalui panel admin.</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</section>
