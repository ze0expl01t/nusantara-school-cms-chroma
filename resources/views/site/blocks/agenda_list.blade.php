@php
    use App\Models\Agenda;
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $safeInput = function ($block, $field, $default = null) {
        if (!$block || !method_exists($block, 'input')) {
            return $default;
        }
        $value = $block->input($field);
        if (is_array($value)) {
            $picked = $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? (count($value) ? reset($value) : null);
            return filled($picked) ? $picked : $default;
        }
        return filled($value) ? $value : $default;
    };

    $sanitizeHttpUrl = function ($url) {
        if (empty($url) || !is_string($url)) {
            return null;
        }
        $trimmed = trim($url);
        if (!preg_match('#^https?://#i', $trimmed)) {
            return null;
        }
        return filter_var($trimmed, FILTER_VALIDATE_URL) ? $trimmed : null;
    };

    $sectionTitle = $safeInput($block, 'title', 'Agenda Kegiatan');
    $subtitle     = $safeInput($block, 'subtitle');
    $limit        = max(1, (int) $safeInput($block, 'limit', 6));

    $agendas = Agenda::where('published', true)
        ->with(['slugs'])
        ->orderBy('agenda_date', 'asc')
        ->take($limit)
        ->get();

    $locale = app()->getLocale();
    Carbon::setLocale($locale);

    $upcomingCount = $agendas->filter(function ($item) use ($locale) {
        if (!$item->agenda_date) return false;
        return !Carbon::parse($item->agenda_date)->locale($locale)->timezone('Asia/Jakarta')->isPast();
    })->count();

    $pastCount = $agendas->count() - $upcomingCount;

    $prefetchUrls = $agendas->take(2)->map(function ($agenda) use ($locale) {
        $slug = $agenda->slug
            ?? optional($agenda->slugs->where('locale', $locale)->first())->slug
            ?? optional($agenda->slugs->first())->slug;
        return $slug ? url('/agenda/' . $slug) : null;
    })->filter()->values();
@endphp

@push('head')
    @foreach($prefetchUrls as $url)
        <link rel="prefetch" href="{{ $url }}">
    @endforeach
@endpush

<section class="py-10 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 [content-visibility:auto] [contain-intrinsic-size:750px]" data-block-reveal>

    <div
        x-data="{ agendaFilter: 'all' }"
        class="relative bg-gradient-to-br from-[#fffef9] via-[#f3fbfa] to-[#fcf7fc] text-slate-900 border border-slate-200/90 rounded-2xl px-5 sm:px-8 lg:px-10 py-8 sm:py-10 lg:py-14 shadow-lg overflow-hidden [contain:layout_style]"
    >
        {{-- Top 3-color global accent line --}}
        <div class="pointer-events-none absolute top-0 inset-x-8 h-1 rounded-b-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>

        {{-- Soft global color washes & subtle geometric ornaments --}}
        <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 -left-20 w-72 h-72 sm:w-[400px] sm:h-[400px] bg-[#f7f0ac]/45 blur-[80px] rounded-full"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 sm:w-[420px] sm:h-[420px] bg-[#acf7f0]/40 blur-[90px] rounded-full"></div>
            <div class="absolute -bottom-24 -right-16 w-72 h-72 sm:w-[400px] sm:h-[400px] bg-[#f0acf7]/35 blur-[80px] rounded-full"></div>

            <div
                class="absolute inset-0 opacity-45"
                style="background-image: radial-gradient(rgba(15, 23, 42, 0.11) 1px, transparent 1px); background-size: 24px 24px; -webkit-mask-image: radial-gradient(ellipse at 50% 35%, #000 10%, transparent 72%); mask-image: radial-gradient(ellipse at 50% 35%, #000 10%, transparent 72%);"
            ></div>

            <svg class="hidden sm:block absolute top-7 right-9 w-24 h-24 text-slate-900/15" viewBox="0 0 96 96" fill="none">
                <circle cx="48" cy="48" r="44" stroke="currentColor" stroke-width="1" stroke-dasharray="4 6"/>
                <circle cx="48" cy="48" r="28" stroke="url(#agLightRingGrad)" stroke-width="1.5" stroke-opacity="0.7"/>
                <defs>
                    <linearGradient id="agLightRingGrad" x1="0" y1="0" x2="96" y2="96" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#e6d965"/>
                        <stop offset="50%" stop-color="#58d6cb"/>
                        <stop offset="100%" stop-color="#d875e3"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <div class="relative z-10 space-y-8 sm:space-y-10">

            @if(filled($sectionTitle))
                <div class="flex flex-col items-center text-center space-y-3 max-w-2xl mx-auto">
                    <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-white/90 border border-slate-200/90 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-[#acf7f0]" aria-hidden="true"></span>
                        <span class="text-xs font-extrabold tracking-widest text-slate-700 uppercase">Jadwal &amp; Kegiatan</span>
                    </div>

                    <h2 class="text-3xl sm:text-4xl lg:text-[2.65rem] font-extrabold text-slate-900 tracking-tight leading-[1.1] [text-wrap:balance]">
                        {{ $sectionTitle }}
                    </h2>

                    <div class="w-24 h-1 rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>

                    @if(filled($subtitle))
                        <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
            @endif

            @if($agendas->count() > 0)
                {{-- Filter Status Agenda --}}
                <div class="flex flex-wrap items-center justify-center gap-2.5" role="group" aria-label="Filter status agenda">
                    <button
                        type="button"
                        @click="agendaFilter = 'all'"
                        :class="agendaFilter === 'all'
                            ? 'bg-[#acf7f0] text-slate-950 border-[#acf7f0] shadow-sm'
                            : 'bg-white/90 text-slate-600 border-slate-200 hover:border-[#acf7f0] hover:text-slate-950'"
                        class="px-4 py-2 rounded-full text-xs font-extrabold border transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] motion-reduce:transition-none"
                    >
                        Semua ({{ $agendas->count() }})
                    </button>
                    <button
                        type="button"
                        @click="agendaFilter = 'upcoming'"
                        :class="agendaFilter === 'upcoming'
                            ? 'bg-[#acf7f0] text-slate-950 border-[#acf7f0] shadow-sm'
                            : 'bg-white/90 text-slate-600 border-slate-200 hover:border-[#acf7f0] hover:text-slate-950'"
                        class="px-4 py-2 rounded-full text-xs font-extrabold border transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] motion-reduce:transition-none"
                    >
                        Mendatang ({{ $upcomingCount }})
                    </button>
                    <button
                        type="button"
                        @click="agendaFilter = 'past'"
                        :class="agendaFilter === 'past'
                            ? 'bg-[#acf7f0] text-slate-950 border-[#acf7f0] shadow-sm'
                            : 'bg-white/90 text-slate-600 border-slate-200 hover:border-[#acf7f0] hover:text-slate-950'"
                        class="px-4 py-2 rounded-full text-xs font-extrabold border transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] motion-reduce:transition-none"
                    >
                        Selesai ({{ $pastCount }})
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 items-stretch">
                    @foreach($agendas as $agenda)
                        @php
                            $dateObj = $agenda->agenda_date
                                ? Carbon::parse($agenda->agenda_date)
                                    ->locale($locale)
                                    ->timezone('Asia/Jakarta')
                                : null;

                            $isPast = $dateObj ? $dateObj->isPast() : false;
                            $statusKey = $isPast ? 'past' : 'upcoming';
                            $statusLabel = $isPast ? 'Selesai' : 'Mendatang';

                            $jamLabel = filled($agenda->time_slot ?? null)
                                ? trim((string) $agenda->time_slot)
                                : ($dateObj ? $dateObj->format('H:i') . ' WIB' : null);

                            $tempatLabel = filled($agenda->venue ?? null)
                                ? trim((string) $agenda->venue)
                                : (filled($agenda->location ?? null) ? trim((string) $agenda->location) : null);

                            $penyelenggaraLabel = filled($agenda->organizer ?? null)
                                ? trim((string) $agenda->organizer)
                                : null;

                            $pesertaLabel = filled($agenda->participants ?? null)
                                ? trim((string) $agenda->participants)
                                : null;

                            $safeExternalUrl = $sanitizeHttpUrl($agenda->external_url ?? null);

                            $slugValue = $agenda->slug
                                ?? optional($agenda->slugs->where('locale', $locale)->first())->slug
                                ?? optional($agenda->slugs->first())->slug;
                            $agendaUrl = $slugValue ? url('/agenda/' . $slugValue) : '#';

                            $cleanDescription = $agenda->description
                                ? preg_replace("/\n{3,}/", "\n\n", trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ["\n", "\n", "\n", "\n\n"], $agenda->description))))
                                : null;

                            $excerpt = $cleanDescription
                                ? Str::limit($cleanDescription, 155)
                                : null;
                        @endphp

                        <article
                            x-show="agendaFilter === 'all' || agendaFilter === '{{ $statusKey }}'"
                            x-cloak
                            data-analytics="agenda-click"
                            data-agenda-id="{{ $agenda->id }}"
                            data-agenda-title="{{ $agenda->title }}"
                            class="relative bg-white/95 border border-slate-200/90 hover:border-[#acf7f0] rounded-2xl sm:rounded-2xl p-5 sm:p-6 overflow-hidden transition-all duration-300 group flex flex-col justify-between space-y-5 shadow-sm hover:-translate-y-1 hover:shadow-xl motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style] [transform:translateZ(0)]"
                        >
                            <span class="pointer-events-none absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] opacity-80 group-hover:opacity-100 transition-opacity duration-300" aria-hidden="true"></span>

                            <div class="space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    @if($dateObj || $jamLabel)
                                        <div class="flex items-center gap-3 min-w-0">
                                            @if($dateObj)
                                                <div class="p-[1.5px] rounded-2xl bg-gradient-to-br from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] shrink-0 shadow-sm">
                                                    <div class="w-12 h-12 rounded-[14px] bg-[#f3fbfa] group-hover:bg-gradient-to-br group-hover:from-[#f7f0ac] group-hover:via-[#acf7f0] group-hover:to-[#f0acf7] flex flex-col items-center justify-center text-slate-900 transition-all duration-300">
                                                        <span class="text-[10px] font-bold leading-none uppercase tracking-wider text-slate-600 group-hover:text-slate-950">
                                                            {{ $dateObj->translatedFormat('M') }}
                                                        </span>
                                                        <span class="text-base font-black leading-tight mt-0.5 text-slate-950">
                                                            {{ $dateObj->format('d') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                @if($dateObj)
                                                    <span class="text-xs font-medium text-slate-500 block truncate">
                                                        {{ $dateObj->translatedFormat('l') }}
                                                    </span>
                                                @endif
                                                @if($jamLabel)
                                                    <span class="text-xs font-bold text-slate-900 block truncate">
                                                        {{ $jamLabel }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider shrink-0 border {{ $isPast ? 'bg-slate-100 border-slate-200 text-slate-600' : 'bg-[#acf7f0]/35 border-[#acf7f0] text-slate-950' }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <h3 class="text-base sm:text-lg md:text-xl font-extrabold text-slate-900 group-hover:text-slate-950 transition-colors duration-200 leading-snug line-clamp-2">
                                    <a href="{{ $agendaUrl }}" class="focus:outline-none focus-visible:underline">
                                        {{ $agenda->title }}
                                    </a>
                                </h3>

                                @if($tempatLabel || $penyelenggaraLabel || $pesertaLabel)
                                    <div class="space-y-1.5 text-xs text-slate-600 bg-gradient-to-r from-[#fffef9] via-[#f3fbfa] to-[#fcf7fc] px-3.5 py-2.5 rounded-xl border border-slate-200/80">
                                        @if($tempatLabel)
                                            <div class="flex items-start gap-2">
                                                <span class="font-extrabold text-slate-900 shrink-0">Tempat:</span>
                                                <span class="line-clamp-2">{{ $tempatLabel }}</span>
                                            </div>
                                        @endif
                                        @if($penyelenggaraLabel)
                                            <div class="flex items-start gap-2">
                                                <span class="font-extrabold text-slate-900 shrink-0">Penyelenggara:</span>
                                                <span class="line-clamp-1">{{ $penyelenggaraLabel }}</span>
                                            </div>
                                        @endif
                                        @if($pesertaLabel)
                                            <div class="flex items-start gap-2">
                                                <span class="font-extrabold text-slate-900 shrink-0">Peserta:</span>
                                                <span class="line-clamp-1">{{ $pesertaLabel }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($excerpt)
                                    <p class="text-slate-600 text-sm line-clamp-3 leading-relaxed whitespace-pre-line">
                                        {{ $excerpt }}
                                    </p>
                                @endif
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3 text-xs font-bold">
                                @if($safeExternalUrl)
                                    <a
                                        href="{{ $safeExternalUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-slate-900 hover:underline underline-offset-4 truncate transition-colors"
                                    >
                                        Tautan / URL
                                    </a>
                                @else
                                    <span class="text-slate-400">Agenda</span>
                                @endif

                                <a
                                    href="{{ $agendaUrl }}"
                                    aria-label="Lihat detail agenda: {{ $agenda->title }}"
                                    class="inline-flex items-center gap-1.5 text-slate-700 group-hover:text-slate-950 transition-colors shrink-0"
                                >
                                    <span>Detail Kegiatan</span>
                                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-300 motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </a>
                            </div>

                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-white/90 border border-slate-200 rounded-2xl shadow-sm">
                    <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-slate-600 text-sm font-medium">Belum ada agenda kegiatan yang dipublikasikan saat ini.</p>
                </div>
            @endif

        </div>
    </div>
</section>
