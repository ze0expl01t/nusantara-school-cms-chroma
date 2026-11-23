@php
    use App\Models\Post;
    use Illuminate\Support\Str;

    $safeInput = function ($block, $field, $default = null) {
        $value = $block->input($field);
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? $default;
        }
        return filled($value) ? $value : $default;
    };

    $sectionTitle = $safeInput($block, 'title', 'Latest Articles & Posts');
    $limit        = (int) $safeInput($block, 'limit', 6);

    // Eager load slugs & medias → hindari N+1 query
    $posts = Post::where('published', true)
        ->with(['slugs', 'medias'])
        ->orderBy('post_date', 'desc')
        ->take($limit)
        ->get();

    $locale = app()->getLocale();

    // ═══ Prefetch 2 lead post terbaru (native) ═══
    $prefetchUrls = $posts->take(2)->map(function ($post) use ($locale) {
        $slug = $post->slug
            ?? optional($post->slugs->where('locale', $locale)->first())->slug
            ?? optional($post->slugs->first())->slug;
        return $slug ? url('/post/' . $slug) : null;
    })->filter()->values();
@endphp

{{-- ═══ Native prefetch (ganti JS fetch) ═══ --}}
@push('head')
    @foreach($prefetchUrls as $url)
        <link rel="prefetch" href="{{ $url }}">
    @endforeach
@endpush

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>

    {{-- Ambient glow — blur dikurangi --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[80px] rounded-full pointer-events-none [transform:translate3d(-50%,-50%,0)]" aria-hidden="true"></div>

    <div class="relative z-10 space-y-8 sm:space-y-10">

        {{-- Header Section --}}
        @if($sectionTitle)
            <div class="flex flex-col items-center text-center space-y-3 pb-4 sm:pb-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-medium text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none" aria-hidden="true"></span>
                    Artikel & Berita
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2 [text-wrap:balance]">
                    {{ $sectionTitle }}
                </h2>
                <div class="w-20 h-1 bg-gradient-to-r from-transparent via-[#acf7f0] to-transparent rounded-full" aria-hidden="true"></div>
            </div>
        @endif

        @if($posts->count() > 0)
            @php
                $leadPost = $posts->first();
                $subPosts = $posts->slice(1, 5);

                $leadDate = $leadPost->post_date
                    ? \Carbon\Carbon::parse($leadPost->post_date)->locale($locale)->timezone('Asia/Jakarta')
                    : null;

                $leadSlug = $leadPost->slug
                    ?? optional($leadPost->slugs->where('locale', $locale)->first())->slug
                    ?? optional($leadPost->slugs->first())->slug;
                $leadUrl = $leadSlug ? url('/post/' . $leadSlug) : '#';

                $leadHasImage = $leadPost->hasImage('cover');
                $leadExcerpt = $leadPost->description
                    ? Str::limit(trim(strip_tags($leadPost->description)), 180, '…')
                    : null;

                $leadImageAlt = trim(strip_tags($leadPost->title)) ?: 'Artikel';
            @endphp

            {{-- Layout: 5 Side List (Kiri) + 1 Lead Story (Kanan) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-stretch">

                {{-- 5 Berita Pendukung (Kiri) --}}
                <div class="lg:col-span-5 flex flex-col justify-between gap-3 order-2 lg:order-1">
                    @if($subPosts->count() > 0)
                        @foreach($subPosts as $subPost)
                            @php
                                $subDate = $subPost->post_date
                                    ? \Carbon\Carbon::parse($subPost->post_date)->locale($locale)->timezone('Asia/Jakarta')
                                    : null;

                                $subSlug = $subPost->slug
                                    ?? optional($subPost->slugs->where('locale', $locale)->first())->slug
                                    ?? optional($subPost->slugs->first())->slug;
                                $subUrl = $subSlug ? url('/post/' . $subSlug) : '#';

                                $subHasImage = $subPost->hasImage('cover');
                                $subCategory = $subPost->category ?? null;
                                $subImageAlt = trim(strip_tags($subPost->title)) ?: 'Artikel';
                            @endphp

                            <a
                                href="{{ $subUrl }}"
                                data-analytics="post-click"
                                data-post-id="{{ $subPost->id }}"
                                data-post-title="{{ $subPost->title }}"
                                aria-label="Baca artikel: {{ $subPost->title }}"
                                class="bg-white border border-slate-200 hover:border-[#f0acf7]/60 hover:bg-slate-50/80 rounded-xl sm:rounded-2xl p-2.5 sm:p-3 transition-[background-color,border-color,box-shadow] duration-300 shadow-sm hover:shadow-md group flex items-center gap-3 sm:gap-3.5 flex-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none [contain:layout_style]"
                            >
                                {{-- Thumbnail --}}
                                <div class="w-20 h-20 sm:w-24 sm:h-20 md:w-28 rounded-lg sm:rounded-xl overflow-hidden bg-slate-100 shrink-0 relative">
                                    @if($subHasImage)
                                        {!! twill_proxy_img(
                                            $subPost,
                                            'cover',
                                            [200, 400],
                                            '120px',
                                            $subImageAlt,
                                            [],
                                            null,
                                            [
                                                'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                                'width' => '400',
                                                'height' => '320',
                                                'loading' => 'lazy',
                                                'decoding' => 'async',
                                            ]
                                        ) !!}
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-100">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Text --}}
                                <div class="space-y-1.5 flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 text-[10px] sm:text-[11px] text-slate-500">
                                        @if($subDate)
                                            <span class="shrink-0">{{ $subDate->translatedFormat('M d, Y') }}</span>
                                        @endif
                                        @if($subCategory)
                                            <span class="text-slate-900 font-semibold uppercase tracking-wider truncate">{{ $subCategory }}</span>
                                        @endif
                                    </div>

                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-slate-950 transition-colors duration-300 line-clamp-2 leading-snug">
                                        {{ $subPost->title }}
                                    </h3>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>

                {{-- Lead Article (Kanan) --}}
                <div class="lg:col-span-7 flex order-1 lg:order-2">
                    <a
                        href="{{ $leadUrl }}"
                        data-analytics="post-click"
                        data-post-id="{{ $leadPost->id }}"
                        data-post-title="{{ $leadPost->title }}"
                        aria-label="Baca artikel utama: {{ $leadPost->title }}"
                        class="bg-white border border-slate-200 hover:border-[#f0acf7]/60 hover:bg-slate-50/50 rounded-2xl sm:rounded-3xl overflow-hidden transition-[background-color,border-color,box-shadow] duration-300 shadow-sm hover:shadow-xl hover:shadow-slate-950/5 group flex flex-col justify-between w-full focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none [contain:layout_style]"
                    >
                        <div>
                            {{-- Cover Image — LCP candidate --}}
                            <div class="relative w-full aspect-[16/10] overflow-hidden bg-slate-100">
                                @if($leadHasImage)
                                    {!! twill_proxy_img(
                                        $leadPost,
                                        'cover',
                                        [640, 1024, 1536],
                                        '(max-width: 1024px) 100vw, 60vw',
                                        $leadImageAlt,
                                        [],
                                        null,
                                        [
                                            'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                            'width' => '1536',
                                            'height' => '960',
                                            'loading' => 'eager',
                                            'fetchpriority' => 'high',
                                            'decoding' => 'async',
                                        ]
                                    ) !!}
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-100">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif

                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-transparent opacity-80 pointer-events-none" aria-hidden="true"></div>

                                @if($leadPost->category)
                                    <div class="absolute top-3 left-3 sm:top-4 sm:left-4">
                                        <span class="px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-[#f0acf7]/90 border border-[#acf7f0]/50 text-[10px] font-bold text-white uppercase tracking-wider backdrop-blur-md shadow-sm">
                                            {{ $leadPost->category }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="p-5 sm:p-7 md:p-8 space-y-3 sm:space-y-4">
                                @if($leadDate)
                                    <div class="text-xs font-semibold text-slate-900 flex items-center gap-2">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $leadDate->translatedFormat('F d, Y') }}
                                    </div>
                                @endif

                                <h3 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 group-hover:text-slate-950 transition-colors duration-300 leading-snug line-clamp-3">
                                    {{ $leadPost->title }}
                                </h3>

                                @if($leadExcerpt)
                                    <p class="text-slate-600 text-sm sm:text-base line-clamp-2 leading-relaxed">
                                        {{ $leadExcerpt }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="p-5 sm:p-7 md:p-8 pt-0">
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-slate-700 group-hover:text-slate-950 transition-colors duration-300">
                                <span>BACA SELENGKAPNYA</span>
                                <div class="w-8 h-8 rounded-xl bg-[#acf7f0]/30 border border-[#f0acf7] group-hover:border-[#f0acf7] group-hover:bg-[#acf7f0] flex items-center justify-center text-slate-900 transition-[background-color,border-color] duration-300 shadow-sm motion-reduce:transition-none">
                                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </a>
                </div>

            </div>

        @else
            {{-- Empty State --}}
            <div class="text-center py-8 sm:py-10 bg-white border border-slate-200 rounded-3xl shadow-sm">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <p class="text-slate-500 text-sm font-medium">Belum ada post artikel yang dipublikasikan saat ini.</p>
            </div>
        @endif

    </div>
</section>
