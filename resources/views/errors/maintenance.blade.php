<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f172a">
    @php
        $pageTitle    = $title ?? 'Sedang Dalam Pemeliharaan';
        $pageMessage  = $message ?? 'Kami sedang melakukan pemeliharaan sistem pembelajaran dan layanan sekolah. Mohon tunggu beberapa saat lagi.';
        $pageEta      = $eta ?? null;
        $pageEmail    = $contactEmail ?? null;
        $appName      = config('app.name', 'Nusantara School');
    @endphp
    <title>Maintenance - {{ $pageTitle }} | {{ $appName }}</title>

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
            --ink: #0f172a;
            --muted: #475569;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: var(--ink);
            background-color: #f8fafc;
            background-image:
                radial-gradient(circle at 12% 15%, rgba(172, 247, 240, 0.45), transparent 42%),
                radial-gradient(circle at 88% 82%, rgba(240, 172, 247, 0.40), transparent 44%),
                radial-gradient(circle at 78% 18%, rgba(247, 240, 172, 0.35), transparent 38%),
                linear-gradient(to right, rgba(15, 23, 42, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
            background-size: auto, auto, auto, 28px 28px, 28px 28px;
            overflow-x: hidden;
            position: relative;
        }

        .edu-ornament-layer {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .edu-float {
            position: absolute;
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform;
        }

        .maint-card {
            position: relative;
            z-index: 1;
            max-width: 620px;
            width: 100%;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(15, 23, 42, 0.09);
            border-radius: 28px;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 55px -18px rgba(15, 23, 42, 0.14);
            overflow: hidden;
            animation: cardReveal 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardReveal {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .maint-top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--chroma-yellow), var(--chroma-cyan), var(--chroma-magenta));
        }

        .maint-header-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .maint-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.12);
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--ink);
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #0d9488;
            box-shadow: 0 0 0 0 rgba(13, 148, 136, 0.55);
            animation: pulseRing 2s infinite;
        }

        @keyframes pulseRing {
            0% { box-shadow: 0 0 0 0 rgba(13, 148, 136, 0.55); }
            70% { box-shadow: 0 0 0 8px rgba(13, 148, 136, 0); }
            100% { box-shadow: 0 0 0 0 rgba(13, 148, 136, 0); }
        }

        .school-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            background: rgba(172, 247, 240, 0.35);
            border: 1px solid rgba(15, 23, 42, 0.08);
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--ink);
        }

        .edu-icon-banner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(247, 240, 172, 0.7), rgba(172, 247, 240, 0.75), rgba(240, 172, 247, 0.65));
            border: 1px solid rgba(15, 23, 42, 0.1);
            margin-bottom: 1.25rem;
            color: var(--ink);
        }

        .maint-title {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.025em;
            color: var(--ink);
            margin-bottom: 0.75rem;
        }

        .maint-desc {
            font-size: 0.975rem;
            line-height: 1.7;
            color: var(--muted);
            margin-bottom: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.75rem;
        }

        .info-box {
            padding: 0.875rem 1rem;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, 0.08);
            text-align: left;
        }

        .info-label {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--ink);
        }

        .maint-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-chroma {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.82rem 1.45rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, var(--chroma-cyan-soft), var(--chroma-magenta-soft));
            border: 1px solid rgba(15, 23, 42, 0.14);
            color: var(--ink);
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .btn-chroma:hover:not(:disabled) {
            transform: translateY(-2px);
            filter: brightness(1.03);
            box-shadow: 0 12px 22px -10px rgba(15, 23, 42, 0.25);
        }

        .btn-chroma:disabled {
            opacity: 0.7;
            cursor: wait;
        }

        .btn-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.8rem 1.25rem;
            border-radius: 9999px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: var(--ink);
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .btn-ghost:hover {
            transform: translateY(-2px);
            border-color: var(--ink);
        }

        .status-feedback {
            margin-top: 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--muted);
            min-height: 1.25rem;
        }

        @media (prefers-reduced-motion: reduce) {
            .maint-card, .pulse-dot, .edu-float, .btn-chroma, .btn-ghost {
                animation: none !important;
                transition: none !important;
                transform: none !important;
            }
        }

        @media (max-width: 520px) {
            .maint-card {
                padding: 2rem 1.35rem;
                border-radius: 22px;
            }
            .maint-actions {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    {{-- Education & Chroma floating ornaments --}}
    <div class="edu-ornament-layer" aria-hidden="true">
        {{-- Graduation cap (top-left) --}}
        <svg data-parallax="14" class="edu-float hidden sm:block top-8 left-8 w-16 h-16 text-[#0f172a]/20" viewBox="0 0 64 64" fill="none">
            <path d="M32 12L4 25L32 38L60 25L32 12Z" fill="#acf7f0" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/>
            <path d="M16 32V45C16 49 23.2 53 32 53C40.8 53 48 49 48 45V32" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
            <path d="M56 27V42" stroke="#ff7cf2" stroke-width="3" stroke-linecap="round"/>
        </svg>

        {{-- Open book & notebook (bottom-right) --}}
        <svg data-parallax="-16" class="edu-float hidden sm:block bottom-8 right-8 w-20 h-20 text-[#0f172a]/20" viewBox="0 0 64 64" fill="none">
            <path d="M10 16C18 14 26 16 32 20C38 16 46 14 54 16V48C46 46 38 48 32 52C26 48 18 46 10 48V16Z" fill="#f7f0ac" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/>
            <path d="M32 20V52" stroke="currentColor" stroke-width="2.2"/>
            <path d="M17 25H26M17 32H25M38 25H47M38 32H46" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>

        {{-- Pencil & ruler / academic accent (top-right) --}}
        <svg data-parallax="10" class="edu-float hidden md:block top-12 right-14 w-14 h-14 text-[#0f172a]/20" viewBox="0 0 64 64" fill="none">
            <rect x="38" y="10" width="12" height="36" rx="3" transform="rotate(35 38 10)" fill="#f0acf7" stroke="currentColor" stroke-width="2"/>
            <path d="M20 38L15 49L26 44" fill="#ffe659" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        </svg>

        {{-- Star / excellence marker (bottom-left) --}}
        <svg data-parallax="-12" class="edu-float hidden md:block bottom-12 left-12 w-14 h-14 text-[#0f172a]/20" viewBox="0 0 64 64" fill="none">
            <path d="M32 10L38.2 22.8L52.2 24.8L42.1 34.6L44.5 48.6L32 42L19.5 48.6L21.9 34.6L11.8 24.8L25.8 22.8L32 10Z" fill="#4ef5e6" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        </svg>
    </div>

    <main class="maint-card" role="main">
        <div class="maint-top-bar" aria-hidden="true"></div>

        <div class="maint-header-row">
            <span class="maint-badge">
                <span class="pulse-dot" aria-hidden="true"></span>
                <span>Maintenance Mode</span>
            </span>

            <span class="school-pill">
                <span>{{ $appName }}</span>
            </span>
        </div>

        <div class="edu-icon-banner" aria-hidden="true">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
            </svg>
        </div>

        <h1 class="maint-title">{{ $pageTitle }}</h1>
        <p class="maint-desc">{{ $pageMessage }}</p>

        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">Status Layanan</div>
                <div class="info-value">Pemeliharaan Terjadwal</div>
            </div>

            @if(filled($pageEta))
                <div class="info-box">
                    <div class="info-label">Estimasi Selesai</div>
                    <div class="info-value">{{ $pageEta }}</div>
                </div>
            @endif

            @if(filled($pageEmail))
                <div class="info-box">
                    <div class="info-label">Bantuan / Kontak</div>
                    <div class="info-value">{{ $pageEmail }}</div>
                </div>
            @endif
        </div>

        <div class="maint-actions">
            <button type="button" id="btnCheckStatus" class="btn-chroma">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span id="btnCheckLabel">Cek Status Layanan</span>
            </button>

            @if(filled($pageEmail))
                <button type="button" id="btnCopyEmail" data-email="{{ $pageEmail }}" class="btn-ghost">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span id="btnCopyLabel">Salin Email</span>
                </button>
            @endif
        </div>

        <div id="statusFeedback" class="status-feedback" role="status" aria-live="polite"></div>
    </main>

    <script>
        (function () {
            const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (!prefersReduced) {
                const items = Array.from(document.querySelectorAll('[data-parallax]'));
                let rafId = null;
                window.addEventListener('pointermove', function (e) {
                    if (rafId) return;
                    rafId = window.requestAnimationFrame(function () {
                        const nx = (e.clientX / Math.max(window.innerWidth, 1)) - 0.5;
                        const ny = (e.clientY / Math.max(window.innerHeight, 1)) - 0.5;
                        items.forEach(function (el) {
                            const depth = parseFloat(el.getAttribute('data-parallax') || '10');
                            const tx = (nx * depth).toFixed(1);
                            const ty = (ny * depth).toFixed(1);
                            el.style.transform = 'translate3d(' + tx + 'px, ' + ty + 'px, 0)';
                        });
                        rafId = null;
                    });
                }, { passive: true });
            }

            const checkBtn = document.getElementById('btnCheckStatus');
            const checkLabel = document.getElementById('btnCheckLabel');
            const feedback = document.getElementById('statusFeedback');

            if (checkBtn && checkLabel && feedback) {
                checkBtn.addEventListener('click', async function () {
                    checkBtn.disabled = true;
                    checkLabel.textContent = 'Mengecek...';
                    feedback.textContent = 'Memeriksa status layanan terkini...';
                    try {
                        const res = await fetch(window.location.href, {
                            method: 'GET',
                            cache: 'no-store',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.status !== 503) {
                            feedback.textContent = 'Layanan sudah kembali aktif! Memuat ulang halaman...';
                            window.location.reload();
                            return;
                        }
                        const nowStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        feedback.textContent = 'Pemeliharaan masih berlangsung (dicek pukul ' + nowStr + ').';
                    } catch (err) {
                        feedback.textContent = 'Belum dapat terhubung ke server. Silakan coba beberapa saat lagi.';
                    } finally {
                        checkBtn.disabled = false;
                        checkLabel.textContent = 'Cek Status Layanan';
                    }
                });
            }

            const copyBtn = document.getElementById('btnCopyEmail');
            const copyLabel = document.getElementById('btnCopyLabel');
            if (copyBtn && copyLabel && feedback) {
                copyBtn.addEventListener('click', async function () {
                    const email = copyBtn.getAttribute('data-email') || '';
                    if (!email) return;
                    try {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            await navigator.clipboard.writeText(email);
                        } else {
                            const ta = document.createElement('textarea');
                            ta.value = email;
                            document.body.appendChild(ta);
                            ta.select();
                            document.execCommand('copy');
                            document.body.removeChild(ta);
                        }
                        copyLabel.textContent = 'Tersalin!';
                        feedback.textContent = 'Alamat email ' + email + ' berhasil disalin.';
                        setTimeout(function () { copyLabel.textContent = 'Salin Email'; }, 2000);
                    } catch (e) {
                        feedback.textContent = 'Silakan salin manual email: ' + email;
                    }
                });
            }
        })();
    </script>
</body>
</html>
