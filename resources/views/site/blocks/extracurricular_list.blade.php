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

    $badgeText      = $safeInput($block, 'badge_text', 'Ekstrakurikuler');
    $titleMain      = $safeInput($block, 'title_main', 'Ekstrakurikuler');
    $titleHighlight = $safeInput($block, 'title_highlight', '& Pengembangan Diri');
    $subtitle       = $safeInput($block, 'subtitle');
    $limit          = (int) $safeInput($block, 'limit', 6);
    $showBtn        = (bool) $safeInput($block, 'show_button', false);
    $btnText        = $safeInput($block, 'button_text', 'Lihat Semua Ekstrakurikuler');

    // Eager load slugs → hindari N+1 query
    $items = \App\Models\Extracurricular::where('published', true)
        ->with(['slugs', 'medias'])
        ->orderBy('position')
        ->limit($limit > 0 ? $limit : 6)
        ->get();
@endphp

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>

    {{-- Efek Ambient Glow — blur dikurangi untuk hemat GPU paint --}}
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
                <p class="text-base md:text-lg text-slate-600 max-w-2xl px-2 pt-2">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        @if($items->count() > 0)

            {{-- Grid Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach($items as $item)
                    @php
                        $cover = $item->hasImage('cover') ? $item->image('cover', 'default') : null;
                        $slug = $item->slug
                            ?? optional($item->slugs->where('locale', app()->getLocale())->first())->slug
                            ?? optional($item->slugs->first())->slug;

                        // Card pertama = LCP element → eager + high priority
                        $isFirst = $loop->first;
                    @endphp

                    <a href="{{ route('extracurricular.show', $slug) }}"
                       aria-label="Lihat detail ekstrakurikuler: {{ $item->title }}"
                       class="group relative bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:border-[#f0acf7]/50 hover:-translate-y-1 transition-[box-shadow,border-color,transform] duration-300 flex flex-col h-full motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style] [transform:translateZ(0)]">

                        {{-- Top accent bar muncul saat hover --}}
                        <span class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 z-10 motion-reduce:transition-none motion-reduce:group-hover:scale-x-0" aria-hidden="true"></span>

                        @if($cover)
                            <div class="aspect-video overflow-hidden bg-slate-100">
                                {!! twill_proxy_img(
                                    $item,
                                    'cover',
                                    [400, 800, 1200],
                                    '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
                                    $item->title,
                                    [],
                                    null,
                                    [
                                        'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                        'width' => '1200',
                                        'height' => '675',
                                        'loading' => $isFirst ? 'eager' : 'lazy',
                                        'fetchpriority' => $isFirst ? 'high' : 'auto',
                                        'decoding' => 'async',
                                    ]
                                ) !!}
                            </div>
                        @else
                            <div class="aspect-video bg-gradient-to-br from-[#f7f0ac] to-[#f0acf7] flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="p-5 sm:p-6 flex flex-col flex-grow">
                            @if($item->category)
                                <span class="inline-flex self-start px-2.5 py-0.5 rounded-full bg-[#acf7f0]/30 text-slate-900 text-[10px] font-bold uppercase tracking-wider border border-[#acf7f0] mb-3">
                                    {{ $item->category }}
                                </span>
                            @endif

                            <h3 class="text-lg font-extrabold text-slate-900 leading-snug mb-2 group-hover:text-slate-950 transition-colors duration-300 line-clamp-2">
                                {{ $item->title }}
                            </h3>

                            @if($item->schedule)
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-4 h-4 text-slate-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                                        {{ $item->schedule }}
                                    </span>
                                </div>
                            @endif

                            @if($item->description)
                                <p class="text-sm text-slate-600 leading-relaxed line-clamp-3 flex-grow">
                                    {{ strip_tags($item->description) }}
                                </p>
                            @endif

                            @if($item->advisor_name)
                                <div class="flex items-center gap-2 pt-4 mt-4 border-t border-slate-100">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-xs text-slate-500 truncate">
                                        <span class="text-slate-400">{{ $item->advisor_role ?: 'Pembina' }}:</span>
                                        <span class="font-semibold text-slate-700">{{ $item->advisor_name }}</span>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            @if($showBtn)
                <div class="flex justify-center">
                    <a href="{{ url('/ekstrakurikuler') }}"
                       class="inline-flex items-center gap-2 px-6 sm:px-8 py-3.5 sm:py-4 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold shadow-lg shadow-slate-950/15 transition-[transform,background-color,box-shadow] duration-300 transform hover:-translate-y-1 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                        {{ $btnText }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            @endif

        @else
            <div class="text-center py-8 sm:py-10 bg-white border border-slate-200 rounded-3xl shadow-sm">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-slate-500 text-sm font-medium">
                    Belum ada ekstrakurikuler yang dipublikasikan.
                </p>
            </div>
        @endif

    </div>
</section>
