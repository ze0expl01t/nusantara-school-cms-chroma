@php
    $safeInput = function ($block, $field, $default = null) {
        $value = $block->input($field);
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? $default;
        }
        return $value ?: $default;
    };

    // Kolom Kiri (Visi)
    $leftTitleMain      = $safeInput($block, 'left_title_main', 'Visi');
    $leftTitleHighlight = $safeInput($block, 'left_title_highlight', 'PT. Nama Perusahaan');
    $leftSubHeading     = $safeInput($block, 'left_sub_heading', 'Kami hadir untuk memberikan solusi terbaik.');
    $leftDescription    = $safeInput($block, 'left_description');

    // Kolom Kanan (Misi)
    $rightTitleMain      = $safeInput($block, 'right_title_main', 'Misi');
    $rightTitleHighlight = $safeInput($block, 'right_title_highlight', 'PT. Nama Perusahaan');
    $rightSubHeading     = $safeInput($block, 'right_sub_heading', 'Langkah-langkah strategis untuk mewujudkan tujuan.');
    $rightDescription    = $safeInput($block, 'right_description');

    // ===== STYLE UNTUK WYSIWYG (tanpa plugin typography) =====
    $proseClass = 'text-slate-600 text-sm sm:text-base leading-relaxed '
        . '[&_p]:my-2 [&_p]:leading-relaxed '
        . '[&_strong]:font-bold [&_strong]:text-slate-900 '
        . '[&_em]:italic '
        . '[&_a]:text-slate-900 [&_a]:underline [&_a]:underline-offset-2 hover:[&_a]:text-slate-900 '
        . '[&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-2 [&_ul]:space-y-1 '
        . '[&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-2 [&_ol]:space-y-1 '
        . '[&_li]:leading-relaxed '
        . '[&_blockquote]:border-l-4 [&_blockquote]:border-[#f0acf7] [&_blockquote]:pl-4 [&_blockquote]:italic [&_blockquote]:text-slate-500 [&_blockquote]:my-3 '
        . '[&_h1]:font-bold [&_h1]:text-slate-900 [&_h1]:text-xl [&_h1]:mt-4 [&_h1]:mb-2 '
        . '[&_h2]:font-bold [&_h2]:text-slate-900 [&_h2]:text-lg [&_h2]:mt-4 [&_h2]:mb-2 '
        . '[&_h3]:font-bold [&_h3]:text-slate-900 [&_h3]:text-base [&_h3]:mt-3 [&_h3]:mb-2 '
        . '[&_h4]:font-bold [&_h4]:text-slate-900 [&_h4]:mt-3 [&_h4]:mb-1 '
        . '[&_code]:bg-slate-100 [&_code]:text-slate-900 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded [&_code]:text-xs '
        . '[&_hr]:my-4 [&_hr]:border-slate-200';
@endphp

<section class="relative py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto overflow-hidden" data-block-reveal>

    {{-- Efek Ambient Glow Halus di Belakang --}}
    <div class="absolute top-1/2 left-1/4 -translate-y-1/2 w-64 h-64 sm:w-80 sm:h-80 bg-[#acf7f0]/10 blur-[100px] rounded-full pointer-events-none"></div>

    <div class="relative z-10">

        {{-- Grid Dua Kolom --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 items-stretch">

            {{-- KOTAK KIRI: VISI --}}
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-10 shadow-sm flex flex-col justify-between relative overflow-hidden group hover:border-[#f0acf7] hover:shadow-xl hover:shadow-slate-950/5 transition-all duration-300 motion-reduce:transition-none">

                {{-- Decorative glow --}}
                <div class="absolute top-0 right-0 w-32 h-32 bg-[#acf7f0]/5 blur-[50px] pointer-events-none"></div>

                <div class="space-y-5 sm:space-y-6 relative z-10">

                    {{-- Heading --}}
                    @if($leftTitleMain || $leftTitleHighlight)
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            {{ $leftTitleMain }}
                            @if($leftTitleHighlight)
                                <span class="block text-slate-900 mt-1">{{ $leftTitleHighlight }}</span>
                            @endif
                        </h2>
                    @endif

                    {{-- Sub Heading --}}
                    @if($leftSubHeading)
                        <p class="text-sm sm:text-base font-medium text-slate-600">
                            {{ $leftSubHeading }}
                        </p>
                    @endif

                    {{-- Deskripsi (WYSIWYG) --}}
                    @if($leftDescription)
                        <div class="{{ $proseClass }} pt-4 border-t border-slate-100">
                            {!! safe_html($leftDescription) !!}
                        </div>
                    @endif

                </div>
            </div>

            {{-- KOTAK KANAN: MISI --}}
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-10 shadow-sm flex flex-col justify-between relative overflow-hidden group hover:border-[#f0acf7] hover:shadow-xl hover:shadow-slate-950/5 transition-all duration-300 motion-reduce:transition-none">

                {{-- Decorative glow --}}
                <div class="absolute top-0 right-0 w-32 h-32 bg-[#acf7f0]/5 blur-[50px] pointer-events-none"></div>

                <div class="space-y-5 sm:space-y-6 relative z-10">

                    {{-- Heading --}}
                    @if($rightTitleMain || $rightTitleHighlight)
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            {{ $rightTitleMain }}
                            @if($rightTitleHighlight)
                                <span class="block text-slate-900 mt-1">{{ $rightTitleHighlight }}</span>
                            @endif
                        </h2>
                    @endif

                    {{-- Sub Heading --}}
                    @if($rightSubHeading)
                        <p class="text-sm sm:text-base font-medium text-slate-600">
                            {{ $rightSubHeading }}
                        </p>
                    @endif

                    {{-- Deskripsi (WYSIWYG) --}}
                    @if($rightDescription)
                        <div class="{{ $proseClass }} pt-4 border-t border-slate-100">
                            {!! safe_html($rightDescription) !!}
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</section>
