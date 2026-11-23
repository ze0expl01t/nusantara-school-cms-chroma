@php
    /* ═══════════════════════════════════════════════════════════
       HELPER: Ambil field block dengan fallback locale
       ═══════════════════════════════════════════════════════════ */
    $safeInput = function ($block, $field, $default = null) {
        $value = $block->input($field);
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? $default;
        }
        return $value ?: $default;
    };

    /* ═══════════════════════════════════════════════════════════
       BASIC FIELDS
       ═══════════════════════════════════════════════════════════ */
    $title    = $safeInput($block, 'title', 'Hubungi Kami');
    $subtitle = $safeInput($block, 'subtitle', '');
    $address  = $safeInput($block, 'address', '');
    $phone    = $safeInput($block, 'phone', '');
    $email    = $safeInput($block, 'email', '');

    /* ═══════════════════════════════════════════════════════════
       GOOGLE MAPS URL — Normalisasi & Validasi
       ═══════════════════════════════════════════════════════════ */
    $defaultMap = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.3431636402493!2d111.4030487!3d-6.968781700000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7740dfd0ebfe17%3A0x32e6044ecb0e7b72!2sVocational%20High%20School%20Muhammadiyah%201%20Blora!5e0!3m2!1sen!2sid!4v1791028319214!5m2!1sen!2sid';

    $mapUrl = $safeInput($block, 'map_embed_url', $defaultMap);

    // 1. Decode HTML entities (&quot; → ", &amp; → &, dll)
    if (is_string($mapUrl)) {
        $mapUrl = html_entity_decode($mapUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // 2. Kalau admin paste kode <iframe> lengkap, extract src-nya saja
    if (is_string($mapUrl) && str_contains($mapUrl, '<iframe')) {
        if (preg_match('/src=["\']([^"\']+)["\']/i', $mapUrl, $m)) {
            $mapUrl = $m[1];
        }
    }

    // 3. Bersihkan karakter sisa (whitespace, quote, newline)
    if (is_string($mapUrl)) {
        $mapUrl = trim($mapUrl, " \t\n\r\0\x0B\"'");
    }

    // 4. Tambahkan https:// kalau tidak ada protokol
    if (is_string($mapUrl) && $mapUrl !== ''
        && !str_starts_with($mapUrl, 'http://')
        && !str_starts_with($mapUrl, 'https://')
        && !str_starts_with($mapUrl, '//')
    ) {
        $mapUrl = 'https://' . ltrim($mapUrl, '/');
    }

    // 5. Validasi domain: hanya Google Maps yang diizinkan
    $isValidMap = is_string($mapUrl)
        && $mapUrl !== ''
        && preg_match('#^https://(www\.|maps\.)?google\.(com|co\.id)/maps#i', $mapUrl);

    // 6. Fallback kalau tidak valid
    if (!$isValidMap) {
        $mapUrl = $defaultMap;
        $isValidMap = true;
    }

    /* ═══════════════════════════════════════════════════════════
       PHONE & WHATSAPP NORMALIZATION
       ═══════════════════════════════════════════════════════════ */
    $phoneClean = preg_replace('/[^0-9+]/', '', $phone);
    $waNumber = preg_replace('/[^0-9]/', '', $phone);

    // Konversi awalan 0 → 62 (untuk WhatsApp internasional)
    if ($waNumber && str_starts_with($waNumber, '0')) {
        $waNumber = '62' . substr($waNumber, 1);
    }

    /* ═══════════════════════════════════════════════════════════
       CTA BUTTONS
       ═══════════════════════════════════════════════════════════ */
    $defaultWaUrl = $waNumber
        ? 'https://wa.me/' . $waNumber . '?text=' . urlencode('Halo, saya ingin bertanya tentang pendaftaran.')
        : null;

    $ctaButtons = [
        [
            'text' => $safeInput($block, 'cta_1_text', 'Tanya Pendaftaran'),
            'url'  => $safeInput($block, 'cta_1_url', $defaultWaUrl),
            'icon' => 'chat',
        ],
        [
            'text' => $safeInput($block, 'cta_2_text', 'Info Jurusan'),
            'url'  => $safeInput($block, 'cta_2_url', '#jurusan'),
            'icon' => 'academic',
        ],
        [
            'text' => $safeInput($block, 'cta_3_text', 'Info Biaya'),
            'url'  => $safeInput($block, 'cta_3_url', '#biaya'),
            'icon' => 'money',
        ],
    ];

    // Filter: text & url tidak kosong, dan URL aman (http/https/mailto/tel/anchor)
    $ctaButtons = array_values(array_filter($ctaButtons, function ($cta) {
        if (empty($cta['text']) || empty($cta['url'])) {
            return false;
        }
        return (bool) preg_match('/^(https?:|mailto:|tel:|#)/i', $cta['url']);
    }));
@endphp

{{-- ═══════════════════════════════════════════════════════════
     SECTION: CONTACT
     ═══════════════════════════════════════════════════════════ --}}
<section
    id="contact"
    class="scroll-mt-24 py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden"
 data-block-reveal>
    {{-- Ambient Glow --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[150px] rounded-full pointer-events-none"></div>

    <div class="relative z-10">
        <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl overflow-hidden shadow-sm grid grid-cols-1 lg:grid-cols-2 items-stretch">

            {{-- ═══ Kolom Kiri: Informasi Kontak ═══ --}}
            <div class="p-6 sm:p-8 lg:p-10 bg-gradient-to-br from-[#f7f0ac]/35 via-white to-slate-50 flex flex-col justify-between gap-6 sm:gap-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-48 h-48 sm:w-64 sm:h-64 bg-[#acf7f0]/10 rounded-full blur-3xl pointer-events-none"></div>

                {{-- Header: Title + Subtitle --}}
                <div class="space-y-3 relative z-10">
                    @if($title)
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ $title }}
                        </h2>
                    @endif

                    @if($subtitle)
                        <div class="text-sm sm:text-base text-slate-600 leading-relaxed prose prose-sm max-w-none prose-p:my-0 prose-p:leading-relaxed">
                            @if(function_exists('safe_html'))
                                {!! safe_html($subtitle) !!}
                            @else
                                {!! strip_tags($subtitle, '<p><br><strong><b><em><i>') !!}
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Detail Kontak --}}
                <div class="space-y-5 text-sm text-slate-700 relative z-10">

                    {{-- Head Office --}}
                    @if($address)
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div class="w-10 h-10 rounded-xl bg-[#acf7f0]/30 border border-[#f0acf7] text-slate-900 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Head Office</h3>
                                <p class="text-slate-600 mt-0.5 leading-snug text-xs sm:text-sm">
                                    {!! nl2br(e($address)) !!}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Direct Line --}}
                    @if($phone)
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div class="w-10 h-10 rounded-xl bg-[#acf7f0]/30 border border-[#f0acf7] text-slate-900 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Direct Line</h3>
                                <a
                                    href="tel:{{ $phoneClean }}"
                                    class="text-slate-600 mt-0.5 hover:text-slate-950 transition-colors duration-200 inline-block break-all text-xs sm:text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 rounded"
                                >
                                    {{ $phone }}
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Support Desk --}}
                    @if($email)
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div class="w-10 h-10 rounded-xl bg-[#acf7f0]/30 border border-[#f0acf7] text-slate-900 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Support Desk</h3>
                                <a
                                    href="mailto:{{ $email }}"
                                    class="text-slate-600 mt-0.5 hover:text-slate-950 transition-colors duration-200 inline-block break-all text-xs sm:text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 rounded"
                                >
                                    {{ $email }}
                                </a>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- CTA Buttons — Grid 3 kolom --}}
                @if(!empty($ctaButtons))
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 relative z-10 pt-2">
                        @foreach($ctaButtons as $cta)
                            <a
                                href="{{ $cta['url'] }}"
                                @if(str_starts_with($cta['url'], 'http')) target="_blank" rel="noopener noreferrer" @endif
                                data-analytics="contact-cta"
                                data-cta-label="{{ $cta['text'] }}"
                                class="group inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-3 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold text-xs sm:text-sm shadow-lg shadow-slate-950/15 transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-2 motion-reduce:transition-none motion-reduce:hover:translate-y-0"
                            >
                                @switch($cta['icon'])
                                    @case('chat')
                                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        @break
                                    @case('academic')
                                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                        </svg>
                                        @break
                                    @case('money')
                                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        @break
                                    @default
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                @endswitch

                                <span class="truncate">{{ $cta['text'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ═══ Kolom Kanan: Google Maps Embed ═══ --}}
            <div class="relative min-h-[280px] sm:min-h-[340px] lg:min-h-[460px] bg-slate-100">
                @if($isValidMap)
                    <iframe
                        src="{{ $mapUrl }}"
                        title="Peta lokasi kantor"
                        aria-label="Peta lokasi kantor"
                        class="absolute inset-0 w-full h-full border-0"
                        allow="fullscreen"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                @else
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 gap-2">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <p class="text-xs font-medium">Peta tidak tersedia</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>
