@php
    // ===== Helper: Safe URL (cegah javascript:, data:, vbscript:) =====
    $safeUrl = function ($url, $fallback = '#') {
        if (empty($url) || !is_string($url)) {
            return $fallback;
        }

        $url = trim($url);

        $dangerous = ['javascript:', 'data:', 'vbscript:', 'file:'];
        foreach ($dangerous as $scheme) {
            if (stripos($url, $scheme) === 0) {
                return $fallback;
            }
        }

        if (preg_match('#^(https?:|mailto:|tel:|/|\#)#i', $url)) {
            return $url;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return $fallback;
        }

        return $url;
    };

    // ===== Helper: Safe Input (translatable) =====
    $safeInput = function ($block, $field, $default = null) {
        if (!method_exists($block, 'input')) {
            return $default;
        }

        $value = $block->input($field);

        if (is_array($value)) {
            $locale   = app()->getLocale();
            $fallback = config('app.fallback_locale', 'en');

            $picked = $value[$locale]
                ?? $value[$fallback]
                ?? (count($value) ? reset($value) : null);

            return filled($picked) ? $picked : $default;
        }

        return filled($value) ? $value : $default;
    };

    // ===== Ambil Data Block =====
    $badgeText          = $safeInput($block, 'badge_text', 'FAQ');
    $titleMain          = $safeInput($block, 'title_main', 'Pertanyaan');
    $titleHighlight     = $safeInput($block, 'title_highlight', 'Yang Sering Diajukan');
    $subtitle           = $safeInput($block, 'subtitle');
    $contactCtaText     = $safeInput($block, 'contact_cta_text');
    $contactButtonText  = $safeInput($block, 'contact_button_text', 'Hubungi Kami');

    // ===== ✅ SANITASI URL =====
    $contactButtonUrl = $safeUrl($block->input('contact_button_url'), url('/kontak'));

    // ===== Ambil FAQ Items (child blocks - Twill 3.6) =====
    $faqItems = collect();
    try {
        if (method_exists($block, 'children')) {
            $faqItems = $block->children()
                ->where('type', 'faq_item')
                ->orderBy('position')
                ->get();
        }
    } catch (\Throwable $e) {
        $faqItems = collect();
    }

    // ═══ Pre-process FAQ data sekali (hindari N+1 translatedInput di loop) ═══
    $locale = app()->getLocale();
    $faqData = $faqItems->map(function ($item) use ($locale) {
        try {
            $question = method_exists($item, 'translatedInput')
                ? ($item->translatedInput('question') ?: $item->input('question'))
                : $item->input('question');

            $answer = method_exists($item, 'translatedInput')
                ? ($item->translatedInput('answer') ?: $item->input('answer'))
                : $item->input('answer');

            $isOpen = (bool) $item->input('is_open');

            return [
                'question' => $question,
                'answer'   => $answer,
                'is_open'  => $isOpen,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    })->filter(fn ($x) => !empty($x['question']))->values();
@endphp

@if($faqData->count() > 0)
<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:700px]" data-block-reveal>

    {{-- Ambient Glow — blur dikurangi untuk hemat GPU paint --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[80px] rounded-full pointer-events-none [transform:translate3d(-50%,-50%,0)]"></div>

    <div class="relative z-10 max-w-3xl mx-auto space-y-8 sm:space-y-10">

        {{-- ===== Header Section ===== --}}
        <div class="flex flex-col items-center text-center space-y-3 pb-4 sm:pb-6">
            @if($badgeText)
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white border border-slate-200/90 shadow-2xs text-xs font-extrabold text-slate-950 tracking-wide uppercase">
                    <span class="w-2 h-2 rounded-full bg-[#acf7f0]" aria-hidden="true"></span>
                    {{ $badgeText }}
                </div>
            @endif

            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2">
                {{ $titleMain }}
                @if($titleHighlight)
                    <span class="text-slate-900">{{ $titleHighlight }}</span>
                @endif
            </h2>

            <div class="w-20 h-1 mx-auto rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>

            @if($subtitle)
                <p class="text-base md:text-lg text-slate-600 max-w-2xl px-2 pt-2">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        {{-- ===== FAQ Accordion ===== --}}
        <div class="space-y-3">
            @foreach($faqData as $index => $faq)
                <div x-data="{ open: {{ $faq['is_open'] ? 'true' : 'false' }} }"
                     class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:border-[#f0acf7]/30 transition-[box-shadow,border-color] duration-300 motion-reduce:transition-none [contain:layout_style]">

                    <button type="button"
                            @click="open = !open"
                            :aria-expanded="open ? 'true' : 'false'"
                            :aria-controls="'faq-answer-{{ $index }}'"
                            class="w-full flex items-center justify-between gap-4 px-5 sm:px-6 py-4 sm:py-5 text-left transition-colors duration-200 hover:bg-slate-50/50 motion-reduce:transition-none focus:outline-none focus-visible:bg-slate-50/70">

                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-[#acf7f0]/30 border border-[#acf7f0] flex items-center justify-center text-slate-900 text-xs font-extrabold group-hover:bg-[#acf7f0] transition-colors duration-300 motion-reduce:transition-none" aria-hidden="true">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="flex-1 text-sm sm:text-base font-bold text-slate-900 leading-snug pt-1">
                                {{ $faq['question'] }}
                            </span>
                        </div>

                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 group-hover:bg-[#acf7f0] flex items-center justify-center text-slate-500 group-hover:text-slate-950 transition-colors duration-300 motion-reduce:transition-none [transform:translateZ(0)]">
                            <svg class="w-4 h-4 transform transition-transform duration-300 motion-reduce:transition-none"
                                 :class="open ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    @if($faq['answer'])
                        <div x-show="open"
                             x-collapse
                             x-cloak
                             id="faq-answer-{{ $index }}"
                             class="border-t border-slate-100">
                            <div class="px-5 sm:px-6 py-4 sm:py-5 pl-5 sm:pl-16 text-sm text-slate-600 leading-relaxed">
                                {!! nl2br(e($faq['answer'])) !!}
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ===== Contact CTA ===== --}}
        @if($contactCtaText)
            <div class="text-center pt-4">
                <p class="text-slate-600 text-sm sm:text-base mb-4">
                    {{ $contactCtaText }}
                </p>

                @if($contactButtonText && $contactButtonUrl && $contactButtonUrl !== '#')
                    <a href="{{ $contactButtonUrl }}"
                       @if(str_starts_with($contactButtonUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif
                       class="inline-flex items-center gap-2 px-6 sm:px-8 py-3.5 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold shadow-lg shadow-slate-950/15 transition-[transform,background-color,box-shadow] duration-300 transform hover:-translate-y-1 text-sm sm:text-base motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                        {{ $contactButtonText }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                @endif
            </div>
        @endif

    </div>
</section>
@endif
