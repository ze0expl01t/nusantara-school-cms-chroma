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

    $titleMain      = $safeInput($block, 'title_main', 'Identitas');
    $titleHighlight = $safeInput($block, 'title_highlight', 'Perusahaan');
    $description    = $safeInput($block, 'description');

    $imageUrl = $block->hasImage('image', 'desktop')
        ? $block->image('image', 'desktop')
        : ($block->hasImage('image') ? $block->image('image') : null);

    // CTA
    $ctaPrimaryText   = $safeInput($block, 'cta_primary_text', '#Kontak Kami');
    $ctaPrimaryUrl    = $safeInput($block, 'cta_primary_url', '#kontak');
    $ctaSecondaryText = $safeInput($block, 'cta_secondary_text', 'Konsultasi');
    $ctaSecondaryUrl  = $safeInput($block, 'cta_secondary_url', '#');

    // Children (repeater)
    $children = $block->children ?? collect();
    $children = $children instanceof \Illuminate\Support\Collection ? $children : collect($children);

    $infoItems = $children->filter(fn ($c) => ($c->type ?? null) === 'informasi_perusahaan_item');
    if ($infoItems->isEmpty() && $children->isNotEmpty()) {
        $infoItems = $children->filter(fn ($c) => $c->input('label') !== null && $c->input('value') !== null);
    }

    $statItems = $children->filter(fn ($c) => ($c->type ?? null) === 'informasi_perusahaan_stat');
@endphp

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden" data-block-reveal>

    {{-- Ambient Glow --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[150px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-8 sm:space-y-10">

        {{-- ============ MAIN GRID ============ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-10 lg:gap-12 items-start">

            {{-- Kolom Kiri: Gambar --}}
            <div class="lg:col-span-5 lg:sticky lg:top-24">
                @if($imageUrl)
                    <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100 aspect-[4/3]">
                        {!! twill_proxy_img($block, 'image', [400, 800, 1200], '(max-width: 1024px) 100vw, 40vw', trim(strip_tags($titleMain . ' ' . $titleHighlight)), [], null, ['class' => 'w-full h-full object-cover']) !!}
                    </div>
                @else
                    <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-200 shadow-sm bg-gradient-to-br from-[#f7f0ac] to-[#f0acf7] aspect-[4/3] flex items-center justify-center">
                        <svg class="w-20 h-20 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Kolom Kanan: Judul + Deskripsi + Tabel + CTA --}}
            <div class="lg:col-span-7 space-y-4 sm:space-y-5">

                {{-- Title --}}
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-[1.1]">
                    {{ $titleMain }}
                    @if($titleHighlight)
                        <span class="text-slate-900">{{ $titleHighlight }}</span>
                    @endif
                </h2>

                {{-- Description --}}
                @if($description)
                    <div class="text-sm sm:text-base text-slate-600 leading-relaxed prose prose-sm max-w-none prose-p:my-1.5 prose-strong:text-slate-900">
                        {!! safe_html($description) !!}
                    </div>
                @endif

                {{-- Info Items --}}
                @if($infoItems->count() > 0)
                    <dl class="pt-1 space-y-0 divide-y divide-slate-100">
                        @foreach($infoItems as $info)
                            @php
                                $label = $safeInput($info, 'label');
                                $value = $safeInput($info, 'value');
                            @endphp

                            @if($label)
                                <div class="grid grid-cols-1 sm:grid-cols-[180px_1fr] gap-1 sm:gap-4 py-2">
                                    <dt class="text-xs sm:text-sm font-semibold text-slate-500 leading-snug">
                                        {{ $label }}
                                    </dt>
                                    <dd class="text-xs sm:text-sm text-slate-900 font-medium leading-snug">
                                        {{ $value }}
                                    </dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                @endif

                {{-- CTA Buttons --}}
                @if($ctaPrimaryText || $ctaSecondaryText)
                    <div class="flex flex-wrap items-center gap-3 pt-3">

                        @if($ctaPrimaryText && $ctaPrimaryUrl)
                            <a
                                href="{{ $ctaPrimaryUrl }}"
                                @if(str_starts_with($ctaPrimaryUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif
                                data-analytics="perusahaan-cta-primary"
                                class="group inline-flex items-center gap-2 px-6 sm:px-7 py-3 sm:py-3.5 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold text-sm shadow-lg shadow-slate-950/15 transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none motion-reduce:hover:translate-y-0"
                            >
                                <span>{{ $ctaPrimaryText }}</span>
                                <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @endif

                        @if($ctaSecondaryText && $ctaSecondaryUrl)
                            <a
                                href="{{ $ctaSecondaryUrl }}"
                                @if(str_starts_with($ctaSecondaryUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif
                                data-analytics="perusahaan-cta-secondary"
                                class="inline-flex items-center gap-2 px-6 sm:px-7 py-3 sm:py-3.5 rounded-full bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 text-slate-800 font-bold text-sm transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none motion-reduce:hover:translate-y-0"
                            >
                                {{ $ctaSecondaryText }}
                            </a>
                        @endif

                    </div>
                @endif

            </div>

        </div>

        {{-- ============ STATISTIK BAWAH ============ --}}
        @if($statItems->count() > 0)
            <div class="pt-6 sm:pt-8 border-t border-slate-200">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($statItems as $stat)
                        @php
                            $value = $safeInput($stat, 'value');
                            $label = $safeInput($stat, 'label');
                        @endphp

                        @if($value || $label)
                            <div class="text-center sm:text-left">
                                @if($value)
                                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none">
                                        {{ $value }}
                                    </p>
                                @endif
                                @if($label)
                                    <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1.5">
                                        {{ $label }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>