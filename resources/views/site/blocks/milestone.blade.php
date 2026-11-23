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

    $badgeText      = $safeInput($block, 'badge_text', 'Perjalanan Kami');
    $titleMain      = $safeInput($block, 'title_main', 'Milestone');
    $titleHighlight = $safeInput($block, 'title_highlight', '& Sejarah');
    $subtitle       = $safeInput($block, 'subtitle');
    $layout         = $block->input('layout') ?: 'vertical';
    $closingText    = $safeInput($block, 'closing_text');

    // ✅ Akses repeater sebagai child blocks (Twill 3.6)
    $items = $block->children()
        ->where('type', 'milestone_item')
        ->orderBy('position')
        ->get();
@endphp

@if($items->count() > 0)
<section class="py-10 sm:py-14 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:1200px]" data-block-reveal>

    {{-- Ambient glow — blur dikurangi untuk hemat GPU paint --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[80px] rounded-full pointer-events-none [transform:translate3d(-50%,-50%,0)]" aria-hidden="true"></div>

    <div class="relative z-10 space-y-10 sm:space-y-14">

        {{-- Header Section --}}
        <div class="flex flex-col items-center text-center space-y-3">
            @if($badgeText)
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-medium text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none" aria-hidden="true"></span>
                    {{ $badgeText }}
                </div>
            @endif

            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2 [text-wrap:balance]">
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

        {{-- TIMELINE VERTIKAL (default) --}}
        @if($layout === 'vertical')
            <div class="relative">
                <div class="absolute left-4 sm:left-6 top-2 bottom-2 w-0.5 bg-gradient-to-b from-[#f7f0ac] via-[#acf7f0] to-transparent" aria-hidden="true"></div>

                <div class="space-y-8 sm:space-y-10">
                    @foreach($items as $index => $item)
                        @php
                            $year = $item->input('year') ?: '';
                            $title = $item->translatedInput('title') ?: $item->input('title');
                            $description = $item->translatedInput('description') ?: $item->input('description');
                            $hasImage = $item->hasImage('image');
                            $isHighlight = (bool) $item->input('is_highlight');
                            $isFirst = $index === 0;
                        @endphp

                        <div class="relative pl-14 sm:pl-20">
                            {{-- Dot / Marker --}}
                            <div class="absolute left-0 top-1 w-9 h-9 sm:w-12 sm:h-12 rounded-full flex items-center justify-center
                                        {{ $isHighlight
                                            ? 'bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/30'
                                            : 'bg-gradient-to-br from-[#f7f0ac] to-[#f0acf7] shadow-lg shadow-slate-950/20' }}
                                        border-4 border-white z-10 [transform:translateZ(0)]">
                                <span class="text-white text-[10px] sm:text-xs font-extrabold">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>

                            {{-- Content Card --}}
                            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:border-[#f0acf7]/30 transition-[box-shadow,border-color] duration-300 motion-reduce:transition-none [contain:layout_style]">
                                <div class="flex flex-col sm:flex-row gap-0 sm:gap-5">

                                    @if($hasImage)
                                        <div class="sm:w-48 sm:flex-shrink-0 aspect-video sm:aspect-auto sm:min-h-[160px] overflow-hidden bg-slate-100">
                                            {!! twill_proxy_img(
                                                $item,
                                                'image',
                                                [400, 800],
                                                '(max-width: 640px) 100vw, 200px',
                                                $title,
                                                [],
                                                null,
                                                [
                                                    'class' => 'w-full h-full object-cover',
                                                    'width' => '800',
                                                    'height' => '600',
                                                    'loading' => $isFirst ? 'eager' : 'lazy',
                                                    'fetchpriority' => $isFirst ? 'high' : 'auto',
                                                    'decoding' => 'async',
                                                ]
                                            ) !!}
                                        </div>
                                    @endif

                                    <div class="p-5 sm:py-5 sm:pr-5 flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            @if($year)
                                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider
                                                             {{ $isHighlight
                                                                 ? 'bg-amber-100 text-amber-700 border border-amber-200'
                                                                 : 'bg-[#acf7f0] text-slate-900 border border-[#f0acf7]' }}">
                                                    {{ $year }}
                                                </span>
                                            @endif

                                            @if($isHighlight)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold uppercase tracking-wider border border-amber-200">
                                                    ⭐ Highlight
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 leading-snug mb-2">
                                            {{ $title }}
                                        </h3>

                                        @if($description)
                                            <p class="text-sm text-slate-600 leading-relaxed">
                                                {{ strip_tags($description) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- TIMELINE ZIG-ZAG (kiri-kanan) --}}
        @if($layout === 'vertical_alternate')
            <div class="relative">
                <div class="absolute left-1/2 -translate-x-1/2 top-2 bottom-2 w-0.5 bg-gradient-to-b from-[#f7f0ac] via-[#acf7f0] to-transparent hidden sm:block" aria-hidden="true"></div>

                <div class="space-y-6 sm:space-y-12">
                    @foreach($items as $index => $item)
                        @php
                            $year = $item->input('year') ?: '';
                            $title = $item->translatedInput('title') ?: $item->input('title');
                            $description = $item->translatedInput('description') ?: $item->input('description');
                            $hasImage = $item->hasImage('image');
                            $isHighlight = (bool) $item->input('is_highlight');
                            $isLeft = $index % 2 === 0;
                            $isFirst = $index === 0;
                        @endphp

                        {{-- Mobile: tampil stacked --}}
                        <div class="sm:hidden bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm [contain:layout_style]">
                            @if($hasImage)
                                <div class="aspect-video overflow-hidden bg-slate-100">
                                    {!! twill_proxy_img(
                                        $item,
                                        'image',
                                        [400, 800],
                                        '100vw',
                                        $title,
                                        [],
                                        null,
                                        [
                                            'class' => 'w-full h-full object-cover',
                                            'width' => '800',
                                            'height' => '600',
                                            'loading' => $isFirst ? 'eager' : 'lazy',
                                            'decoding' => 'async',
                                        ]
                                    ) !!}
                                </div>
                            @endif
                            <div class="p-5">
                                @if($year)
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider mb-2
                                                 {{ $isHighlight ? 'bg-amber-100 text-amber-700' : 'bg-[#acf7f0] text-slate-900' }}">
                                        {{ $year }}
                                    </span>
                                @endif
                                <h3 class="text-base font-extrabold text-slate-900 mb-2">{{ $title }}</h3>
                                @if($description)
                                    <p class="text-sm text-slate-600">{{ strip_tags($description) }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Desktop: zig-zag --}}
                        <div class="hidden sm:grid grid-cols-11 gap-4 items-center relative">
                            @if($isLeft)
                                {{-- Konten kiri --}}
                                <div class="col-span-5">
                                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:border-[#f0acf7]/30 transition-[box-shadow,border-color] duration-300 motion-reduce:transition-none [contain:layout_style]">
                                        @if($hasImage)
                                            <div class="aspect-video overflow-hidden bg-slate-100">
                                                {!! twill_proxy_img(
                                                    $item,
                                                    'image',
                                                    [400, 800],
                                                    '(max-width: 1024px) 50vw, 400px',
                                                    $title,
                                                    [],
                                                    null,
                                                    [
                                                        'class' => 'w-full h-full object-cover',
                                                        'width' => '800',
                                                        'height' => '600',
                                                        'loading' => $isFirst ? 'eager' : 'lazy',
                                                        'fetchpriority' => $isFirst ? 'high' : 'auto',
                                                        'decoding' => 'async',
                                                    ]
                                                ) !!}
                                            </div>
                                        @endif
                                        <div class="p-5">
                                            @if($year)
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider mb-2
                                                             {{ $isHighlight ? 'bg-amber-100 text-amber-700' : 'bg-[#acf7f0] text-slate-900' }}">
                                                    {{ $year }}
                                                </span>
                                            @endif
                                            <h3 class="text-base font-extrabold text-slate-900 mb-2">{{ $title }}</h3>
                                            @if($description)
                                                <p class="text-sm text-slate-600 leading-relaxed">{{ strip_tags($description) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Marker tengah --}}
                                <div class="col-span-1 flex justify-center">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center
                                                {{ $isHighlight
                                                    ? 'bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/30'
                                                    : 'bg-gradient-to-br from-[#f7f0ac] to-[#f0acf7] shadow-lg shadow-slate-950/20' }}
                                                border-4 border-white z-10 [transform:translateZ(0)]">
                                        <span class="text-white text-xs font-extrabold">
                                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-span-5"></div>
                            @else
                                <div class="col-span-5"></div>

                                {{-- Marker tengah --}}
                                <div class="col-span-1 flex justify-center">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center
                                                {{ $isHighlight
                                                    ? 'bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/30'
                                                    : 'bg-gradient-to-br from-[#f7f0ac] to-[#f0acf7] shadow-lg shadow-slate-950/20' }}
                                                border-4 border-white z-10 [transform:translateZ(0)]">
                                        <span class="text-white text-xs font-extrabold">
                                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Konten kanan --}}
                                <div class="col-span-5">
                                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:border-[#f0acf7]/30 transition-[box-shadow,border-color] duration-300 motion-reduce:transition-none [contain:layout_style]">
                                        @if($hasImage)
                                            <div class="aspect-video overflow-hidden bg-slate-100">
                                                {!! twill_proxy_img(
                                                    $item,
                                                    'image',
                                                    [400, 800],
                                                    '(max-width: 1024px) 50vw, 400px',
                                                    $title,
                                                    [],
                                                    null,
                                                    [
                                                        'class' => 'w-full h-full object-cover',
                                                        'width' => '800',
                                                        'height' => '600',
                                                        'loading' => $isFirst ? 'eager' : 'lazy',
                                                        'decoding' => 'async',
                                                    ]
                                                ) !!}
                                            </div>
                                        @endif
                                        <div class="p-5">
                                            @if($year)
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider mb-2
                                                             {{ $isHighlight ? 'bg-amber-100 text-amber-700' : 'bg-[#acf7f0] text-slate-900' }}">
                                                    {{ $year }}
                                                </span>
                                            @endif
                                            <h3 class="text-base font-extrabold text-slate-900 mb-2">{{ $title }}</h3>
                                            @if($description)
                                                <p class="text-sm text-slate-600 leading-relaxed">{{ strip_tags($description) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Closing Text --}}
        @if($closingText)
            <div class="text-center pt-4">
                <p class="text-base sm:text-lg font-semibold text-slate-900 italic">
                    {{ $closingText }}
                </p>
            </div>
        @endif

    </div>
</section>
@endif
