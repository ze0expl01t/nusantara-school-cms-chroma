@php
    use A17\Twill\Facades\TwillAppSettings;

    // ===== Ambil setting group 'general' =====
    $settingGeneral = \App\Models\AppSetting::where('name', 'general')->first();

    $settingBlock = null;
    if ($settingGeneral) {
        $settingBlock = \DB::table('twill_blocks')
            ->where('blockable_type', 'A17\Twill\Models\AppSetting')
            ->where('blockable_id', $settingGeneral->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    // ===== HELPER: Ambil field translatable =====
    $s = function ($key, $default = null) use ($settingGeneral) {
        if ($settingGeneral) {
            try {
                $val = $settingGeneral->{$key} ?? null;

                if (is_array($val)) {
                    $locale   = app()->getLocale();
                    $fallback = config('app.fallback_locale', 'en');

                    $picked = $val[$locale]
                        ?? $val[$fallback]
                        ?? (count($val) ? reset($val) : null);

                    if ($picked !== null && $picked !== '') {
                        return $picked;
                    }
                } elseif ($val !== null && $val !== '') {
                    return $val;
                }
            } catch (\Throwable $e) {
                // lanjut
            }
        }

        try {
            $val = TwillAppSettings::get("general.general.$key");
        } catch (\Throwable $e) {
            $val = null;
        }

        if (is_array($val)) {
            $locale   = app()->getLocale();
            $fallback = config('app.fallback_locale', 'en');

            $picked = $val[$locale]
                ?? $val[$fallback]
                ?? (count($val) ? reset($val) : null);

            return ($picked !== null && $picked !== '') ? $picked : $default;
        }

        return filled($val) ? $val : $default;
    };

    // ===== HELPER: Sanitize URL =====
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

        return 'https://' . $url;
    };

    // ===== HELPER: Ambil URL media =====
    $media = function ($role) use ($settingBlock) {
        if (!$settingBlock) return null;

        $row = \DB::table('twill_mediables')
            ->join('twill_medias', 'twill_medias.id', '=', 'twill_mediables.media_id')
            ->where('twill_mediables.mediable_type', 'blocks')
            ->where('twill_mediables.mediable_id', $settingBlock->id)
            ->where('twill_mediables.role', $role)
            ->whereNull('twill_medias.deleted_at')
            ->orderBy('twill_mediables.id', 'desc')
            ->select('twill_medias.uuid', 'twill_medias.filename')
            ->first();

        if (!$row || empty($row->uuid)) return null;
        return asset('storage/uploads/' . ltrim($row->uuid, '/'));
    };

    // ===== HELPER: Ambil URL file =====
    $file = function ($role) use ($settingBlock) {
        if (!$settingBlock) return null;

        $row = \DB::table('twill_fileables')
            ->join('twill_files', 'twill_files.id', '=', 'twill_fileables.file_id')
            ->where('twill_fileables.fileable_type', 'blocks')
            ->where('twill_fileables.fileable_id', $settingBlock->id)
            ->where('twill_fileables.role', $role)
            ->whereNull('twill_files.deleted_at')
            ->orderBy('twill_fileables.id', 'desc')
            ->select('twill_files.uuid', 'twill_files.filename')
            ->first();

        if (!$row || empty($row->uuid)) return null;
        return asset('storage/uploads/' . ltrim($row->uuid, '/'));
    };

    // ===== IDENTITAS SITUS =====
    $siteTitle       = $s('site_title', 'HSE SMKMUH1BLORA');
    $siteTagline     = $s('site_tagline', 'Safety First • Health Always • Environment Matters');
    $metaDescription = $s('meta_description', 'Portal HSE & K3 PT. Nama Perusahaan — Safety First, Health Always, Environment Matters.');
    $metaKeywords    = $s('meta_keywords', 'HSE, K3, Safety, PT. Nama Perusahaan');

    // ===== ANALYTICS =====
    $googleAnalyticsId          = $s('google_analytics_id') ?: 'G-S0EZ5D88DM';
    $googleAnalyticsAnonymizeIp = (bool) $s('google_analytics_anonymize_ip', true);

    $googleTagManagerIdRaw = trim((string) $s('google_tag_manager_id'));
    $googleTagManagerId    = ($googleTagManagerIdRaw !== '' && preg_match('/^GTM-[A-Z0-9]+$/i', $googleTagManagerIdRaw))
        ? $googleTagManagerIdRaw
        : null;

    // ===== LOGO & MEDIA =====
    $logoUrl      = $media('logo');
    $logoWhiteUrl = $media('logo_white');
    $ogImageUrl   = $media('og_image');
    $faviconUrl   = $file('favicon_file');

    // ===== CTA HEADER =====
    $ctaText = $s('cta_text', 'Hubungi Kami');
    $ctaUrl  = $safeUrl($s('cta_url'), url('/kontak'));

    // ===== KONTAK =====
    $contactEmail   = $s('contact_email', 'hse@smkmuh1blora.sch.id');
    $contactPhone   = $s('contact_phone', '+62 858-9206-3082');
    $contactAddress = $s('contact_address', 'Jl. Gatot Subroto Km.1, Blora, Jawa Tengah, Indonesia');
    $googleMapsUrl  = $safeUrl($s('google_maps_url'), null);

    // ===== SOSIAL MEDIA =====
    $socials = [
        'twitter'   => $safeUrl($s('twitter_url'), null),
        'linkedin'  => $safeUrl($s('linkedin_url'), null),
        'youtube'   => $safeUrl($s('youtube_url'), null),
        'instagram' => $safeUrl($s('instagram_url'), null),
        'facebook'  => $safeUrl($s('facebook_url'), null),
        'telegram'  => $safeUrl($s('telegram_url'), null),
        'whatsapp'  => $safeUrl($s('whatsapp_url'), null),
    ];

    // ===== FOOTER TEXT =====
    $footerIntro    = $s('footer_intro', 'Kami menyediakan akses ke');
    $footerWordsRaw = $s('footer_words', 'Kebijakan Akses.,Materi Pelatihan.,Penilaian Risiko.,Informasi K3.');
    $footerWordsRaw = is_string($footerWordsRaw) ? $footerWordsRaw : '';

    $footerWordsArray = array_values(array_filter(array_map('trim', explode(',', $footerWordsRaw))));
    if (empty($footerWordsArray)) {
        $footerWordsArray = ['Informasi K3.'];
    }

    $quickLinksLabel  = $s('quick_links_label', 'Quick Links');
    $contactUsLabel   = $s('contact_us_label', 'Contact Us');
    $stayUpdatedLabel = $s('stay_updated_label', 'Stay Updated');

    $newsletterText        = $s('newsletter_text', 'Berlangganan newsletter untuk informasi terbaru.');
    $newsletterPlaceholder = $s('newsletter_placeholder', 'Masukkan email Anda');
    $newsletterButton      = $s('newsletter_button', 'Subscribe');

    $footerCopyright = $s('footer_copyright', 'All rights reserved.');
    $vendorLabel     = $s('vendor_label', 'Powered By');
    $vendorName      = $s('vendor_name', 'Arkan Intermedia');
    $vendorUrl       = $safeUrl($s('vendor_url'), '#');

    // ===== HEADER ORG =====
    $headerOrgName     = $s('header_org_name', $siteTitle);
    $headerOrgSubtitle = $s('header_org_subtitle', 'PT. Nama Perusahaan');

    // ===== FOOTER ORG =====
    $footerOrgName     = $s('footer_org_name', $siteTitle);
    $footerOrgSubtitle = $s('footer_org_subtitle', 'PT. Nama Perusahaan');

    // ===== WHATSAPP NUMBER =====
    $waNumber = preg_replace('/[^0-9]/', '', (string) $contactPhone);

    // ===== MENU ITEMS (NESTED) =====
    $menuItems = collect();
    if (class_exists(\App\Models\MenuLink::class)) {
        try {
            $menuItems = \App\Models\MenuLink::published()
                ->whereNull('parent_id')
                ->orderBy('position')
                ->with([
                    'children' => fn ($q) => $q->published()->orderBy('position'),
                ])
                ->get();
        } catch (\Throwable $e) {
            $menuItems = collect();
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // QUICK LINKS — LIMIT 5 ITEMS
    // ═══════════════════════════════════════════════════════════════
    $quickLinks = $menuItems->take(5);

    // ===== HELPER: Resolve URL dari MenuLink =====
    $resolveMenuUrl = function ($link) use ($safeUrl) {
        if (!$link) return '#';

        $pageRelation = method_exists($link, 'getRelated')
            ? $link->getRelated('page')->first()
            : null;

        if ($pageRelation && !empty($pageRelation->slug)) {
            try {
                return route('frontend.page', [$pageRelation->slug]);
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return $safeUrl($link->url ?? '#');
    };

    // ===== CURRENT URL =====
    $currentUrl = url()->current();

    // ===== FLAG: admin request =====
    $adminPath = trim(config('twill.admin_app_path', 'admin'), '/');
    $isAdminRequest = request()->is($adminPath) || request()->is($adminPath . '/*')
        || request()->is('admin*') || request()->is('twill*') || request()->is('panel-maintenance*');

    // ===== SEO =====
    $pageTitle  = trim($__env->yieldContent('meta_title'));
    $finalTitle = $pageTitle !== '' ? $pageTitle : ($siteTitle . ' | ' . $siteTagline);
    $ogTitle    = $pageTitle !== '' ? $pageTitle : $siteTitle;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    {{-- viewport-fit=cover untuk iPhone notch / Dynamic Island --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="{{ $isAdminRequest ? 'noindex, nofollow' : 'index, follow' }}">

    {{-- color-scheme: cegah flash warna saat dark-mode OS --}}
    <meta name="color-scheme" content="light">

    {{-- theme-color: warna address bar browser mobile --}}
    <meta name="theme-color" content="#0f172a">

    <link rel="canonical" href="{{ $currentUrl }}">

    <title>{{ $finalTitle }}</title>
    <meta name="description" content="@yield('meta_description', $metaDescription)">

    @if($metaKeywords)
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif

    {{-- ═══ RESOURCE HINTS — paralel DNS + early connection ═══ --}}
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Google Analytics / GTM (kalau aktif, non-admin) --}}
    @if(!$isAdminRequest && ($googleAnalyticsId || $googleTagManagerId))
        <link rel="preconnect" href="https://www.googletagmanager.com">
        <link rel="dns-prefetch" href="https://www.google-analytics.com">
    @endif

    {{-- YouTube thumbnails (dari video_gallery block) --}}
    <link rel="dns-prefetch" href="https://i.ytimg.com">

    {{-- OPEN GRAPH --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="@yield('meta_description', $metaDescription)">
    <meta property="og:site_name" content="{{ $siteTitle }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @elseif($ogImageUrl)
        <meta property="og:image" content="{{ $ogImageUrl }}">
    @endif

    {{-- TWITTER CARD --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="@yield('meta_description', $metaDescription)">
    @hasSection('og_image')
        <meta name="twitter:image" content="@yield('og_image')">
    @elseif($ogImageUrl)
        <meta name="twitter:image" content="{{ $ogImageUrl }}">
    @endif

    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif

    {{-- GOOGLE ANALYTICS --}}
    @if(!$isAdminRequest)

        @if($googleTagManagerId)
            <script data-cfasync="false">
                (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,'script','dataLayer','{{ $googleTagManagerId }}');
            </script>
        @endif

        @if($googleAnalyticsId && !$googleTagManagerId)
            <script async data-cfasync="false" src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsId }}"></script>
            <script data-cfasync="false">
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());

                gtag('config', '{{ $googleAnalyticsId }}', {
                    @if($googleAnalyticsAnonymizeIp)
                        'anonymize_ip': true,
                    @endif
                    'cookie_flags': 'SameSite=None;Secure',
                });

                document.addEventListener('click', function (e) {
                    const el = e.target.closest('[data-analytics]');
                    if (el && typeof gtag === 'function') {
                        gtag('event', 'click', {
                            'event_category': 'CTA',
                            'event_label': el.getAttribute('data-analytics')
                        });
                    }
                }, { passive: true });
            </script>
        @endif

    @endif

    {{-- GOOGLE FONTS — preload untuk render lebih awal --}}
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- VITE ASSETS (Tailwind + Alpine) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    {{-- ═══════════════════════════════════════════════════════════════
         CRITICAL: @stack('head')
         Semua @push('head') dari block (hero, video_gallery, dll)
         akan dirender di sini. TANPA ini, resource hints (preconnect,
         preload LCP image) dari block TIDAK akan muncul.
         ═══════════════════════════════════════════════════════════════ --}}
    @stack('head')
@php
    $schoolSchema = [
        "@context" => "https://schema.org",
        "@type" => "School",
        "name" => $siteTitle,
        "description" => $metaDescription,
        "url" => url("/"),
    ];
    if (!empty($contactPhone)) {
        $schoolSchema["telephone"] = $contactPhone;
    }
    if (!empty($contactEmail)) {
        $schoolSchema["email"] = $contactEmail;
    }
    if (!empty($contactAddress)) {
        $schoolSchema["address"] = [
            "@type" => "PostalAddress",
            "streetAddress" => $contactAddress,
        ];
    }
@endphp
@if(!$isAdminRequest)
    <script type="application/ld+json">{!! json_encode($schoolSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
</head>
<body
    class="bg-slate-50 text-slate-800 antialiased selection:bg-[#f0acf7] selection:text-white flex flex-col min-h-screen [text-rendering:optimizeLegibility]"
    x-data="{ pageLoading: true }"
    @beforeunload.window="pageLoading = true"
    @pageshow.window="pageLoading = false"
    x-init="
        if (document.readyState === 'complete') {
            pageLoading = false;
        } else {
            window.addEventListener('load', () => { pageLoading = false; }, { once: true });
        }

        document.addEventListener('click', (e) => {
            let el = e.target.closest('a');

            if (
                el &&
                el.href &&
                !el.getAttribute('target') &&
                !el.hasAttribute('download') &&
                el.href.startsWith(window.location.origin) &&
                !el.getAttribute('href').startsWith('#')
            ) {
                pageLoading = true;
            }
        }, { passive: true });
    "
>

    @if(!$isAdminRequest && $googleTagManagerId)
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id={{ $googleTagManagerId }}"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
    @endif

    {{-- PAGE LOADING --}}
    <div class="fixed top-0 left-0 right-0 z-[100] h-1 bg-transparent overflow-hidden" x-show="pageLoading" x-cloak>
        <div class="h-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7] shadow-[0_0_10px_rgba(240,172,247,0.75)] loading-bar-animation"></div>
    </div>

    <!-- ================== HEADER ================== -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-slate-950/95 backdrop-blur-md border-b border-slate-800/80 shadow-sm transition-all duration-300"
            x-data="{ mobileMenu: false, searchOpen: false }"
            @keydown.escape.window="mobileMenu = false; searchOpen = false">

        <div class="absolute bottom-0 left-0 right-0 h-1 flex z-20 opacity-90" aria-hidden="true">
            <div class="w-1/3 h-full bg-[#f0acf7]"></div>
            <div class="w-1/3 h-full bg-[#acf7f0]"></div>
            <div class="w-1/3 h-full bg-[#f7f0ac]"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">

            {{-- LOGO --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 group flex-shrink-0">
                @if($logoWhiteUrl || $logoUrl)
                    <img
                        src="{{ $logoWhiteUrl ?? $logoUrl }}"
                        alt="{{ $siteTitle }}"
                        width="80"
                        height="40"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        class="h-10 w-auto max-w-[60px] sm:max-w-[80px] object-contain flex-shrink-0 group-hover:scale-105 transition-transform motion-reduce:transition-none motion-reduce:group-hover:scale-100"
                     onerror="this.onerror=null;this.style.display='none';">
                    <div class="flex flex-col leading-tight min-w-0">
                        <span class="text-sm sm:text-base md:text-lg font-extrabold tracking-tight text-white leading-none truncate max-w-[140px] sm:max-w-[220px] md:max-w-[300px]">
                            {{ $headerOrgName }}
                        </span>
                        @if($headerOrgSubtitle)
                            <span class="hidden sm:block text-[10px] tracking-widest text-slate-400 uppercase font-semibold mt-1 truncate max-w-[180px] sm:max-w-[220px] md:max-w-[300px]">
                                {{ $headerOrgSubtitle }}
                            </span>
                        @endif
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#f7f0ac] to-[#f0acf7] flex items-center justify-center text-white shadow-lg shadow-slate-950/15 group-hover:scale-105 transition-transform motion-reduce:transition-none motion-reduce:group-hover:scale-100 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="flex flex-col leading-tight min-w-0">
                        <span class="text-sm sm:text-base md:text-lg font-extrabold tracking-tight text-white leading-none truncate max-w-[140px] sm:max-w-[220px] md:max-w-[300px]">
                            {{ $headerOrgName }}
                        </span>
                        @if($headerOrgSubtitle)
                            <span class="hidden sm:block text-[10px] tracking-widest text-slate-400 uppercase font-semibold mt-1 truncate max-w-[180px] sm:max-w-[220px] md:max-w-[300px]">
                                {{ $headerOrgSubtitle }}
                            </span>
                        @endif
                    </div>
                @endif
            </a>

            <!-- DESKTOP MENU (Nested Dropdown) -->
            <style>
    /* ── DESKTOP MENU CSS (BULLETPROOF — Pure CSS Hover + :has()) ── */
    nav, .dmenu-root, .dmenu-item { overflow: visible !important; }
    .dmenu-root { display: flex; align-items: center; gap: 4px; list-style: none; padding: 0; margin: 0; }
    .dmenu-item { position: relative; list-style: none; margin: 0; }
    .dmenu-item > a {
        display: flex; align-items: center; gap: 6px;
        padding: 8px 14px; border-radius: 8px;
        font-size: 14px; font-weight: 500; letter-spacing: -0.01em;
        color: #cbd5e1; text-decoration: none; white-space: nowrap;
        transition: color 0.18s ease, background 0.18s ease;
        position: relative;
    }
    .dmenu-item > a:hover { color: #fff; background: rgba(52, 211, 153, 0.08); }
    .dmenu-icon {
        width: 11px; height: 11px; opacity: 0.5; flex-shrink: 0;
        transition: transform 0.25s ease, opacity 0.18s ease, color 0.18s ease;
    }
    .dmenu-item > a:hover .dmenu-icon { opacity: 1; color: #34d399; }
    .dmenu-root > .dmenu-item > a > .dmenu-icon { transform: rotate(0deg); }
    .dmenu-root > .dmenu-item:hover > a > .dmenu-icon { transform: rotate(180deg); }
    .dmenu-submenu .dmenu-icon { transform: rotate(-90deg); }
    .dmenu-submenu .dmenu-item:hover > a > .dmenu-icon { transform: rotate(0deg); }

    /* Dropdown Base */
    .dmenu-submenu {
        display: none !important;
        position: absolute; top: 100%; left: 0;
        margin-top: 0; padding: 8px 6px 8px 0;
        min-width: 280px; width: max-content; max-width: 95vw;
        z-index: 99999; list-style: none;
        background: #0b1120;
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        box-shadow: 0 16px 48px -12px rgba(0, 0, 0, 0.6), 0 8px 16px -8px rgba(0, 0, 0, 0.4);
        overflow: visible !important;
    }
    .dmenu-item:hover > .dmenu-submenu {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Scroll hanya untuk submenu yang TIDAK punya child */
    .dmenu-submenu:not(:has(.dmenu-submenu)) {
        max-height: 70vh;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: thin;
        scrollbar-color: rgba(71, 85, 105, 0.8) transparent;
        -webkit-overflow-scrolling: touch;
    }
    .dmenu-submenu::-webkit-scrollbar { width: 6px; }
    .dmenu-submenu::-webkit-scrollbar-track { background: transparent; margin: 4px 0; }
    .dmenu-submenu::-webkit-scrollbar-thumb { background: rgba(71, 85, 105, 0.8); border-radius: 4px; }
    .dmenu-submenu::-webkit-scrollbar-thumb:hover { background: #64748b; }

    /* Level 3 Position */
    .dmenu-submenu .dmenu-submenu { top: -8px; left: 100%; margin-left: 4px; padding-top: 0; }

    /* Submenu Links */
    .dmenu-submenu .dmenu-item > a {
        border-radius: 0; padding: 9px 16px; color: #94a3b8;
        font-size: 13.5px; font-weight: 450; justify-content: space-between;
        transition: color 0.15s ease, background 0.15s ease;
    }
    .dmenu-submenu .dmenu-item > a:hover {
        background: rgba(172, 247, 240, 0.14); color: #f7f0ac;
    }

        .dmenu-root > .dmenu-item > a {
        position: relative;
        border-radius: 10px;
        transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
    }
    .dmenu-root > .dmenu-item > a:hover {
        background: linear-gradient(135deg, rgba(172, 247, 240, 0.22), rgba(240, 172, 247, 0.22));
        color: #f7f0ac;
    }
    .dmenu-root > .dmenu-item > a::before {
        content: '';
        position: absolute;
        bottom: 2px;
        left: 50%;
        width: 0;
        height: 2px;
        transform: translateX(-50%);
        background: linear-gradient(90deg, #f7f0ac, #acf7f0, #f0acf7);
        border-radius: 999px;
        transition: width 0.22s ease;
    }
    .dmenu-root > .dmenu-item > a:hover::before {
        width: 65%;
    }
    .dmenu-root > .dmenu-item > a:hover .dmenu-icon {
        color: #acf7f0;
        opacity: 1;
    }

    /* Active states — ROOT */
    .dmenu-root > .dmenu-active > a,
    .dmenu-root > .dmenu-parent-active > a {
        background: rgba(172, 247, 240, 0.14) !important;
        color: #f7f0ac !important;
        position: relative;
    }
    .dmenu-root > .dmenu-active > a { color: #acf7f0 !important; background: rgba(240, 172, 247, 0.16) !important; }
    .dmenu-root > .dmenu-active > a::after,
    .dmenu-root > .dmenu-parent-active > a::after {
        content: ''; position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%);
        width: 60%; height: 2px;
        background: linear-gradient(90deg, #f7f0ac, #acf7f0, #f0acf7);
        border-radius: 2px;
    }
    .dmenu-root > .dmenu-active > a .dmenu-icon,
    .dmenu-root > .dmenu-parent-active > a .dmenu-icon { color: #acf7f0; opacity: 1; }
    .dmenu-root > .dmenu-active > a:hover,
    .dmenu-root > .dmenu-parent-active > a:hover {
        background: rgba(172, 247, 240, 0.22) !important; color: #f7f0ac !important;
    }

    /* Active states — SUBMENU */
    .dmenu-submenu .dmenu-active > a {
        background: rgba(240, 172, 247, 0.18) !important;
        color: #f7f0ac !important; font-weight: 600;
        padding-left: 22px !important; position: relative;
    }
    .dmenu-submenu .dmenu-active > a::after,
    .dmenu-submenu .dmenu-parent-active > a::after {
        content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
        width: 80%; height: 2px;
        background: linear-gradient(90deg, #f7f0ac, #acf7f0, #f0acf7);
        border-radius: 2px;
    }
    .dmenu-submenu .dmenu-active > a::before {
        content: ''; position: absolute; left: 8px; top: 50%; transform: translateY(-50%);
        width: 4px; height: 4px; border-radius: 50%;
        background: #acf7f0; box-shadow: 0 0 8px rgba(172, 247, 240, 0.85);
    }
    .dmenu-submenu .dmenu-parent-active > a { color: #f7f0ac !important; font-weight: 500; }
</style>
<nav class="hidden md:flex items-center gap-1 bg-slate-800/80 p-1.5 rounded-xl border border-slate-700">
    @php
        $dmenuData = \App\Models\MenuLink::published()
            ->orderBy('_lft')
            ->get()
            ->toTree();

        $currentUrl = request()->url();
        $homeUrl = url('/');

        $isUrlActive = function($url) use ($currentUrl, $homeUrl) {
            if (empty($url) || $url === '#') return false;
            if ($url === $currentUrl) return true;
            if ($url !== $homeUrl && strlen($url) < strlen($currentUrl)) {
                if (strpos($currentUrl, rtrim($url, '/') . '/') === 0) return true;
            }
            return false;
        };

        $hasActiveDescendant = function($item) use (&$hasActiveDescendant, $isUrlActive) {
            if (!isset($item->children) || !$item->children) return false;
            foreach ($item->children as $child) {
                $childPage = method_exists($child, 'getRelated') ? $child->getRelated('page')->first() : null;
                $childUrl = ($childPage && !empty($childPage->slug)) ? route('frontend.page', [$childPage->slug]) : '#';
                if ($isUrlActive($childUrl)) return true;
                if ($hasActiveDescendant($child)) return true;
            }
            return false;
        };

        $dmenuRender = function($items) use (&$dmenuRender, $isUrlActive, $hasActiveDescendant) {
            if (empty($items)) return '';
            if (is_object($items) && method_exists($items, 'isEmpty') && $items->isEmpty()) return '';

            $html = '';
            foreach ($items as $item) {
                $pageRelation = method_exists($item, 'getRelated') ? $item->getRelated('page')->first() : null;
                $url = ($pageRelation && !empty($pageRelation->slug)) ? route('frontend.page', [$pageRelation->slug]) : '#';
                $hasChildren = isset($item->children) && $item->children && count($item->children) > 0;
                $title = e($item->title);
                $urlEsc = e($url);

                $isActive = $isUrlActive($url);
                $isParentOfActive = !$isActive && $hasActiveDescendant($item);

                $classes = ['dmenu-item'];
                if ($isActive) $classes[] = 'dmenu-active';
                if ($isParentOfActive) $classes[] = 'dmenu-parent-active';

                $html .= '<li class="' . implode(' ', $classes) . '">';
                $html .= '<a href="' . $urlEsc . '">';
                $html .= '<span>' . $title . '</span>';
                if ($hasChildren) {
                    $html .= '<svg class="dmenu-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';
                }
                $html .= '</a>';

                if ($hasChildren) {
                    $html .= '<ul class="dmenu-submenu">';
                    $html .= $dmenuRender($item->children);
                    $html .= '</ul>';
                }

                $html .= '</li>';
            }
            return $html;
        };
    @endphp

    @if($dmenuData->isNotEmpty())
        <ul class="dmenu-root">
            {!! $dmenuRender($dmenuData) !!}
        </ul>
    @endif
</nav>

            {{-- Desktop Actions --}}
            <div class="hidden md:flex items-center gap-3 flex-shrink-0">
                <button type="button"
                        @click="searchOpen = !searchOpen; $nextTick(() => { if(searchOpen) $refs.desktopSearchInput.focus(); })"
                        aria-label="Buka pencarian"
                        class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:text-slate-950 hover:bg-[#f7f0ac] hover:border-[#f0acf7] transition-[background-color,border-color,color] duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] motion-reduce:transition-none">
                    <svg x-show="!searchOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <svg x-show="searchOpen" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <a href="{{ $ctaUrl }}" class="px-5 py-2.5 rounded-full text-sm font-bold bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 shadow-lg shadow-slate-950/15 transition-[transform,background-color] duration-300 transform hover:-translate-y-0.5 motion-reduce:transition-none motion-reduce:hover:translate-y-0 [transform:translateZ(0)]">
                    {{ $ctaText }}
                </a>
            </div>

            {{-- Hamburger --}}
            <button @click="mobileMenu = !mobileMenu"
                    class="md:hidden p-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white focus:outline-none flex-shrink-0"
                    :aria-expanded="mobileMenu ? 'true' : 'false'"
                    aria-label="Toggle menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileMenu" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Desktop Search Dropdown --}}
        <div x-cloak
             x-show="searchOpen"
             x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             @click.outside="searchOpen = false"
             class="hidden md:block absolute top-full left-0 right-0 bg-slate-900/95 backdrop-blur-xl border-b border-slate-800 shadow-2xl">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                <form action="{{ route('search') }}" method="GET" class="max-w-2xl mx-auto">
                    <div class="relative bg-slate-800 border border-slate-700 rounded-2xl focus-within:border-[#acf7f0] transition">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="q"
                            x-ref="desktopSearchInput"
                            placeholder="Cari berita, fasilitas, ekstrakurikuler..."
                            maxlength="100"
                            class="w-full bg-transparent pl-12 pr-24 py-3.5 text-sm text-white placeholder-slate-500 focus:outline-none rounded-2xl"
                        >
                        <button type="submit"
                                class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-2 rounded-xl bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 text-xs font-bold transition motion-reduce:transition-none">
                            Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MOBILE MENU (Accordion) -->
        <div x-cloak
             x-show="mobileMenu"
             x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             @click.outside="mobileMenu = false"
             class="md:hidden bg-slate-900 border-b border-slate-800 px-4 sm:px-6 py-5 space-y-2 shadow-2xl max-h-[calc(100vh-5rem)] overflow-y-auto overscroll-contain">

            @if($menuItems->count() > 0)
                @foreach($menuItems as $link)
                    @php
                        $url = $resolveMenuUrl($link);
                        $hasChildren = $link->children && $link->children->count() > 0;

                        $isActive = request()->url() === $url;
                        if (!$isActive && $hasChildren) {
                            foreach ($link->children as $child) {
                                if (request()->url() === $resolveMenuUrl($child)) {
                                    $isActive = true;
                                    break;
                                }
                            }
                        }
                    @endphp

                    @if($hasChildren)
                        <div x-data="{ expanded: {{ $isActive ? 'true' : 'false' }} }"
                             class="rounded-xl overflow-hidden transition-[background-color] duration-200 motion-reduce:transition-none
                                    {{ $isActive ? 'bg-[#f0acf7]/10' : 'bg-transparent' }}">

                            <button type="button"
                                    @click="expanded = !expanded"
                                    :aria-expanded="expanded ? 'true' : 'false'"
                                    class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-semibold transition-[background-color,color] duration-200 motion-reduce:transition-none
                                           {{ $isActive
                                               ? 'text-[#f7f0ac]'
                                               : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                <span class="text-left flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full transition-[background-color] duration-200 motion-reduce:transition-none
                                                 {{ $isActive ? 'bg-[#acf7f0] shadow-[0_0_8px_rgba(52,211,153,0.9)]' : 'bg-slate-600' }}"
                                          aria-hidden="true"></span>
                                    {{ $link->title }}
                                </span>
                                <svg class="w-4 h-4 transform transition-transform duration-300 flex-shrink-0 motion-reduce:transition-none
                                            {{ $isActive ? 'text-[#acf7f0]' : 'text-slate-500' }}"
                                     :class="expanded ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="expanded"
                                 x-collapse
                                 x-cloak
                                 class="mt-1 mb-2 ml-4 pl-4 border-l-2 border-[#acf7f0]/20 space-y-1.5">

                                @foreach($link->children as $child)
                                    @php
                                        $childUrl = $resolveMenuUrl($child);
                                        $childActive = request()->url() === $childUrl;
                                    @endphp

                                    <a href="{{ $childUrl }}"
                                       class="group/sub relative flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-[background-color,color] duration-200 motion-reduce:transition-none
                                              {{ $childActive
                                                  ? 'bg-[#f0acf7]/15 text-[#f7f0ac]'
                                                  : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">

                                        <span class="w-1 h-1 rounded-full transition-[background-color] duration-200 flex-shrink-0 motion-reduce:transition-none
                                                     {{ $childActive
                                                         ? 'bg-[#acf7f0] shadow-[0_0_8px_rgba(52,211,153,0.9)]'
                                                         : 'bg-slate-600 group-hover/sub:bg-[#acf7f0]' }}"
                                              aria-hidden="true"></span>

                                        <span class="flex-1">{{ $child->title }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $url }}"
                           class="block w-full text-left px-4 py-3 rounded-xl font-semibold transition motion-reduce:transition-none
                                  {{ $isActive ? 'bg-[#f0acf7] text-slate-950 shadow-md shadow-slate-950/15' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                            {{ $link->title }}
                        </a>
                    @endif
                @endforeach
            @else
                <a href="{{ url('/') }}" class="block w-full text-left px-4 py-3 rounded-xl font-semibold text-slate-300 hover:bg-slate-800 transition motion-reduce:transition-none">
                    Home
                </a>
            @endif

            <div class="pt-4 border-t border-slate-800 space-y-2">
                <a href="{{ route('search') }}"
                   class="flex items-center gap-3 w-full px-4 py-3 rounded-xl font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition motion-reduce:transition-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </a>

                <a href="{{ $ctaUrl }}" class="block w-full text-center px-4 py-3 rounded-xl font-bold bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 transition shadow-md shadow-slate-950/15 motion-reduce:transition-none">
                    {{ $ctaText }}
                </a>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-grow pt-20">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="relative bg-slate-900 text-slate-300 mt-auto overflow-hidden border-t-0">
        <div class="absolute top-0 left-0 right-0 h-1 flex z-20 opacity-90" aria-hidden="true">
            <div class="w-1/3 h-full bg-[#f0acf7]"></div>
            <div class="w-1/3 h-full bg-[#acf7f0]"></div>
            <div class="w-1/3 h-full bg-[#f7f0ac]"></div>
        </div>

        @php
            $fgTop1  = random_int(5, 65);
            $fgLeft1 = random_int(5, 75);
            $fgSize1 = random_int(280, 460);

            $fgTop2  = random_int(15, 70);
            $fgLeft2 = random_int(15, 80);
            $fgSize2 = random_int(260, 440);

            $fgTop3  = random_int(10, 75);
            $fgLeft3 = random_int(10, 75);
            $fgSize3 = random_int(280, 450);
        @endphp
        {{-- Randomized Global Pastel Ambient Glows --}}
        <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden" aria-hidden="true">
            <div class="absolute rounded-full bg-[#f7f0ac]/15 blur-[95px]"
                 style="top: {{ $fgTop1 }}%; left: {{ $fgLeft1 }}%; width: {{ $fgSize1 }}px; height: {{ $fgSize1 }}px; transform: translate(-50%, -50%);"></div>
            <div class="absolute rounded-full bg-[#acf7f0]/15 blur-[95px]"
                 style="top: {{ $fgTop2 }}%; left: {{ $fgLeft2 }}%; width: {{ $fgSize2 }}px; height: {{ $fgSize2 }}px; transform: translate(-50%, -50%);"></div>
            <div class="absolute rounded-full bg-[#f0acf7]/15 blur-[95px]"
                 style="top: {{ $fgTop3 }}%; left: {{ $fgLeft3 }}%; width: {{ $fgSize3 }}px; height: {{ $fgSize3 }}px; transform: translate(-50%, -50%);"></div>
        </div>

<div class="relative z-10 max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8">

                {{-- Kolom 1: Logo + Info --}}
                <div class="lg:col-span-4 space-y-6">
                    @if($logoWhiteUrl)
                        <div class="flex items-center gap-3">
                            <img src="{{ $logoWhiteUrl }}" alt="{{ $footerOrgName }}" width="80" height="48" loading="lazy" decoding="async"
                                 class="h-12 w-auto max-w-[80px] object-contain flex-shrink-0" onerror="this.onerror=null;this.style.display='none';">
                            <div class="flex flex-col leading-tight">
                                <span class="text-base sm:text-lg font-bold text-white tracking-tight">{{ $footerOrgName }}</span>
                                @if($footerOrgSubtitle)
                                    <span class="text-[11px] tracking-widest text-slate-400 uppercase font-semibold mt-1">{{ $footerOrgSubtitle }}</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-lg bg-[#f0acf7] flex items-center justify-center text-white shadow-md shadow-slate-950/15 flex-shrink-0" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </span>
                            <div class="flex flex-col leading-tight">
                                <span class="text-base sm:text-lg font-bold text-white tracking-tight">{{ $footerOrgName }}</span>
                                @if($footerOrgSubtitle)
                                    <span class="text-[11px] tracking-widest text-slate-400 uppercase font-semibold mt-1">{{ $footerOrgSubtitle }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <p class="text-sm text-slate-400 leading-relaxed pr-2">
                        {{ $siteTagline }} <br>{{ $footerIntro }}<br>
                        <span class="inline-grid align-bottom text-[#acf7f0] font-semibold"
                            x-data="{ words: @js($footerWordsArray), index: 0 }"
                            x-init="setInterval(() => index = (index + 1) % words.length, 2500)">
                            <template x-for="(word, i) in words" :key="i">
                                <span x-show="index === i"
                                    x-transition:enter="transition ease-out duration-500 motion-reduce:transition-none"
                                    x-transition:enter-start="opacity-0 translate-y-3"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-500 motion-reduce:transition-none"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-3"
                                    class="col-start-1 row-start-1"
                                    x-text="word"></span>
                            </template>
                            <span class="col-start-1 row-start-1 invisible pointer-events-none" aria-hidden="true">Placeholder.</span>
                        </span>
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        @if(!empty($socials['twitter']))
                            <a href="{{ $socials['twitter'] }}" target="_blank" rel="noopener noreferrer" aria-label="Twitter" class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:bg-[#f7f0ac] hover:text-slate-950 hover:border-[#f7f0ac] transition-[background-color,border-color,color] duration-300 motion-reduce:transition-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                            </a>
                        @endif
                        @if(!empty($socials['linkedin']))
                            <a href="{{ $socials['linkedin'] }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white hover:border-transparent transition-[background-color,border-color,color] duration-300 motion-reduce:transition-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </a>
                        @endif
                        @if(!empty($socials['youtube']))
                            <a href="{{ $socials['youtube'] }}" target="_blank" rel="noopener noreferrer" aria-label="YouTube" class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:bg-red-600 hover:text-white hover:border-transparent transition-[background-color,border-color,color] duration-300 motion-reduce:transition-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        @endif
                        @if(!empty($socials['instagram']))
                            <a href="{{ $socials['instagram'] }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:bg-pink-600 hover:text-white hover:border-transparent transition-[background-color,border-color,color] duration-300 motion-reduce:transition-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        @endif
                        @if(!empty($socials['facebook']))
                            <a href="{{ $socials['facebook'] }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:bg-blue-700 hover:text-white hover:border-transparent transition-[background-color,border-color,color] duration-300 motion-reduce:transition-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.373 14.5 5 15.5 5H18V0h-3.808C10.59 0 9 1.581 9 4.75V8z"/></svg>
                            </a>
                        @endif
                        @if(!empty($socials['telegram']))
                            <a href="{{ $socials['telegram'] }}" target="_blank" rel="noopener noreferrer" aria-label="Telegram" class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:bg-sky-500 hover:text-white hover:border-transparent transition-[background-color,border-color,color] duration-300 motion-reduce:transition-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                            </a>
                        @endif
                        @php
                            $waUrl = !empty($socials['whatsapp'])
                                ? $socials['whatsapp']
                                : ($waNumber ? 'https://wa.me/' . $waNumber : null);
                        @endphp
                        @if($waUrl)
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:bg-[#f7f0ac] hover:text-slate-950 hover:border-[#f7f0ac] transition-[background-color,border-color,color] duration-300 motion-reduce:transition-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════════════════ --}}
                {{-- Kolom 2: Quick Links — LIMIT 5 ITEMS                     --}}
                {{-- ═══════════════════════════════════════════════════════════ --}}
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-white font-semibold text-base flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-[#acf7f0] rounded-full" aria-hidden="true"></span> {{ $quickLinksLabel }}
                    </h4>
                    <ul class="space-y-3 text-sm">
                        @if($quickLinks->count() > 0)
                            @foreach($quickLinks as $link)
                                @php $url = $resolveMenuUrl($link); @endphp
                                <li>
                                    <a href="{{ $url }}" class="hover:text-[#acf7f0] transition-colors duration-200 motion-reduce:transition-none">{{ $link->title }}</a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                {{-- Kolom 3: Contact --}}
                <div class="lg:col-span-3 space-y-4">
                    <h4 class="text-white font-semibold text-base flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-[#acf7f0] rounded-full" aria-hidden="true"></span> {{ $contactUsLabel }}
                    </h4>
                    <ul class="space-y-4 text-sm">
                        @if($contactAddress)
                            <li class="flex items-start gap-3 group">
                                <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 flex-shrink-0 mt-0.5 group-hover:border-[#f0acf7] group-hover:text-[#acf7f0] transition-[border-color,color] duration-300 motion-reduce:transition-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <span class="text-slate-400 group-hover:text-slate-200 transition-colors duration-300 motion-reduce:transition-none leading-snug">{!! nl2br(e($contactAddress)) !!}</span>
                            </li>
                        @endif
                        @if($contactPhone)
                            <li class="flex items-center gap-3 group">
                                <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 flex-shrink-0 group-hover:border-[#f0acf7] group-hover:text-[#acf7f0] transition-[border-color,color] duration-300 motion-reduce:transition-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <a href="tel:{{ $contactPhone }}" class="text-slate-400 group-hover:text-slate-200 transition-colors duration-300 motion-reduce:transition-none">{{ $contactPhone }}</a>
                            </li>
                        @endif
                        @if($contactEmail)
                            <li class="flex items-center gap-3 group">
                                <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 flex-shrink-0 group-hover:border-[#f0acf7] group-hover:text-[#acf7f0] transition-[border-color,color] duration-300 motion-reduce:transition-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <a href="mailto:{{ $contactEmail }}" class="text-slate-400 group-hover:text-slate-200 transition-colors duration-300 motion-reduce:transition-none">{{ $contactEmail }}</a>
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- Kolom 4: Newsletter --}}
                <div class="lg:col-span-3 space-y-4"
                     x-data="{
                         status: 'idle',
                         message: '',
                         errorMessage: '',
                         async submit(e) {
                             this.status = 'loading';
                             this.message = '';
                             this.errorMessage = '';

                             const form = e.target;
                             const formData = new FormData(form);
                             const token = document.querySelector('meta[name=csrf-token]')?.content
                                 || document.querySelector('input[name=_token]')?.value;

                             try {
                                 const res = await fetch('{{ route('newsletter.subscribe') }}', {
                                     method: 'POST',
                                     headers: {
                                         'X-CSRF-TOKEN': token,
                                         'X-Requested-With': 'XMLHttpRequest',
                                         'Accept': 'application/json',
                                     },
                                     body: formData,
                                     credentials: 'same-origin',
                                 });

                                 const data = await res.json();

                                 if (res.ok && data.success) {
                                     this.status = 'success';
                                     this.message = data.message || 'Berhasil!';
                                     form.reset();
                                 } else {
                                     this.status = 'error';
                                     this.errorMessage = data.message
                                         || Object.values(data.errors || {}).flat().join(' ')
                                         || 'Terjadi kesalahan. Coba lagi.';
                                 }
                             } catch (err) {
                                 this.status = 'error';
                                 this.errorMessage = 'Koneksi gagal. Coba lagi.';
                             }
                         }
                     }">

                    <h4 class="text-white font-semibold text-base flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-[#acf7f0] rounded-full" aria-hidden="true"></span> {{ $stayUpdatedLabel }}
                    </h4>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ $newsletterText }}</p>

                    <form @submit.prevent="submit($event)" class="space-y-3">
                        @csrf
                        <div class="relative bg-slate-800 border border-slate-700 rounded-xl p-1.5 flex items-center focus-within:border-[#acf7f0] transition">
                            <input type="email"
                                   name="email"
                                   required
                                   maxlength="150"
                                   autocomplete="email"
                                   placeholder="{{ $newsletterPlaceholder }}"
                                   class="w-full bg-transparent px-3 text-sm text-white placeholder-slate-500 focus:outline-none">

                            <button type="submit"
                                    :disabled="status === 'loading'"
                                    class="bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 px-5 py-2.5 rounded-lg text-sm font-semibold transition flex-shrink-0 disabled:opacity-50 shadow-md motion-reduce:transition-none">
                                <span x-show="status === 'idle'">{{ $newsletterButton }}</span>
                                <span x-show="status === 'loading'" x-cloak>...</span>
                                <span x-show="status === 'success'" x-cloak>OK</span>
                            </button>
                        </div>
                    </form>

                    <p x-show="status === 'success'" x-cloak x-text="message" class="text-[#acf7f0] text-xs leading-relaxed"></p>
                    <p x-show="status === 'error'" x-cloak x-text="errorMessage" class="text-red-400 text-xs leading-relaxed"></p>
                </div>

            </div>

            {{-- Copyright --}}
            <div class="mt-16 border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm gap-4 text-slate-400">
                <p>&copy; {{ date('Y') }} {{ $siteTitle }}. {{ $footerCopyright }}</p>
                <div class="flex items-center gap-2">
                    <span>{{ $vendorLabel }}</span>
                    @if($vendorUrl && $vendorUrl !== '#')
                        <a href="{{ $vendorUrl }}" target="_blank" rel="noopener noreferrer" class="font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#f7f0ac] to-[#f0acf7]">{{ $vendorName }}</a>
                    @else
                        <span class="font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#f7f0ac] to-[#f0acf7]">{{ $vendorName }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Back to Top --}}
        <button x-data="{ show: false }"
                @scroll.window="show = window.pageYOffset > 400"
                @click="window.scrollTo({top: 0, behavior: 'smooth'})"
                :class="show ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10 pointer-events-none'"
                class="fixed bottom-6 right-6 z-50 p-3.5 rounded-full bg-[#f0acf7] text-slate-950 shadow-xl hover:bg-[#f0acf7] transition-[transform,opacity,background-color] duration-300 motion-reduce:transition-none [transform:translateZ(0)]"
                aria-label="Kembali ke atas">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
        </button>
    </footer>

    @stack('scripts')
</body>
</html>
