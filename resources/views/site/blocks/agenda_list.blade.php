@php
    use App\Models\Agenda;
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $safeInput = function ($block, $field, $default = null) {
        $value = $block->input($field);
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? $default;
        }
        return $value ?: $default;
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
    $limit = (int) $safeInput($block, 'limit', 6);

    $agendas = Agenda::where('published', true)
        ->with(['slugs'])
        ->orderBy('agenda_date', 'asc')
        ->take($limit)
        ->get();

    $locale = app()->getLocale();
    Carbon::setLocale($locale);
    $nowWib = Carbon::now('Asia/Jakarta');

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

<section
    x-data="{ agendaFilter: 'all' }"
    class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:700px]"
    data-block-reveal
>

    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[80px] rounded-full pointer-events-none [transform:translate3d(-50%,-50%,0)]"></div>

    <div class="relative z-10 space-y-8 sm:space-y-10">

        @if($sectionTitle)
            <div class="flex flex-col items-center text-center space-y-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-semibold text-slate-600">
                    <span class="w-2 h-2 rounded-full bg-[#acf7f0] animate-pulse motion-reduce:animate-none" aria-hidden="true"></span>
                    Jadwal & Kegiatan
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2">
                    {{ $sectionTitle }}
                </h2>
                <div class="w-20 h-1 bg-gradient-to-r from-transparent via-[#acf7f0] to-transparent rounded-full" aria-hidden="true"></div>
            </div>
        @endif

        @if($agendas->count() > 0)
            <!-- Filter Status Agenda: Semua / Mendatang / Selesai -->
            <div class="flex flex-wrap items-center justify-center gap-2" role="group" aria-label="Filter status agenda">
                <button
                    type="button"
                    @click="agendaFilter = 'all'"
                    :class="agendaFilter === 'all' ? 'bg-[#f0acf7] text-slate-950 border-[#f0acf7] shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-[#f0acf7] hover:text-slate-950'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold border transition-colors"
                >
                    Semua ({{ $agendas->count() }})
                </button>
                <button
                    type="button"
                    @click="agendaFilter = 'upcoming'"
                    :class="agendaFilter === 'upcoming' ? 'bg-[#f0acf7] text-slate-950 border-[#f0acf7] shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-[#f0acf7] hover:text-slate-950'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold border transition-colors"
                >
                    Mendatang
                </button>
                <button
                    type="button"
                    @click="agendaFilter = 'past'"
                    :class="agendaFilter === 'past' ? 'bg-[#f0acf7] text-slate-950 border-[#f0acf7] shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-[#f0acf7] hover:text-slate-950'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold border transition-colors"
                >
                    Selesai
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
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
                            ? Str::limit($cleanDescription, 160)
                            : null;
                    @endphp

                    <article
                        x-show="agendaFilter === 'all' || agendaFilter === '{{ $statusKey }}'"
                        x-cloak
                        data-analytics="agenda-click"
                        data-agenda-id="{{ $agenda->id }}"
                        data-agenda-title="{{ $agenda->title }}"
                        class="bg-white border border-slate-200 hover:border-[#f0acf7] hover:shadow-xl hover:shadow-slate-950/10 rounded-2xl sm:rounded-3xl p-5 sm:p-6 transition-all duration-300 group flex flex-col justify-between space-y-5 sm:space-y-6 hover:-translate-y-1.5 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [contain:layout_style] [transform:translateZ(0)]"
                    >

                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                @if($dateObj || $jamLabel)
                                    <div class="flex items-center gap-3">
                                        @if($dateObj)
                                            <div class="w-12 h-12 rounded-2xl bg-[#acf7f0]/30 border border-[#acf7f0] group-hover:border-[#acf7f0] group-hover:bg-[#acf7f0] flex flex-col items-center justify-center text-slate-900 shrink-0 transition-all duration-300 shadow-sm">
                                                <span class="text-[10px] font-bold leading-none uppercase tracking-wider">
                                                    {{ $dateObj->translatedFormat('M') }}
                                                </span>
                                                <span class="text-base font-black leading-tight">
                                                    {{ $dateObj->format('d') }}
                                                </span>
                                            </div>
                                        @endif
                                        <div>
                                            @if($dateObj)
                                                <span class="text-xs font-semibold text-slate-500 block">
                                                    {{ $dateObj->translatedFormat('l') }}
                                                </span>
                                            @endif
                                            @if($jamLabel)
                                                <span class="text-xs font-bold text-slate-900 block">
                                                    {{ $jamLabel }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shrink-0 border {{ $isPast ? 'bg-slate-100 border-slate-200 text-slate-600' : 'bg-[#acf7f0]/30 border-[#f0acf7] text-slate-900' }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <h3 class="text-base sm:text-lg md:text-xl font-bold text-slate-900 group-hover:text-slate-950 transition-colors duration-300 leading-snug line-clamp-2">
                                <a href="{{ $agendaUrl }}" class="focus:outline-none focus-visible:underline">
                                    {{ $agenda->title }}
                                </a>
                            </h3>

                            @if($tempatLabel || $penyelenggaraLabel || $pesertaLabel)
                                <div class="space-y-1.5 text-xs text-slate-600 bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-100">
                                    @if($tempatLabel)
                                        <div class="flex items-start gap-2">
                                            <span class="font-semibold text-slate-700 shrink-0">Tempat:</span>
                                            <span class="line-clamp-1">{{ $tempatLabel }}</span>
                                        </div>
                                    @endif
                                    @if($penyelenggaraLabel)
                                        <div class="flex items-start gap-2">
                                            <span class="font-semibold text-slate-700 shrink-0">Penyelenggara:</span>
                                            <span class="line-clamp-1">{{ $penyelenggaraLabel }}</span>
                                        </div>
                                    @endif
                                    @if($pesertaLabel)
                                        <div class="flex items-start gap-2">
                                            <span class="font-semibold text-slate-700 shrink-0">Peserta:</span>
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

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3 text-xs font-semibold">
                            @if($safeExternalUrl)
                                <a
                                    href="{{ $safeExternalUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-slate-900 hover:text-slate-950 underline underline-offset-4 truncate"
                                >
                                    Tautan / URL
                                </a>
                            @else
                                <span class="text-slate-400">Agenda</span>
                            @endif

                            <a
                                href="{{ $agendaUrl }}"
                                aria-label="Lihat detail agenda: {{ $agenda->title }}"
                                class="inline-flex items-center gap-1.5 text-slate-600 group-hover:text-slate-950 transition-colors shrink-0"
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
            <div class="text-center py-16 bg-white border border-slate-200 rounded-3xl shadow-sm">
                <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-slate-500 text-sm font-medium">Belum ada agenda kegiatan yang dipublikasikan saat ini.</p>
            </div>
        @endif

    </div>
</section>
