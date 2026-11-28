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

    $badgeText   = $safeInput($block, 'badge_text');
    $titleMain   = $safeInput($block, 'title_main', 'Profil Lulusan Unggul');
    $subHeading  = $safeInput($block, 'sub_heading');
    $description = $safeInput($block, 'description');

    $ctaPrimaryText   = $safeInput($block, 'cta_primary_text', 'Kontak Kami');
    $ctaPrimaryUrl    = $safeInput($block, 'cta_primary_url');
    $ctaSecondaryText = $safeInput($block, 'cta_secondary_text', 'Kegiatan');
    $ctaSecondaryUrl  = $safeInput($block, 'cta_secondary_url');

    $stats = $block->children;

    // Ambil gambar hero
    $hasHeroImage = $block->hasImage('hero_5_image', 'desktop') || $block->hasImage('hero_5_image');

    $heroImageAlt = $titleMain ?: 'Ilustrasi profil';
    $fallbackHeroImage = 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1536&q=80';
    $fallbackHeroImageAlt = 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1536&q=80';

    // ═══ LCP media path untuk preload ═══
    $heroMedia = $hasHeroImage ? twill_media($block, 'hero_5_image') : null;
    $heroMediaPath = $heroMedia ? twill_media_path($heroMedia) : null;
@endphp

{{-- ═══ LCP Preload hero image ═══ --}}
@if($heroMediaPath)
    @push('head')
        <link rel="preload"
              as="image"
              href="{{ url('media/' . $heroMediaPath . '?format=webp&w=1024') }}"
              imagesrcset="{{ url('media/' . $heroMediaPath . '?format=webp&w=640') }} 640w,
                           {{ url('media/' . $heroMediaPath . '?format=webp&w=1024') }} 1024w,
                           {{ url('media/' . $heroMediaPath . '?format=webp&w=1536') }} 1536w"
              imagesizes="(max-width: 1024px) 100vw, 50vw"
              fetchpriority="high">
    @endpush
@endif

<section class="relative py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto overflow-hidden [content-visibility:auto] [contain-intrinsic-size:900px]" data-block-reveal>

    {{-- Efek Ambient Glow — blur dikurangi untuk hemat GPU paint --}}
    <div class="absolute top-1/2 left-0 -translate-y-1/2 w-64 h-64 sm:w-96 sm:h-96 bg-[#acf7f0]/10 blur-[60px] rounded-full pointer-events-none [transform:translate3d(0,-50%,0)]" aria-hidden="true"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 sm:w-96 sm:h-96 bg-[#f0acf7]/10 blur-[60px] rounded-full pointer-events-none" aria-hidden="true"></div>

    <div class="relative z-10 space-y-10 sm:space-y-12">

        {{-- Konten Utama: Grid Kiri (Teks) & Kanan (Gambar) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-10 lg:gap-12 items-center">

            {{-- Kolom Teks (Kiri - 7 Kolom) --}}
            <div class="lg:col-span-7 space-y-4 sm:space-y-5">

                {{-- Badge — animasi DIPERTAHANKAN --}}
                @if($badgeText)
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs sm:text-sm font-medium text-slate-700">
                        <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none" aria-hidden="true"></span>
                        {{ $badgeText }}
                    </div>
                @endif

                {{-- Heading Utama --}}
                @if($titleMain)
                    <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight [text-wrap:balance]">
                        {{ $titleMain }}
                    </h2>
                    <div class="w-20 h-1 rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>
                @endif

                {{-- Sub Heading --}}
                @if($subHeading)
                    <h3 class="text-base sm:text-lg md:text-xl font-bold text-slate-900 tracking-wide">
                        {{ $subHeading }}
                    </h3>
                @endif

                {{-- Deskripsi --}}
                @if($description)
                    <div class="text-slate-600 text-sm sm:text-base leading-relaxed prose prose-sm sm:prose-base max-w-none prose-p:my-0 prose-p:leading-relaxed">
                        {!! nl2br(e($description)) !!}
                    </div>
                @endif

                {{-- Tombol CTA --}}
                @if(($ctaPrimaryText && $ctaPrimaryUrl) || ($ctaSecondaryText && $ctaSecondaryUrl))
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 pt-2">

                        {{-- Primary CTA --}}
                        @if($ctaPrimaryText && $ctaPrimaryUrl)
                            <a
                                href="{{ $ctaPrimaryUrl }}"
                                data-analytics="about-cta-primary"
                                class="inline-flex items-center justify-center px-5 sm:px-6 py-2.5 sm:py-3 bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold rounded-full transition-[transform,background-color,box-shadow] duration-300 shadow-md shadow-slate-950/15 hover:-translate-y-0.5 text-sm sm:text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [transform:translateZ(0)]"
                            >
                                {{ $ctaPrimaryText }}
                            </a>
                        @endif

                        {{-- Secondary CTA --}}
                        @if($ctaSecondaryText && $ctaSecondaryUrl)
                            <a
                                href="{{ $ctaSecondaryUrl }}"
                                data-analytics="about-cta-secondary"
                                class="inline-flex items-center justify-center px-5 sm:px-6 py-2.5 sm:py-3 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200 font-bold rounded-full transition-[transform,background-color,border-color] duration-300 shadow-sm hover:-translate-y-0.5 text-sm sm:text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [transform:translateZ(0)]"
                            >
                                {{ $ctaSecondaryText }}
                            </a>
                        @endif

                    </div>
                @endif

            </div>

            {{-- Kolom Foto (Kanan - 5 Kolom) --}}
            <div class="lg:col-span-5">
                <div class="relative rounded-2xl sm:rounded-2xl overflow-hidden aspect-[4/3] bg-slate-100 border border-slate-200 shadow-xl shadow-slate-950/10 hover:shadow-slate-950/20 transition-[box-shadow] duration-500 group motion-reduce:transition-none [contain:layout_style] [transform:translateZ(0)]">

                    @php
                            $heroImgHtml = $hasHeroImage
                                ? twill_proxy_img(
                                    $block,
                                    'hero_5_image',
                                    [640, 1024, 1536],
                                    '(max-width: 1024px) 100vw, 50vw',
                                    $heroImageAlt,
                                    [],
                                    $fallbackHeroImage,
                                    [
                                        'class' => 'w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                        'width' => '1536',
                                        'height' => '1152',
                                        'loading' => 'eager',
                                        'fetchpriority' => 'high',
                                        'decoding' => 'async',
                                        'onerror' => "this.onerror=null;this.removeAttribute('srcset');this.src='" . $fallbackHeroImage . "';",
                                    ]
                                )
                                : '';
                        @endphp
                        @if(filled(trim($heroImgHtml)))
                            {!! $heroImgHtml !!}
                        @else
                            <img
                                src="{{ $fallbackHeroImage }}"
                                alt="{{ $heroImageAlt }}"
                                class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                                width="1536"
                                height="1152"
                                loading="eager"
                                fetchpriority="high"
                                decoding="async"
                                onerror="this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1536&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1536&q=80';}else{this.onerror=null;this.style.display='none';}"
                            >
                        @endif

                    {{-- Overlay gradient saat hover --}}
                    <div class="absolute inset-0 bg-gradient-to-tr from-slate-950/25 via-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none motion-reduce:transition-none" aria-hidden="true"></div>
                </div>
            </div>

        </div>

        {{-- Statistik Bawah (Repeater) --}}
        @if($stats && $stats->count() > 0)
            <div class="pt-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    @foreach($stats as $stat)
                        @php
                            $iconSvg = $safeInput($stat, 'icon');
                            $value   = $safeInput($stat, 'value');
                            $label   = $safeInput($stat, 'label');
                        @endphp

                        <div class="bg-white border border-slate-200 hover:border-[#f0acf7]/60 hover:bg-slate-50/80 rounded-2xl p-4 sm:p-5 transition-[transform,box-shadow,border-color,background-color] duration-300 shadow-sm hover:shadow-xl hover:shadow-slate-950/5 hover:-translate-y-1 group flex items-center gap-3 sm:gap-4 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style] [transform:translateZ(0)]">

                            {{-- Ikon --}}
                            @if($iconSvg)
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-slate-50 border border-slate-200 group-hover:border-[#f0acf7] group-hover:bg-[#f7f0ac]/40 flex items-center justify-center text-slate-600 group-hover:text-slate-950 shrink-0 transition-[background-color,border-color,color] duration-300 shadow-sm motion-reduce:transition-none [&>svg]:w-5 [&>svg]:h-5 sm:[&>svg]:w-6 sm:[&>svg]:h-6">
                                    {!! safe_svg($iconSvg) !!}
                                </div>
                            @endif

                            {{-- Nilai & Label --}}
                            <div class="min-w-0 flex-1">
                                @if($value)
                                    <div class="text-lg sm:text-xl md:text-2xl font-black text-slate-900 group-hover:text-slate-900 mb-0.5 transition-colors duration-300 leading-tight motion-reduce:transition-none">
                                        {{ $value }}
                                    </div>
                                @endif
                                @if($label)
                                    <div class="text-[10px] sm:text-[11px] md:text-xs font-semibold tracking-wider text-slate-500 uppercase group-hover:text-slate-800 transition-colors duration-300 leading-snug motion-reduce:transition-none">
                                        {{ $label }}
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>
