<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f172a">
    @php
        $resolvedCode = $statusCode
            ?? ((isset($exception) && method_exists($exception, 'getStatusCode'))
                ? $exception->getStatusCode()
                : 404);

        $messages = [
            403 => ['badge' => 'Error 403', 'title' => 'Akses Ditolak', 'desc' => 'Maaf, Anda tidak memiliki izin untuk mengakses halaman atau sumber daya ini.'],
            404 => ['badge' => 'Error 404', 'title' => 'Halaman Tidak Ditemukan', 'desc' => 'Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin tautan sudah berubah, dipindahkan, atau dihapus.'],
            419 => ['badge' => 'Error 419', 'title' => 'Sesi Telah Berakhir', 'desc' => 'Sesi Anda telah berakhir karena tidak ada aktivitas atau token keamanan kedaluwarsa. Silakan muat ulang halaman.'],
            429 => ['badge' => 'Error 429', 'title' => 'Terlalu Banyak Permintaan', 'desc' => 'Anda mengirim terlalu banyak permintaan dalam waktu singkat. Silakan tunggu beberapa saat lagi.'],
            500 => ['badge' => 'Error 500', 'title' => 'Kesalahan Server Internal', 'desc' => 'Maaf, terjadi gangguan pada sisi server. Tim kami sedang melakukan penanganan.'],
            503 => ['badge' => 'Error 503', 'title' => 'Layanan Tidak Tersedia', 'desc' => 'Kami sedang melakukan pemeliharaan rutin. Silakan kembali beberapa saat lagi.'],
        ];

        $pageBadge = $badgeLabel ?? ($messages[$resolvedCode]['badge'] ?? "Error {$resolvedCode}");
        $pageTitle = $title ?? ($messages[$resolvedCode]['title'] ?? 'Terjadi Kesalahan');
        $pageDesc  = $desc ?? ($messages[$resolvedCode]['desc'] ?? 'Maaf, sepertinya ada yang tidak beres. Silakan coba beberapa saat lagi.');
        $isSessionExpired = ((int) $resolvedCode === 419);
    @endphp
    <title>{{ $resolvedCode }} - {{ $pageTitle }} | {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --chroma-cyan: #4ef5e6;
            --chroma-cyan-soft: #acf7f0;
            --chroma-yellow: #ffe659;
            --chroma-yellow-soft: #f7f0ac;
            --chroma-magenta: #ff7cf2;
            --chroma-magenta-soft: #f0acf7;
            --surface-bg: #f8fafc;
            --text-ink: #0f172a;
            --text-subtle: #475569;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top left, rgba(172, 247, 240, 0.35), transparent 45%),
                        radial-gradient(circle at bottom right, rgba(240, 172, 247, 0.30), transparent 45%),
                        linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--text-ink);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            overflow-x: hidden;
            position: relative;
        }

        .chroma-orb {
            position: fixed;
            border-radius: 9999px;
            filter: blur(90px);
            z-index: 0;
            opacity: 0.45;
            pointer-events: none;
        }
        .chroma-orb-cyan {
            width: 340px;
            height: 340px;
            background: var(--chroma-cyan-soft);
            top: -90px;
            left: -90px;
            animation: chromaFloat 8s ease-in-out infinite;
        }
        .chroma-orb-magenta {
            width: 360px;
            height: 360px;
            background: var(--chroma-magenta-soft);
            bottom: -100px;
            right: -100px;
            animation: chromaFloat 10s ease-in-out infinite reverse;
        }
        .chroma-orb-yellow {
            width: 240px;
            height: 240px;
            background: var(--chroma-yellow-soft);
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.35;
        }

        @keyframes chromaFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-16px); }
        }

        .chroma-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 24px 48px -16px rgba(15, 23, 42, 0.12);
            animation: chromaEnter 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(14px);
            overflow: hidden;
        }

        @keyframes chromaEnter {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chroma-top-strip {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--chroma-yellow-soft), var(--chroma-cyan-soft), var(--chroma-magenta-soft));
        }

        .chroma-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.875rem;
            border-radius: 9999px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-ink);
            margin-bottom: 1.25rem;
        }

        .chroma-badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: linear-gradient(135deg, var(--chroma-cyan), var(--chroma-magenta));
        }

        .chroma-code {
            font-size: clamp(4.25rem, 12vw, 6rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
            margin-bottom: 0.875rem;
            background: linear-gradient(135deg, #0f172a 15%, #0d9488 55%, #c026d3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .chroma-title {
            font-size: clamp(1.35rem, 3.5vw, 1.65rem);
            font-weight: 800;
            color: var(--text-ink);
            margin-bottom: 0.625rem;
            letter-spacing: -0.02em;
        }

        .chroma-desc {
            font-size: 0.95rem;
            color: var(--text-subtle);
            line-height: 1.65;
            margin-bottom: 1.5rem;
        }

        .chroma-meta {
            display: inline-block;
            padding: 0.45rem 0.9rem;
            border-radius: 12px;
            background: rgba(247, 240, 172, 0.45);
            border: 1px solid rgba(15, 23, 42, 0.08);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-ink);
            margin-bottom: 1.75rem;
        }

        .chroma-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .chroma-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, var(--chroma-cyan-soft), var(--chroma-magenta-soft));
            border: 1px solid rgba(15, 23, 42, 0.12);
            color: var(--text-ink);
            font-weight: 800;
            font-size: 0.9rem;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: 0 10px 20px -8px rgba(15, 23, 42, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .chroma-btn-primary:hover {
            transform: translateY(-2px);
            filter: brightness(1.03);
            box-shadow: 0 14px 24px -8px rgba(15, 23, 42, 0.25);
        }

        .chroma-btn-primary:active {
            transform: translateY(0);
        }

        .chroma-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1.35rem;
            border-radius: 9999px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: var(--text-ink);
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: transform 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
        }

        .chroma-btn-secondary:hover {
            transform: translateY(-2px);
            border-color: #0f172a;
            background: #f8fafc;
        }

        .chroma-btn-primary:focus-visible,
        .chroma-btn-secondary:focus-visible {
            outline: 2px solid #0f172a;
            outline-offset: 3px;
        }

        @media (prefers-reduced-motion: reduce) {
            .chroma-orb,
            .chroma-card,
            .chroma-btn-primary,
            .chroma-btn-secondary {
                animation: none !important;
                transition: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }

        @media (max-width: 480px) {
            .chroma-card {
                padding: 2rem 1.35rem;
                border-radius: 20px;
            }
            .chroma-actions {
                flex-direction: column;
                width: 100%;
            }
            .chroma-btn-primary,
            .chroma-btn-secondary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="chroma-orb chroma-orb-cyan" aria-hidden="true"></div>
    <div class="chroma-orb chroma-orb-magenta" aria-hidden="true"></div>
    <div class="chroma-orb chroma-orb-yellow" aria-hidden="true"></div>

    <main class="chroma-card" role="main">
        <div class="chroma-top-strip" aria-hidden="true"></div>

        <div class="chroma-badge">
            <span class="chroma-badge-dot" aria-hidden="true"></span>
            <span>{{ $pageBadge }}</span>
        </div>

        @if(!empty($showCode))
            <div class="chroma-code">{{ $resolvedCode }}</div>
        @endif

        <h1 class="chroma-title">{{ $pageTitle }}</h1>
        <p class="chroma-desc">{{ $pageDesc }}</p>

        @if(!empty($extraInfo))
            <div class="chroma-meta">{{ $extraInfo }}</div>
        @endif

        <div class="chroma-actions">
            @if($isSessionExpired)
                <button type="button" onclick="window.location.reload()" class="chroma-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Muat Ulang Halaman</span>
                </button>
                <a href="{{ url('/') }}" class="chroma-btn-secondary">
                    <span>Kembali ke Beranda</span>
                </a>
            @elseif(!empty($hideActions))
                {{-- Tanpa tombol aksi pada mode pemeliharaan murni --}}
            @else
                <a href="{{ url('/') }}" class="chroma-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
                <button type="button" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ url('/') }}'; }" class="chroma-btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    <span>Halaman Sebelumnya</span>
                </button>
            @endif
        </div>
    </main>
</body>
</html>