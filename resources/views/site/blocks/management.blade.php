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

    $badgeText      = $safeInput($block, 'badge_text', 'Struktur & Kepemimpinan');
    $titleMain      = $safeInput($block, 'title_main');
    $titleHighlight = $safeInput($block, 'title_highlight');
    $subtitle       = $safeInput($block, 'subtitle');

    $items = $block->children()
        ->orderBy('position')
        ->get();

    $fallbackGrads = [
        'from-[#acf7f0]/80 via-[#f7f0ac]/70 to-[#f0acf7]/80',
        'from-[#f0acf7]/80 via-[#acf7f0]/70 to-[#f7f0ac]/80',
        'from-[#f7f0ac]/85 via-[#f0acf7]/65 to-[#acf7f0]/80',
    ];
@endphp

<section class="py-10 sm:py-14 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto [content-visibility:auto] [contain-intrinsic-size:700px]" data-block-reveal>
    <div class="space-y-8 sm:space-y-12">

        @if(filled($titleMain) || filled($titleHighlight) || filled($subtitle))
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
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 md:gap-7">
                @foreach($items as $item)
                    @php
                        $hasPhoto = $item->hasImage('photo', 'desktop')
                                 || $item->hasImage('photo', 'default')
                                 || $item->hasImage('photo');

                        $name     = $safeInput($item, 'name');
                        $position = $safeInput($item, 'position');

                        $facebook  = $safeInput($item, 'facebook_url');
                        $instagram = $safeInput($item, 'instagram_url');
                        $linkedin  = $safeInput($item, 'linkedin_url');

                        $hasSocials = filled($facebook) || filled($instagram) || filled($linkedin);
                        $initial    = filled($name) ? mb_strtoupper(mb_substr(trim($name), 0, 1)) : '';
                        $gradClass  = $fallbackGrads[$loop->index % count($fallbackGrads)];
                        $itemKey    = $item->id ?? ($loop->index + 1);
                        $altText    = trim(($name ?? '') . (filled($position) ? ' - ' . $position : ''));
                        $publicFallbackUrl = 'https://picsum.photos/seed/smkmuh1-mgmt-' . $itemKey . '/600/800.webp';
                    @endphp

                    <article class="group relative w-full aspect-[3/4] rounded-3xl overflow-hidden bg-white border border-slate-200/90 hover:border-slate-950/25 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style] [transform:translateZ(0)]">

                        {{-- Layer 0: Pastel gradient safety surface --}}
                        <div class="absolute inset-0 z-0 flex flex-col items-center justify-center bg-gradient-to-br {{ $gradClass }} text-slate-950 select-none px-4 pb-16" aria-hidden="true">
                            @if(filled($initial))
                                <span class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/85 border border-slate-950/15 shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-black text-slate-950">
                                    {{ $initial }}
                                </span>
                            @endif
                        </div>

                        {{-- Layer 1: Public free fallback photo (used when photo is missing or primary image fails) --}}
                        <div class="absolute inset-0 z-10">
                            <img
                                src="{{ $publicFallbackUrl }}"
                                alt="{{ $altText }}"
                                width="600"
                                height="800"
                                loading="lazy"
                                decoding="async"
                                onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}"
                                class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                            >
                        </div>

                        {{-- Layer 2: Primary Twill photo (hides itself on error so fallback photo/surface shows cleanly) --}}
                        @if($hasPhoto)
                            <div class="absolute inset-0 z-10">
                                {!! twill_proxy_img(
                                    $item,
                                    'photo',
                                    [300, 600, 900],
                                    '(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 25vw',
                                    $altText,
                                    [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                    [
                                        'class' => 'w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                        'width' => '600',
                                        'height' => '800',
                                        'loading' => 'lazy',
                                        'decoding' => 'async',
                                        'onerror' => "this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}",
                                    ]
                                ) !!}
                            </div>
                        @endif

                        {{-- Bottom readability gradient --}}
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-3/5 z-20 bg-gradient-to-t from-slate-950/90 via-slate-950/35 to-transparent transition-opacity duration-300" aria-hidden="true"></div>

                        {{-- Subtle 3-color top bar on hover --}}
                        <span class="pointer-events-none absolute top-0 inset-x-0 h-1 z-30 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] opacity-0 group-hover:opacity-100 transition-opacity duration-300" aria-hidden="true"></span>

                        {{-- Social links overlay on hover --}}
                        @if($hasSocials)
                            <div class="absolute inset-0 z-30 bg-slate-950/75 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 motion-reduce:transition-none flex items-center justify-center pb-14">
                                <div class="flex gap-2.5 sm:gap-3.5 translate-y-3 group-hover:translate-y-0 transition-transform duration-300 motion-reduce:transition-none motion-reduce:translate-y-0">
                                    @if(filled($facebook))
                                        <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook {{ $name }}"
                                           class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-gradient-to-r hover:from-[#f7f0ac] hover:via-[#acf7f0] hover:to-[#f0acf7] text-slate-950 flex items-center justify-center shadow-sm transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0]">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(filled($instagram))
                                        <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram {{ $name }}"
                                           class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-gradient-to-r hover:from-[#f7f0ac] hover:via-[#acf7f0] hover:to-[#f0acf7] text-slate-950 flex items-center justify-center shadow-sm transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0]">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(filled($linkedin))
                                        <a href="{{ $linkedin }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn {{ $name }}"
                                           class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-gradient-to-r hover:from-[#f7f0ac] hover:via-[#acf7f0] hover:to-[#f0acf7] text-slate-950 flex items-center justify-center shadow-sm transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0]">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Name & Position --}}
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-40 p-4 sm:p-5 text-center transform group-hover:-translate-y-0.5 transition-transform duration-300 motion-reduce:transition-none motion-reduce:group-hover:translate-y-0">
                            @if(filled($name))
                                <h3 class="text-sm sm:text-base md:text-lg font-extrabold text-white mb-1 drop-shadow-[0_2px_6px_rgba(0,0,0,0.85)] leading-snug line-clamp-2">
                                    {{ $name }}
                                </h3>
                            @endif

                            @if(filled($position))
                                <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/15 backdrop-blur-xs border border-white/25 text-[11px] sm:text-xs font-bold text-[#acf7f0] group-hover:text-[#f7f0ac] transition-colors duration-300 leading-snug line-clamp-1">
                                    {{ $position }}
                                </span>
                            @endif
                        </div>

                    </article>
                @endforeach
            </div>
        @endif

    </div>
</section>
