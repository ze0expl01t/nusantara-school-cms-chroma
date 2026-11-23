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

    // Optional: anchor ID dari title (slug-friendly)
    $anchorId = $safeInput($block, 'anchor_id')
        ?: ($title ? \Illuminate\Support\Str::slug($title) : null);
@endphp

<section
    @if($anchorId) id="{{ $anchorId }}" @endif
    class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto text-left space-y-3 scroll-mt-24 [contain:layout_style]"
 data-block-reveal>

    {{-- Badge --}}
    @if($badge)
        <div class="inline-block">
            <span class="inline-block px-3 py-1 text-[11px] sm:text-xs font-bold tracking-widest text-slate-900 uppercase bg-[#acf7f0]/30 border border-[#f0acf7] rounded-full shadow-sm">
                {{ $badge }}
            </span>
        </div>
    @endif

    {{-- Title — text-wrap:balance untuk cegah widow --}}
    @if($title)
        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight [text-wrap:balance]">
            {{ $title }}
        </h2>
    @endif

    {{-- Description --}}
    @if($description)
        <div class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-3xl prose prose-sm sm:prose-base prose-p:my-2 prose-p:leading-relaxed prose-strong:text-slate-900 prose-ul:my-2 prose-ul:list-disc prose-ul:list-inside prose-ol:my-2 prose-ol:list-decimal prose-ol:list-inside prose-li:my-0.5 prose-a:text-slate-900 prose-a:underline">
            {!! safe_html($description) !!}
        </div>
    @endif

</section>
