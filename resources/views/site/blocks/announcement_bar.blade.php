@php
    $text = $block->translatedInput('text') ?? '';
    $highlightText = $block->translatedInput('highlightText') ?? $block->translatedInput('highlight_text') ?? '';
    
    // ── DYNAMIC ICON & BUTTON VARIABLES ──
    $icon = $block->input('icon') ?? 'megaphone';
    $buttonText = $block->translatedInput('button_text') ?? '';
    $buttonUrl  = $block->input('button_url') ?? '#';
    $buttonStyle = $block->input('button_style') ?? 'solid';
    
    $style = $block->input('style') ?? 'emerald';
    $styles = [
        'emerald' => 'bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] text-slate-950',
        'blue'    => 'bg-gradient-to-r from-[#acf7f0] via-[#f0acf7] to-[#f7f0ac] text-slate-950',
        'amber'   => 'bg-gradient-to-r from-[#f7f0ac] to-[#f0acf7] text-slate-950',
        'dark'    => 'bg-gradient-to-r from-[#f0acf7] via-[#acf7f0] to-[#f7f0ac] text-slate-950',
    ];
    $bgClass = $styles[$style] ?? $styles['emerald'];
@endphp

@if(!empty($text))
<style>
    @keyframes custom-marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
    .marquee-content { display: inline-flex; white-space: nowrap; animation: custom-marquee 25s linear infinite; will-change: transform; }
    .marquee-content:hover { animation-play-state: paused; }
    @media (prefers-reduced-motion: reduce) { .marquee-content { animation: none !important; } }
</style>

{{-- Posisi: top-20 (di bawah header), z-40 (di bawah menu dropdown) --}}
<div class="sticky top-20 z-40 w-full shadow-sm {{ $bgClass }} overflow-hidden border-b border-black/10 block m-0 p-0 leading-none" data-block-reveal>
    <div class="flex items-center px-4 py-2 sm:px-6 m-0">

        {{-- DYNAMIC ICON --}}
        @if($icon !== 'none')
        <div class="flex-shrink-0 mr-3 opacity-90">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($icon === 'sparkles')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                @elseif($icon === 'alert')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                @elseif($icon === 'info')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                @elseif($icon === 'calendar')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                @else
                    {{-- Default: Megaphone --}}
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                @endif
            </svg>
        </div>
        @endif

        {{-- MARQUEE CONTAINER --}}
        <div class="flex-1 overflow-hidden relative">
            <div class="marquee-content">
                <span class="text-sm font-medium pr-8">{{ $text }}</span>
                @if($highlightText)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-white/75 text-[10px] sm:text-xs font-bold uppercase tracking-wider mr-8">
                        {{ $highlightText }}
                    </span>
                @endif
                <span class="text-sm font-medium pr-8">{{ $text }}</span>
                @if($highlightText)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-white/75 text-[10px] sm:text-xs font-bold uppercase tracking-wider mr-8">
                        {{ $highlightText }}
                    </span>
                @endif
            </div>
        </div>

        {{-- DYNAMIC BUTTON (Kanan) --}}
        @if(!empty($buttonText) && !empty($buttonUrl))
            <div class="flex-shrink-0 ml-4 z-10 hidden sm:block">
                <a href="{{ $buttonUrl }}" 
                   class="inline-flex items-center justify-center px-4 py-1.5 text-xs font-bold rounded-full transition-all duration-200
                   @if($buttonStyle === 'solid')
                       bg-slate-950 text-[#f7f0ac] hover:bg-slate-900 shadow-sm
                   @elseif($buttonStyle === 'outline')
                       border-2 border-slate-950 text-slate-950 hover:bg-white/80
                   @else
                       text-slate-950 underline hover:underline
                   @endif
                   ">
                    {{ $buttonText }}
                </a>
            </div>
        @endif

    </div>
</div>
@endif
