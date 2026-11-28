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

    $cleanText = function (?string $html): string {
        if (!filled($html)) {
            return '';
        }
        $decoded = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = preg_replace('/\s+/u', ' ', $decoded);
        return trim((string) $decoded);
    };

    $sectionTitle = $safeInput($block, 'title');
    $limit        = max((int) $safeInput($block, 'limit', 6), 1);

    $posts = Post::where('published', true)
        ->with(['slugs', 'medias'])
        ->orderBy('post_date', 'desc')
        ->take($limit)
        ->get();

    $locale = app()->getLocale();

    $prefetchUrls = $posts->take(2)->map(function ($post) use ($locale) {
        $slug = $post->slug
            ?? optional($post->slugs->where('locale', $locale)->first())->slug
            ?? optional($post->slugs->first())->slug;
        return $slug ? url('/post/' . $slug) : null;
    })->filter()->values();
@endphp

@push('head')
    @foreach($prefetchUrls as $url)
        <link rel="prefetch" href="{{ $url }}">
    @endforeach
@endpush

<section class="py-10 sm:py-14 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>

    <div class="relative z-10 space-y-8 sm:space-y-10">

        @if(filled($sectionTitle))
            <div class="flex flex-col items-center text-center space-y-3 pb-2 sm:pb-4">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white border border-slate-200/90 shadow-2xs text-xs font-extrabold text-slate-950 tracking-wide uppercase">
                    <span class="w-2 h-2 rounded-full bg-[#acf7f0]" aria-hidden="true"></span>
                    <span>Artikel &amp; Berita</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-[2.65rem] font-extrabold text-slate-900 tracking-tight leading-[1.1] px-2 [text-wrap:balance]">
                    {{ $sectionTitle }}
                </h2>
                <div class="w-20 h-1 rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>
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

                $leadHasImage  = $leadPost->hasImage('cover');
                $leadCleanDesc = $cleanText($leadPost->description);
                $leadExcerpt   = filled($leadCleanDesc) ? Str::limit($leadCleanDesc, 175, '…') : null;
                $leadImageAlt  = $cleanText($leadPost->title);
                $leadKey       = $leadPost->id ?? 1;
                $leadFbUrl     = 'https://picsum.photos/seed/smkmuh1-post-lead-' . $leadKey . '/1200/750.webp';
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-stretch">

                {{-- Side List (Left) --}}
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

                                $subHasImage  = $subPost->hasImage('cover');
                                $subCategory  = $subPost->category ?? null;
                                $subTitleText = $cleanText($subPost->title);
                                $subKey       = $subPost->id ?? ($loop->index + 2);
                                $subFbUrl     = 'https://picsum.photos/seed/smkmuh1-post-sub-' . $subKey . '/400/320.webp';
                            @endphp

                            <a
                                href="{{ $subUrl }}"
                                data-analytics="post-click"
                                data-post-id="{{ $subPost->id }}"
                                data-post-title="{{ $subTitleText }}"
                                aria-label="{{ $subTitleText }}"
                                class="bg-white border border-slate-200/90 hover:border-slate-950/20 rounded-2xl p-2.5 sm:p-3 transition-all duration-300 shadow-2xs hover:shadow-md hover:-translate-y-0.5 group flex items-center gap-3 sm:gap-3.5 flex-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style]"
                            >
                                <div class="w-20 h-20 sm:w-24 sm:h-20 md:w-28 rounded-xl overflow-hidden bg-gradient-to-br from-[#f7f0ac]/60 via-[#acf7f0]/50 to-[#f0acf7]/60 border border-slate-200/80 shrink-0 relative">
                                    <img
                                        src="{{ $subFbUrl }}"
                                        alt="{{ $subTitleText }}"
                                        width="400"
                                        height="320"
                                        loading="lazy"
                                        decoding="async"
                                        onerror="this.style.display='none'"
                                        class="absolute inset-0 z-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                                    >
                                    @if($subHasImage)
                                        {!! twill_proxy_img(
                                            $subPost,
                                            'cover',
                                            [200, 400],
                                            '120px',
                                            $subTitleText,
                                            [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                            [
                                                'class' => 'relative z-10 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                                'width' => '400',
                                                'height' => '320',
                                                'loading' => 'lazy',
                                                'decoding' => 'async',
                                                'onerror' => "this.style.display='none'",
                                            ]
                                        ) !!}
                                    @endif
                                </div>

                                <div class="space-y-1.5 flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 text-[11px] text-slate-600">
                                        @if($subDate)
                                            <span class="shrink-0 font-medium">{{ $subDate->translatedFormat('M d, Y') }}</span>
                                        @endif
                                        @if(filled($subCategory))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#acf7f0]/55 border border-[#acf7f0] text-[10px] font-extrabold text-slate-950 uppercase tracking-wider truncate">
                                                {{ $subCategory }}
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="text-xs sm:text-sm font-bold text-slate-950 group-hover:text-slate-950 transition-colors duration-200 line-clamp-2 leading-snug">
                                        {{ $subTitleText }}
                                    </h3>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>

                {{-- Lead Article (Right) --}}
                <div class="lg:col-span-7 flex order-1 lg:order-2">
                    <a
                        href="{{ $leadUrl }}"
                        data-analytics="post-click"
                        data-post-id="{{ $leadPost->id }}"
                        data-post-title="{{ $leadImageAlt }}"
                        aria-label="{{ $leadImageAlt }}"
                        class="bg-white border border-slate-200/90 hover:border-slate-950/20 rounded-2xl overflow-hidden transition-all duration-300 shadow-2xs hover:shadow-xl hover:-translate-y-1 group flex flex-col justify-between w-full focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style]"
                    >
                        <div>
                            <div class="relative w-full aspect-[16/10] overflow-hidden bg-gradient-to-br from-[#f7f0ac]/60 via-[#acf7f0]/50 to-[#f0acf7]/60 border-b border-slate-200/80">
                                <img
                                    src="{{ $leadFbUrl }}"
                                    alt="{{ $leadImageAlt }}"
                                    width="1200"
                                    height="750"
                                    loading="eager"
                                    decoding="async"
                                    onerror="this.style.display='none'"
                                    class="absolute inset-0 z-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                                >
                                @if($leadHasImage)
                                    {!! twill_proxy_img(
                                        $leadPost,
                                        'cover',
                                        [640, 1024, 1536],
                                        '(max-width: 1024px) 100vw, 60vw',
                                        $leadImageAlt,
                                        [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80',
                                        [
                                            'class' => 'relative z-10 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none motion-reduce:group-hover:scale-100',
                                            'width' => '1536',
                                            'height' => '960',
                                            'loading' => 'eager',
                                            'fetchpriority' => 'high',
                                            'decoding' => 'async',
                                            'onerror' => "this.style.display='none'",
                                        ]
                                    ) !!}
                                @endif

                                @if(filled($leadPost->category))
                                    <div class="absolute top-3.5 left-3.5 sm:top-4 sm:left-4 z-20">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-[#acf7f0]/90 border border-slate-950/15 text-[10px] sm:text-xs font-extrabold text-slate-950 uppercase tracking-wider shadow-xs">
                                            {{ $leadPost->category }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="p-5 sm:p-7 md:p-8 space-y-3 sm:space-y-4">
                                @if($leadDate)
                                    <div class="text-xs font-bold text-slate-600 flex items-center gap-2">
                                        <svg class="w-4 h-4 shrink-0 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>{{ $leadDate->translatedFormat('F d, Y') }}</span>
                                    </div>
                                @endif

                                <h3 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-950 leading-snug line-clamp-3">
                                    {{ $leadImageAlt }}
                                </h3>

                                @if(filled($leadExcerpt))
                                    <p class="text-slate-600 text-sm sm:text-base line-clamp-2 leading-relaxed">
                                        {{ $leadExcerpt }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="p-5 sm:p-7 md:p-8 pt-0">
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold text-slate-950 tracking-wider uppercase">
                                <span>BACA SELENGKAPNYA</span>
                                <div class="w-8 h-8 rounded-full bg-[#acf7f0]/55 border border-[#acf7f0] group-hover:bg-gradient-to-r group-hover:from-[#f7f0ac] group-hover:via-[#acf7f0] group-hover:to-[#f0acf7] flex items-center justify-center text-slate-950 transition-all duration-300 motion-reduce:transition-none">
                                    <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
        @endif

    </div>
</section>
