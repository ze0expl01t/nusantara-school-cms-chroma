@php
    $safeInput = function ($block, $field, $default = null) {
        $value = $block->input($field);
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? $default;
        }
        return $value ?: $default;
    };

    $titleMain      = $safeInput($block, 'title_main', 'Pendidikan');
    $titleHighlight = $safeInput($block, 'title_highlight', '& Fasilitas');
    $subtitle       = $safeInput($block, 'subtitle', 'Pendidikan Berkualitas dengan Kompetensi Industri');

    $items = $block->children;
@endphp

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>

    <div class="space-y-10 sm:space-y-12">

        {{-- Header Section --}}
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2">
                {{ $titleMain }}
                <span class="text-slate-900">{{ $titleHighlight }}</span>
            </h2>
            @if($subtitle)
                <p class="text-base md:text-lg text-slate-600 px-2">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        {{-- Grid Cards --}}
        @if($items && $items->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 md:gap-8">
                @foreach($items as $item)
                    @php
                        $icon       = $safeInput($item, 'icon');
                        $title      = $safeInput($item, 'title');
                        $desc       = $safeInput($item, 'description');
                        $buttonUrl  = $safeInput($item, 'button_url');
                        $buttonText = $safeInput($item, 'button_text', 'Read More');
                    @endphp

                    <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-6 sm:p-6 md:p-8 shadow-sm hover:shadow-xl hover:border-[#f0acf7]/50 hover:shadow-slate-950/5 transition-[box-shadow,border-color] duration-300 group flex flex-col h-full relative overflow-hidden motion-reduce:transition-none [contain:layout_style] [transform:translateZ(0)]">

                        {{-- Ikon — scale pakai transform, bukan width/height --}}
                        <div class="flex justify-center mb-5 sm:mb-6">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center group-hover:scale-110 group-hover:bg-[#acf7f0] transition-[transform,background-color] duration-500 text-slate-900 shadow-sm motion-reduce:transition-none motion-reduce:group-hover:scale-100 [transform:translateZ(0)]">
                                @if($icon)
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                        {!! safe_svg($icon) !!}
                                    </div>
                                @else
                                    {{-- Fallback Default Icon --}}
                                    <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>

                        {{-- Konten Teks --}}
                        <div class="text-center flex-grow flex flex-col">
                            <h3 class="text-base sm:text-lg md:text-xl font-bold text-slate-900 mb-3 group-hover:text-slate-950 transition-colors duration-300 leading-snug line-clamp-2">
                                {{ $title }}
                            </h3>

                            @if($desc)
                                <p class="text-sm text-slate-600 leading-relaxed mb-6 line-clamp-4">
                                    {{ $desc }}
                                </p>
                            @endif
                        </div>

                        {{-- Tombol Read More --}}
                        @if($buttonUrl)
                            <div class="mt-auto flex justify-center">
                                <a
                                    href="{{ $buttonUrl }}"
                                    data-analytics="feature-click"
                                    data-feature-title="{{ $title }}"
                                    aria-label="Selengkapnya tentang {{ $title }}"
                                    class="inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 text-xs sm:text-sm font-bold rounded-full transition-colors duration-300 shadow-md shadow-slate-950/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none"
                                >
                                    {{ $buttonText }}
                                    <svg class="w-4 h-4 transform group-hover:translate-x-1.5 transition-transform duration-300 motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="w-full text-center py-8 sm:py-10 text-slate-500 bg-white rounded-3xl border border-slate-200 shadow-sm">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <p class="text-slate-500 text-sm font-medium">
                    Belum ada data yang ditambahkan melalui panel admin.
                </p>
            </div>
        @endif

    </div>
</section>
