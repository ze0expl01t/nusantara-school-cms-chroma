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

    $badge       = $safeInput($block, 'badge');
    $title       = $safeInput($block, 'title');
    $description = $safeInput($block, 'description');

    $stat1Value = $safeInput($block, 'stat_1_value', '100%');
    $stat1Label = $safeInput($block, 'stat_1_label', 'Enterprise Security Standard');
    $stat2Value = $safeInput($block, 'stat_2_value', '24/7');
    $stat2Label = $safeInput($block, 'stat_2_label', 'Professional Engineer Support');

    // Ambil gambar — pakai twill_media_path untuk LCP preload
    $hasHeroImage = $block->hasImage('hero_image');
    $heroMedia    = $hasHeroImage ? twill_media($block, 'hero_image') : null;
    $heroMediaPath = $heroMedia ? twill_media_path($heroMedia) : null;

    $fallbackImage = 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80';

    // Alt text: pakai title kalau ada, fallback generic
    $imageAlt = $title ?: 'Ilustrasi corporate transformation';
@endphp

{{-- ═══ LCP Preload: hero image adalah LCP element di desktop ═══ --}}
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

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden" data-block-reveal>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-10 lg:gap-12 items-center">

        {{-- ═══ Kolom Kiri: Teks, Deskripsi, dan Statistik ═══ --}}
        <div class="lg:col-span-6 space-y-5 sm:space-y-6">

            {{-- Badge --}}
            @if($badge)
                <div class="inline-block px-3.5 py-1.5 rounded-full bg-[#acf7f0]/30 border border-[#f0acf7] text-slate-900 text-[11px] sm:text-xs font-bold tracking-widest uppercase shadow-sm">
                    {{ $badge }}
                </div>
            @endif

            {{-- Title (kemungkinan LCP element di mobile — pastikan tidak blocking) --}}
            @if($title)
                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight [text-wrap:balance]">
                    {{ $title }}
                </h2>
            @endif

            {{-- Description --}}
            @if($description)
                <div class="text-sm sm:text-base text-slate-600 leading-relaxed prose prose-sm max-w-none prose-p:my-2">
                    {!! safe_html($description) !!}
                </div>
            @endif

            {{-- Statistik 2 Kolom --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 pt-6 border-t border-slate-200">

                {{-- Stat 1 --}}
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="w-1 h-12 bg-[#f0acf7] rounded-full flex-shrink-0" aria-hidden="true"></div>
                    <div class="min-w-0">
                        <div class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-none">
                            {{ $stat1Value }}
                        </div>
                        <div class="text-xs sm:text-sm text-slate-500 mt-2 leading-snug">
                            {{ $stat1Label }}
                        </div>
                    </div>
                </div>

                {{-- Stat 2 --}}
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="w-1 h-12 bg-[#f0acf7] rounded-full flex-shrink-0" aria-hidden="true"></div>
                    <div class="min-w-0">
                        <div class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-none">
                            {{ $stat2Value }}
                        </div>
                        <div class="text-xs sm:text-sm text-slate-500 mt-2 leading-snug">
                            {{ $stat2Label }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ═══ Kolom Kanan: Gambar ═══ --}}
        <div class="lg:col-span-6 relative">
            <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-xl bg-white group [contain:layout_style] [transform:translateZ(0)]">

                {!! twill_proxy_img(
                        $block,
                        'hero_image',
                        [640, 1024, 1536],
                        '(max-width: 1024px) 100vw, 50vw',
                        $imageAlt,
                        [],
                        $fallbackImage,
                        [
                            'class' => 'w-full h-[280px] sm:h-[380px] lg:h-[420px] object-cover transition-transform duration-700 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                            'width' => '1536',
                            'height' => '1024',
                            'loading' => 'eager',              // ⬅️ HERO = above-fold = EAGER
                            'fetchpriority' => 'high',          // ⬅️ Prioritas tinggi untuk LCP
                            'decoding' => 'sync',               // ⬅️ Sync karena LCP, tidak boleh delay,
                                  'onerror' => "this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}",
                        ]
                    ) !!}

                {{-- Gradient overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/20 via-transparent to-transparent pointer-events-none" aria-hidden="true"></div>
            </div>

            {{-- Decorative glow — blur dikurangi untuk hemat GPU paint --}}
            <div class="absolute bottom-0 right-0 translate-x-1/4 translate-y-1/4 w-48 h-48 sm:w-64 sm:h-64 bg-[#acf7f0]/10 blur-[60px] rounded-full pointer-events-none [transform:translate3d(25%,25%,0)]" aria-hidden="true"></div>
        </div>

    </div>
</section>
