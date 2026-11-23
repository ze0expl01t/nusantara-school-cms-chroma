@extends('site.layouts.master')

@section('title', isset($agenda) && $agenda->title ? $agenda->title . ' | SMKMUH1BLORA' : 'Detail Agenda | HSE & K3 Applications')

@php
    $slugCurrent = $agenda->slug
        ?? optional($agenda->slugs->where('locale', app()->getLocale())->first())->slug
        ?? optional($agenda->slugs->first())->slug
        ?? request()->route('slug');

    $dateObj = $agenda->agenda_date
        ? \Carbon\Carbon::parse($agenda->agenda_date)->locale(app()->getLocale())->timezone('Asia/Jakarta')
        : null;

    $isPast = $dateObj ? $dateObj->isPast() : false;
    $statusLabel = $isPast ? 'Selesai' : 'Mendatang';

    $jamDetail = filled($agenda->time_slot ?? null)
        ? trim((string) $agenda->time_slot)
        : ($dateObj ? $dateObj->format('H:i') . ' WIB' : null);

    $tempatDetail = filled($agenda->venue ?? null)
        ? trim((string) $agenda->venue)
        : (filled($agenda->location ?? null) ? trim((string) $agenda->location) : null);

    $penyelenggaraDetail = filled($agenda->organizer ?? null)
        ? trim((string) $agenda->organizer)
        : null;

    $pesertaDetail = filled($agenda->participants ?? null)
        ? trim((string) $agenda->participants)
        : null;

    $rawExternalUrl = trim((string) ($agenda->external_url ?? ''));
    $safeExternalUrl = ($rawExternalUrl !== '' && preg_match('#^https?://#i', $rawExternalUrl) && filter_var($rawExternalUrl, FILTER_VALIDATE_URL))
        ? $rawExternalUrl
        : null;

    $rawDescription = (string) ($agenda->description ?? '');
    $hasHtmlTags = $rawDescription !== strip_tags($rawDescription);
    $plainDescForMeta = \Illuminate\Support\Str::limit(trim(strip_tags($agenda->meta_description ?? $agenda->description ?? $agenda->summary ?? $agenda->excerpt ?? '')), 160);

    $startIso = null;
    $endIso = null;
    $gcalStart = null;
    $gcalEnd = null;

    if ($dateObj) {
        $startCal = $dateObj->copy();
        $endCal = $dateObj->copy()->addHours(2);

        if (filled($agenda->time_slot ?? null) && preg_match('/(\d{1,2})[:.](\d{2})\s*[-–]\s*(\d{1,2})[:.](\d{2})/', (string) $agenda->time_slot, $tm)) {
            $startCal = $startCal->setTime(min(23, max(0, (int) $tm[1])), min(59, max(0, (int) $tm[2])), 0);
            $endCal = $endCal->setTime(min(23, max(0, (int) $tm[3])), min(59, max(0, (int) $tm[4])), 0);
            if ($endCal->lessThanOrEqualTo($startCal)) {
                $endCal->addDay();
            }
        }

        $startIso = $startCal->toIso8601String();
        $endIso = $endCal->toIso8601String();
        $gcalStart = $startCal->copy()->timezone('UTC')->format('Ymd\THis\Z');
        $gcalEnd = $endCal->copy()->timezone('UTC')->format('Ymd\THis\Z');
    }

    $gcalDetailsParts = array_filter([
        $plainDescForMeta !== '' ? $plainDescForMeta : null,
        $jamDetail ? 'Jam: ' . $jamDetail : null,
        $penyelenggaraDetail ? 'Penyelenggara: ' . $penyelenggaraDetail : null,
        $pesertaDetail ? 'Peserta: ' . $pesertaDetail : null,
        $safeExternalUrl ? 'URL: ' . $safeExternalUrl : null,
    ]);

    $googleCalendarUrl = ($gcalStart && $gcalEnd)
        ? 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action' => 'TEMPLATE',
            'text' => (string) $agenda->title,
            'dates' => $gcalStart . '/' . $gcalEnd,
            'details' => implode("\n", $gcalDetailsParts),
            'location' => (string) ($tempatDetail ?? ''),
        ])
        : null;

    $icsDownloadUrl = $slugCurrent ? route('agenda.ics', $slugCurrent) : null;
    $shareUrl = request()->url();
    $shareText = trim((string) $agenda->title . ($dateObj ? ' — ' . $dateObj->translatedFormat('d M Y') : ''));
    $whatsappShareUrl = 'https://wa.me/?' . http_build_query(['text' => $shareText . ' ' . $shareUrl]);
    $telegramShareUrl = 'https://t.me/share/url?' . http_build_query(['url' => $shareUrl, 'text' => $shareText]);

    $eventSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => (string) $agenda->title,
        'description' => $plainDescForMeta,
        'url' => $shareUrl,
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus' => $isPast ? 'https://schema.org/EventCompleted' : 'https://schema.org/EventScheduled',
    ];
    if ($startIso) {
        $eventSchema['startDate'] = $startIso;
    }
    if ($endIso) {
        $eventSchema['endDate'] = $endIso;
    }
    if ($tempatDetail) {
        $eventSchema['location'] = [
            '@type' => 'Place',
            'name' => $tempatDetail,
        ];
    }
    if ($penyelenggaraDetail) {
        $eventSchema['organizer'] = [
            '@type' => 'Organization',
            'name' => $penyelenggaraDetail,
        ];
    }
@endphp

@push('head')
    <meta name="description" content="{{ $plainDescForMeta }}">

    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:title" content="{{ $agenda->meta_title ?? \Illuminate\Support\Str::limit($agenda->title ?? $agenda->name ?? config('app.name'), 70) }}">
    <meta property="og:description" content="{{ $plainDescForMeta }}">
    @if($agenda->hasImage('cover'))
        <meta property="og:image" content="{{ $agenda->image('cover', 'default') ?? $agenda->image('thumbnail', 'default') ?? '' }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $agenda->meta_title ?? \Illuminate\Support\Str::limit($agenda->title ?? $agenda->name ?? config('app.name'), 70) }}">
    <meta name="twitter:description" content="{{ $plainDescForMeta }}">

    <script type="application/ld+json">{!! json_encode($eventSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('meta_title',
    $agenda->meta_title
        ? $agenda->meta_title
        : \Illuminate\Support\Str::limit($agenda->title . ' | ' . config('app.name'), 70)
)

@section('meta_description',
    $agenda->meta_description
        ? $agenda->meta_description
        : \Illuminate\Support\Str::limit(strip_tags($agenda->description ?? ''), 160)
)

@if($agenda->hasImage('og_image', 'default'))
    @section('og_image', $agenda->image('og_image', 'default'))
@endif

@section('content')
<main class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto relative overflow-hidden">

    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-96 h-96 bg-[#acf7f0]/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 space-y-6">

        @include('site.partials.breadcrumb', ['items' => [
            ['label' => 'Agenda', 'url' => url('/#agenda')],
            ['label' => $agenda->title, 'url' => null],
        ]])

        <div class="space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                @if($dateObj)
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-900 text-xs font-semibold shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $dateObj->translatedFormat('l, d F Y') }}
                    </div>
                @endif

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $isPast ? 'bg-slate-100 border-slate-200 text-slate-600' : 'bg-[#acf7f0]/30 border-[#f0acf7] text-slate-900' }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                {{ $agenda->title }}
            </h1>
        </div>

        <!-- Add to Calendar + Share & Copy Link -->
        <div
            x-data="{
                copied: false,
                async copyLink() {
                    try {
                        await navigator.clipboard.writeText('{{ $shareUrl }}');
                        this.copied = true;
                        setTimeout(() => { this.copied = false; }, 2200);
                    } catch (e) {
                        const tmp = document.createElement('input');
                        tmp.value = '{{ $shareUrl }}';
                        document.body.appendChild(tmp);
                        tmp.select();
                        document.execCommand('copy');
                        document.body.removeChild(tmp);
                        this.copied = true;
                        setTimeout(() => { this.copied = false; }, 2200);
                    }
                }
            }"
            class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-3"
        >
            <div class="flex flex-wrap items-center gap-2">
                @if($icsDownloadUrl)
                    <a
                        href="{{ $icsDownloadUrl }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 text-xs font-bold shadow-sm transition-colors"
                    >
                        <span>Unduh Kalender (.ics)</span>
                    </a>
                @endif

                @if($googleCalendarUrl)
                    <a
                        href="{{ $googleCalendarUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold transition-colors"
                    >
                        <span>Google Calendar</span>
                    </a>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ $whatsappShareUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#acf7f0]/30 hover:bg-[#acf7f0] text-slate-900 border border-[#f0acf7] text-xs font-semibold transition-colors"
                >
                    <span>WhatsApp</span>
                </a>
                <a
                    href="{{ $telegramShareUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-xs font-semibold transition-colors"
                >
                    <span>Telegram</span>
                </a>
                <button
                    type="button"
                    @click="copyLink()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold transition-colors"
                >
                    <span x-show="!copied">Salin Tautan</span>
                    <span x-show="copied" x-cloak class="text-slate-900 font-bold">Tersalin!</span>
                </button>
            </div>
        </div>

        <!-- Informasi Lengkap Agenda: Jam, Tempat, Penyelenggara, Peserta, URL -->
        @if($jamDetail || $tempatDetail || $penyelenggaraDetail || $pesertaDetail || $safeExternalUrl)
            <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-6 shadow-sm">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @if($jamDetail)
                        <div class="space-y-1">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Jam</dt>
                            <dd class="font-semibold text-slate-800">{{ $jamDetail }}</dd>
                        </div>
                    @endif

                    @if($tempatDetail)
                        <div class="space-y-1">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Tempat</dt>
                            <dd class="font-semibold text-slate-800">{{ $tempatDetail }}</dd>
                        </div>
                    @endif

                    @if($penyelenggaraDetail)
                        <div class="space-y-1">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Penyelenggara</dt>
                            <dd class="font-semibold text-slate-800">{{ $penyelenggaraDetail }}</dd>
                        </div>
                    @endif

                    @if($pesertaDetail)
                        <div class="space-y-1">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Peserta</dt>
                            <dd class="font-semibold text-slate-800">{{ $pesertaDetail }}</dd>
                        </div>
                    @endif

                    @if($safeExternalUrl)
                        <div class="sm:col-span-2 space-y-1 pt-1 border-t border-slate-100">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">URL</dt>
                            <dd>
                                <a
                                    href="{{ $safeExternalUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 font-semibold text-slate-900 hover:text-slate-950 underline underline-offset-4 break-all"
                                >
                                    <span>{{ $safeExternalUrl }}</span>
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        @if($agenda->hasImage('cover'))
            <div class="rounded-3xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100 aspect-[16/9]">
                <img src="{{ $agenda->image('cover', 'default') }}" alt="{{ $agenda->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        @if(filled(trim($rawDescription)))
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 backdrop-blur-xl shadow-sm space-y-4 text-slate-600 leading-relaxed">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Deskripsi</div>
                @if($hasHtmlTags)
                    <div class="prose max-w-none text-slate-600 [&_strong]:text-slate-900">
                        {!! safe_html($rawDescription) !!}
                    </div>
                @else
                    <div class="text-slate-700 whitespace-pre-line leading-relaxed">{{ $rawDescription }}</div>
                @endif
            </div>
        @endif

        @if($agenda->blocks->count() > 0)
            <div class="space-y-8">
                {!! $agenda->renderBlocks() !!}
            </div>
        @endif

        @php
            $nextSlug = isset($nextAgenda) ? ($nextAgenda->slug ?? optional($nextAgenda->slugs->first())->slug) : null;
        @endphp

        <div class="pt-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:border-[#f0acf7] text-xs font-bold text-slate-700 hover:text-slate-950 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Halaman Utama</span>
            </a>

            @if(isset($nextAgenda) && $nextSlug)
                <a href="{{ route('agenda.show', $nextSlug) }}" class="group inline-flex items-center gap-3 px-5 py-3 rounded-2xl bg-white border border-slate-200 hover:border-[#f0acf7] hover:bg-slate-50 text-slate-700 transition-all duration-300 shadow-sm">
                    <div class="text-right">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Agenda Selanjutnya</span>
                        <span class="block text-sm font-bold text-slate-900 group-hover:text-slate-950 transition-colors max-w-xs truncate">{{ $nextAgenda->title }}</span>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#acf7f0]/30 border border-[#f0acf7] group-hover:border-[#f0acf7] group-hover:bg-[#acf7f0] flex items-center justify-center text-slate-900 shrink-0 transition-all duration-300 shadow-sm">
                        <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            @endif
        </div>

    </div>
</main>
@endsection
