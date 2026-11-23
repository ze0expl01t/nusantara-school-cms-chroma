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

    $subtitle    = $safeInput($block, 'subtitle');
    $title       = $safeInput($block, 'title');
    $description = $safeInput($block, 'description');

    // Optional: anchor ID dari title (slug-friendly)
    $anchorId = $safeInput($block, 'anchor_id')
        ?: ($title ? \Illuminate\Support\Str::slug($title) : null);
@endphp

<section
    @if($anchorId) id="{{ $anchorId }}" @endif
    class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto text-center space-y-4 sm:space-y-5 scroll-mt-24 [contain:layout_style]"
 data-block-reveal>

    {{-- Subtitle / Badge --}}
    @if($subtitle)
        <div class="inline-block">
            <span class="inline-block px-3 py-1 text-[11px] sm:text-xs font-bold tracking-widest text-slate-900 uppercase bg-[#acf7f0]/30 border border-[#f0acf7] rounded-full shadow-sm">
                {{ $subtitle }}
            </span>
        </div>
    @endif

    {{-- Title — [text-wrap:balance] hindari widow (1 kata di baris akhir) --}}
    @if($title)
        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight px-2 [text-wrap:balance]">
            {{ $title }}
        </h2>
    @endif

    {{-- Description --}}
    @if($description)
        <div class="text-sm sm:text-base text-slate-600 max-w-2xl mx-auto leading-relaxed px-2 prose prose-sm sm:prose-base prose-p:my-0 prose-p:leading-relaxed">
            {!! safe_html($description) !!}
        </div>
    @endif

</section>
