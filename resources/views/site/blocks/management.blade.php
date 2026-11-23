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

    $titleMain      = $safeInput($block, 'title_main', 'Penanggung Jawab &');
    $titleHighlight = $safeInput($block, 'title_highlight', 'Management Perusahaan');
    $subtitle       = $safeInput($block, 'subtitle', 'Berperan dalam pengawasan dan peningkatan mutu pendidikan');

    // Urutkan children by position + eager load medias (hindari N+1)
    $items = $block->children()
        ->orderBy('position')
        ->get();
@endphp

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto [content-visibility:auto] [contain-intrinsic-size:700px]" data-block-reveal>
    <div class="space-y-8 sm:space-y-10">

        {{-- Header Section --}}
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2 [text-wrap:balance]">
                {{ $titleMain }}
                @if($titleHighlight)
                    <span class="text-slate-900">{{ $titleHighlight }}</span>
                @endif
            </h2>
            @if($subtitle)
                <p class="text-sm sm:text-base md:text-lg text-slate-600 px-2">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        {{-- Grid Personel --}}
        @if($items && $items->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 md:gap-8">
                @foreach($items as $item)
                    @php
                        $hasPhoto = $item->hasImage('photo', 'desktop')
                                 || $item->hasImage('photo', 'default')
                                 || $item->hasImage('photo');

                        $name     = $safeInput($item, 'name', 'Nama Personel');
                        $position = $safeInput($item, 'position', 'Jabatan');

                        $facebook  = $safeInput($item, 'facebook_url');
                        $instagram = $safeInput($item, 'instagram_url');
                        $linkedin  = $safeInput($item, 'linkedin_url');

                        $hasSocials = $facebook || $instagram || $linkedin;
                        $initial = mb_substr($name, 0, 1);
                    @endphp

                    {{-- Card Personel --}}
                    <article class="relative w-full aspect-[3/4] rounded-2xl sm:rounded-3xl overflow-hidden group shadow-sm hover:shadow-xl hover:shadow-slate-950/10 transition-[box-shadow,border-color] duration-500 motion-reduce:transition-none border border-slate-200 hover:border-[#f0acf7] bg-slate-100 [contain:layout_style] [transform:translateZ(0)]">

                        {{-- ═══ Layer 1: Foto / Placeholder (z-0) ═══ --}}
                        <div class="absolute inset-0 z-0">
                            @if($hasPhoto)
                                {!! twill_proxy_img(
                                    $item,
                                    'photo',
                                    [300, 600, 900],
                                    '(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 25vw',
                                    $name . ' — ' . $position,
                                    [],
                                    null,
                                    [
                                        'class' => 'w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-110 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                        'width' => '600',
                                        'height' => '800',
                                        'loading' => 'lazy',
                                        'decoding' => 'async',
                                    ]
                                ) !!}
                            @else
                                {{-- Fallback: inisial nama, gradient background ═══ --}}
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#f7f0ac] to-[#f0acf7] text-slate-900 font-bold text-4xl sm:text-5xl select-none" aria-hidden="true">
                                    {{ $initial }}
                                </div>
                            @endif
                        </div>

                        {{-- ═══ Layer 2: Gradient overlay (z-10, selalu tampil) ═══ --}}
                        <div class="absolute inset-0 z-10 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-transparent opacity-100 group-hover:opacity-0 transition-opacity duration-500 motion-reduce:transition-none pointer-events-none" aria-hidden="true"></div>

                        {{-- ═══ Layer 3: Social overlay (z-20, hover saja) ═══ --}}
                        @if($hasSocials)
                            <div class="absolute inset-0 z-20 bg-slate-950/85 opacity-0 group-hover:opacity-100 transition-opacity duration-500 motion-reduce:transition-none flex items-center justify-center">
                                <div class="flex gap-2.5 sm:gap-4 translate-y-4 group-hover:translate-y-0 transition-transform duration-500 motion-reduce:transition-none motion-reduce:translate-y-0">

                                    @if($facebook)
                                        <a
                                            href="{{ $facebook }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="Facebook {{ $name }}"
                                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/20 hover:bg-white text-white hover:text-slate-950 flex items-center justify-center transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                                        >
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if($instagram)
                                        <a
                                            href="{{ $instagram }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="Instagram {{ $name }}"
                                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/20 hover:bg-white text-white hover:text-slate-950 flex items-center justify-center transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                                        >
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if($linkedin)
                                        <a
                                            href="{{ $linkedin }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="LinkedIn {{ $name }}"
                                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/20 hover:bg-white text-white hover:text-slate-950 flex items-center justify-center transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                                        >
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    @endif

                                </div>
                            </div>
                        @endif

                        {{-- ═══ Layer 4: Nama & Jabatan (z-30, di atas semua) ═══ --}}
                        <div class="absolute bottom-0 left-0 w-full p-3 sm:p-4 md:p-6 text-center transform group-hover:-translate-y-1 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:translate-y-0 z-30 pointer-events-none">
                            <h3 class="text-sm sm:text-base md:text-lg font-bold text-white mb-0.5 drop-shadow-[0_2px_4px_rgba(0,0,0,0.9)] leading-snug line-clamp-2">
                                {{ $name }}
                            </h3>
                            @if($position)
                                <p class="text-[11px] sm:text-xs md:text-sm font-semibold text-[#acf7f0] group-hover:text-[#f7f0ac] transition-colors duration-300 drop-shadow-[0_2px_4px_rgba(0,0,0,0.9)] leading-snug line-clamp-1">
                                    {{ $position }}
                                </p>
                            @endif
                        </div>

                    </article>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="w-full text-center py-8 sm:py-10 bg-white rounded-3xl border border-slate-200 shadow-sm">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-slate-500 text-sm font-medium">
                    Belum ada data personel yang ditambahkan melalui panel admin.
                </p>
            </div>
        @endif

    </div>
</section>
