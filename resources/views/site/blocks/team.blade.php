@php
    use App\Models\TeamMember;

    $safeInput = function ($block, $field, $default = null) {
        $value = $block->input($field);
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('app.fallback_locale')]
                ?? $default;
        }
        return filled($value) ? $value : $default;
    };

    $sectionTitle   = $safeInput($block, 'title', 'Tim & Pengajar Kami');
    $badgeText      = $safeInput($block, 'badge_text', 'TIM KAMI');
    $subtitle       = $safeInput($block, 'subtitle', '');
    $filterDept     = $safeInput($block, 'filter_department', 'all');
    $onlyLeadership = (bool) $safeInput($block, 'only_leadership', false);
    $limit          = (int) $safeInput($block, 'limit', 8);
    $columns        = (int) $safeInput($block, 'columns', 4);

    $query = TeamMember::query()
        ->published()
        ->with(['medias', 'slugs', 'translations']);

    if ($filterDept !== 'all') {
        $query->where('department', $filterDept);
    }

    if ($onlyLeadership) {
        $query->where('is_leadership', true);
    }

    $query->orderBy('position', 'asc')->orderBy('id', 'asc');

    if ($limit > 0) {
        $query->take($limit);
    }

    $members = $query->get();

    $gridClass = match($columns) {
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        5 => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5',
        default => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
    };

    // Helper: cek foto
    $hasPhoto = fn ($m) => $m->hasImage('photo', 'default') || $m->hasImage('photo');
@endphp

<section class="py-8 sm:py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden [content-visibility:auto] [contain-intrinsic-size:800px]" data-block-reveal>

    {{-- Ambient glow — blur dikurangi --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-[#acf7f0]/10 blur-[80px] rounded-full pointer-events-none [transform:translate3d(-50%,-50%,0)]" aria-hidden="true"></div>

    <div class="relative z-10 space-y-8 sm:space-y-10">

        {{-- Header Section --}}
        <div class="flex flex-col items-center text-center space-y-3 pb-4 sm:pb-6">

            @if($badgeText)
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white border border-slate-200/90 shadow-2xs text-xs font-extrabold text-slate-950 tracking-wide uppercase">
                    <span class="w-2 h-2 rounded-full bg-[#acf7f0]" aria-hidden="true"></span>
                    {{ $badgeText }}
                </div>
            @endif

            @if($sectionTitle)
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight px-2 [text-wrap:balance]">
                    {{ $sectionTitle }}
                </h2>
            @endif

            <div class="w-20 h-1 mx-auto rounded-full bg-gradient-to-r from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]" aria-hidden="true"></div>

            @if($subtitle)
                <p class="text-slate-500 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed px-2 pt-1">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        {{-- Team Grid --}}
        @if($members->count() > 0)

            <div class="grid {{ $gridClass }} gap-6 sm:gap-8">
                @foreach($members as $member)
                    @php
                        $photo = $hasPhoto($member)
                            ? ($member->hasImage('photo', 'default') ? $member->image('photo', 'default') : $member->image('photo'))
                            : null;

                        $name     = $member->title;
                        $jobTitle = $member->job_title;
                        $dept     = $member->department;
                        $bio      = $member->description;

                        $socials = array_filter([
                            'facebook'  => $member->facebook_url,
                            'instagram' => $member->instagram_url,
                            'linkedin'  => $member->linkedin_url,
                            'twitter'   => $member->twitter_url,
                            'youtube'   => $member->youtube_url,
                            'whatsapp'  => $member->whatsapp_url,
                        ]);

                        $initial = mb_substr($name, 0, 1);
                    @endphp

                    <div
                        x-data="{
                            open: false,
                            init() {
                                this.$watch('open', value => {
                                    document.body.style.overflow = value ? 'hidden' : '';
                                });
                            }
                        }"
                        @keydown.escape.window="open = false"
                        class="group [contain:layout_style]"
                    >

                        {{-- Trigger Button --}}
                        <button
                            type="button"
                            @click="open = true"
                            aria-label="Lihat profil {{ $name }}"
                            class="w-full text-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#acf7f0] focus-visible:ring-offset-4 focus-visible:ring-offset-white rounded-2xl p-2 transition-colors duration-200 hover:bg-slate-50 motion-reduce:transition-none"
                        >
                            {{-- Photo --}}
                            <div class="relative mx-auto w-28 h-28 sm:w-32 sm:h-32 lg:w-36 lg:h-36 mb-4">
                                <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-[#f7f0ac] to-[#f0acf7] opacity-0 group-hover:opacity-100 transition-opacity duration-300" aria-hidden="true"></div>

                                <div class="relative w-full h-full rounded-full overflow-hidden bg-slate-100 border-4 border-white shadow-lg group-hover:scale-105 transition-transform duration-300 motion-reduce:transition-none motion-reduce:group-hover:scale-100 [transform:translateZ(0)]">
                                    @if($photo)
                                        {!! twill_proxy_img($member, 'photo', [160, 320, 480], '(max-width: 768px) 128px, 144px', $name, [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80', ['class' => 'w-full h-full object-cover', 'width' => '480', 'height' => '480', 'loading' => 'lazy', 'decoding' => 'async', 'onerror' => "this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}"]) !!}
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#f7f0ac] to-[#f0acf7] text-slate-900 font-bold text-3xl sm:text-4xl select-none" aria-hidden="true">
                                            {{ $initial }}
                                        </div>
                                    @endif
                                </div>

                                @if($member->is_leadership)
                                    <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-[#f7f0ac] border-2 border-white flex items-center justify-center shadow-md text-slate-950">
                                        <svg class="w-4 h-4 text-slate-950" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4-6.2-4.6-6.2 4.6 2.4-7.4L2 9.4h7.6z"/>
                                        </svg>
                                    </div>
                                @endif

                                {{-- Eye overlay --}}
                                <div class="absolute inset-0 rounded-full bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center pointer-events-none" aria-hidden="true">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Name --}}
                            <h3 class="text-slate-900 font-bold text-sm sm:text-base leading-tight group-hover:text-slate-950 transition-colors duration-300">
                                {{ $name }}
                            </h3>

                            @if($jobTitle)
                                <p class="text-slate-900 text-xs sm:text-sm font-semibold mt-1 leading-snug">
                                    {{ $jobTitle }}
                                </p>
                            @endif

                            @if($dept)
                                <p class="text-slate-400 text-xs mt-1">{{ $dept }}</p>
                            @endif

                            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold text-slate-900 group-hover:text-slate-950">
                                Lihat Profil
                                <svg class="w-3 h-3 transform group-hover:translate-x-0.5 transition-transform motion-reduce:transition-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </button>

                        {{-- Social Links di card --}}
                        @if(!empty($socials))
                            <div class="flex justify-center gap-1.5 mt-3 flex-wrap">
                                @foreach($socials as $platform => $url)
                                    <a href="{{ $url }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       aria-label="{{ $name }} di {{ ucfirst($platform) }}"
                                       class="w-7 h-7 rounded-full bg-slate-100 hover:bg-[#f0acf7] text-slate-600 hover:text-slate-950 flex items-center justify-center transition-[background-color,color] duration-200 motion-reduce:transition-none">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            @switch($platform)
                                                @case('facebook')
                                                    <path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.373 14.5 5 15.5 5H18V0h-3.808C10.59 0 9 1.581 9 4.75V8z"/>
                                                    @break
                                                @case('instagram')
                                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                                    @break
                                                @case('linkedin')
                                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                                    @break
                                                @case('twitter')
                                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                                    @break
                                                @case('youtube')
                                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                    @break
                                                @case('whatsapp')
                                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654z"/>
                                                    @break
                                            @endswitch
                                        </svg>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        {{-- Modal Bio --}}
                        <template x-teleport="body">
                            <div
                                x-show="open"
                                x-cloak
                                @click.self="open = false"
                                x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/80 backdrop-blur-sm"
                                role="dialog"
                                aria-modal="true"
                                aria-labelledby="team-modal-title-{{ $member->id }}"
                            >
                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                                    x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                                    @click.stop
                                    class="relative bg-white w-full sm:rounded-3xl rounded-t-3xl shadow-2xl
                                           max-w-lg
                                           h-[92vh] sm:h-auto sm:max-h-[90vh]
                                           flex flex-col
                                           overflow-hidden"
                                >

                                    {{-- Mobile Drag Handle --}}
                                    <div class="sm:hidden pt-2.5 pb-1 flex justify-center bg-gradient-to-br from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]">
                                        <div class="w-10 h-1 rounded-full bg-white/40" aria-hidden="true"></div>
                                    </div>

                                    {{-- Close Button --}}
                                    <button
                                        type="button"
                                        @click="open = false"
                                        aria-label="Tutup"
                                        class="absolute top-3 right-3 z-20 w-9 h-9 rounded-full
                                               bg-white/95 hover:bg-white
                                               text-slate-700 hover:text-slate-900
                                               flex items-center justify-center
                                               shadow-lg transition-colors duration-200
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-white
                                               active:scale-95"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>

                                    {{-- Header Compact --}}
                                    <div class="relative bg-gradient-to-br from-[#f7f0ac] via-[#acf7f0] to-[#f0acf7]
                                                px-5 pt-6 pb-5 sm:px-6 sm:pt-8 sm:pb-6
                                                flex-shrink-0">

                                        <div class="absolute inset-0 opacity-10 pointer-events-none" aria-hidden="true">
                                            <svg class="w-full h-full" fill="none" viewBox="0 0 400 200" preserveAspectRatio="none">
                                                <path stroke="currentColor" stroke-width="1" d="M0 40h400M0 80h400M0 120h400M0 160h400M40 0v200M80 0v200M120 0v200M160 0v200M200 0v200M240 0v200M280 0v200M320 0v200M360 0v200"/>
                                            </svg>
                                        </div>

                                        <div class="relative flex flex-col items-center text-center">

                                            <div class="relative">
                                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full overflow-hidden border-4 border-white shadow-xl bg-slate-100">
                                                    @if($photo)
                                                        {!! twill_proxy_img($member, 'photo', [160, 320, 480], '(max-width: 768px) 96px', $name, [], 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80', ['class' => 'w-full h-full object-cover', 'width' => '480', 'height' => '480', 'loading' => 'lazy', 'decoding' => 'async', 'onerror' => "this.removeAttribute('srcset');if(!this.dataset.fbStep){this.dataset.fbStep='1';this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80';}else if(this.dataset.fbStep==='1'){this.dataset.fbStep='2';this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80';}else{this.onerror=null;this.style.display='none';}"]) !!}
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#f7f0ac] to-[#f0acf7] text-slate-900 font-bold text-3xl select-none" aria-hidden="true">
                                                            {{ $initial }}
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($member->is_leadership)
                                                    <div class="absolute -bottom-1 -right-1 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#f7f0ac] border-2 border-white flex items-center justify-center shadow-lg text-slate-950">
                                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-slate-950" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4-6.2-4.6-6.2 4.6 2.4-7.4L2 9.4h7.6z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>

                                            <h3 id="team-modal-title-{{ $member->id }}"
                                                class="text-lg sm:text-2xl font-extrabold text-white mt-3 sm:mt-4 leading-tight px-2">
                                                {{ $name }}
                                            </h3>

                                            @if($jobTitle)
                                                <p class="text-[#f7f0ac] text-xs sm:text-sm font-semibold mt-1 px-2">
                                                    {{ $jobTitle }}
                                                </p>
                                            @endif

                                            @if($dept)
                                                <span class="inline-block mt-2 px-3 py-1 rounded-full bg-white/25 backdrop-blur-sm text-[10px] sm:text-[11px] font-bold text-white uppercase tracking-wider">
                                                    {{ $dept }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Body Scrollable --}}
                                    <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-6 sm:py-6 space-y-5" style="scrollbar-width: thin;">

                                        @if($bio)
                                            <div>
                                                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                                                    Biografi
                                                </h4>
                                                <div class="text-slate-600 leading-relaxed text-sm
                                                            [&_p]:my-2
                                                            [&_strong]:text-slate-900 [&_strong]:font-bold
                                                            [&_em]:italic
                                                            [&_a]:text-slate-900 [&_a]:underline
                                                            [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-2
                                                            [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-2">
                                                    {!! safe_html($bio) !!}
                                                </div>
                                            </div>
                                        @endif

                                        @if($member->email || $member->phone)
                                            <div class="border-t border-slate-100 pt-4">
                                                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                                                    Kontak
                                                </h4>
                                                <div class="space-y-2 text-sm">
                                                    @if($member->email)
                                                        <a href="mailto:{{ $member->email }}"
                                                           class="flex items-center gap-2.5 text-slate-600 hover:text-slate-950 transition-colors duration-200 motion-reduce:transition-none group/contact">
                                                            <div class="w-8 h-8 rounded-lg bg-[#acf7f0]/30 group-hover/contact:bg-[#acf7f0] flex items-center justify-center text-slate-900 shrink-0 transition-colors duration-200">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                                </svg>
                                                            </div>
                                                            <span class="truncate">{{ $member->email }}</span>
                                                        </a>
                                                    @endif

                                                    @if($member->phone)
                                                        <a href="tel:{{ $member->phone }}"
                                                           class="flex items-center gap-2.5 text-slate-600 hover:text-slate-950 transition-colors duration-200 motion-reduce:transition-none group/contact">
                                                            <div class="w-8 h-8 rounded-lg bg-[#acf7f0]/30 group-hover/contact:bg-[#acf7f0] flex items-center justify-center text-slate-900 shrink-0 transition-colors duration-200">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                                </svg>
                                                            </div>
                                                            <span>{{ $member->phone }}</span>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if(!empty($socials))
                                            <div class="border-t border-slate-100 pt-4 pb-2">
                                                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                                                    Sosial Media
                                                </h4>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($socials as $platform => $url)
                                                        <a href="{{ $url }}"
                                                           target="_blank"
                                                           rel="noopener noreferrer"
                                                           aria-label="{{ ucfirst($platform) }}"
                                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 hover:bg-[#f0acf7] text-slate-700 hover:text-slate-950 text-xs font-semibold transition-[background-color,color,border-color] duration-200 border border-slate-200 hover:border-[#f0acf7] active:scale-95 motion-reduce:transition-none">
                                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                @switch($platform)
                                                                    @case('facebook')
                                                                        <path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.373 14.5 5 15.5 5H18V0h-3.808C10.59 0 9 1.581 9 4.75V8z"/>
                                                                        @break
                                                                    @case('instagram')
                                                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                                                        @break
                                                                    @case('linkedin')
                                                                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                                                        @break
                                                                    @case('twitter')
                                                                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                                                        @break
                                                                    @case('youtube')
                                                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                                        @break
                                                                    @case('whatsapp')
                                                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654z"/>
                                                                        @break
                                                                @endswitch
                                                            </svg>
                                                            {{ ucfirst($platform) }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div class="h-2 sm:hidden"></div>
                                    </div>

                                </div>
                            </div>
                        </template>

                    </div>
                @endforeach
            </div>

        @else
            <div class="text-center py-8 sm:py-10 bg-white border border-slate-200 rounded-3xl shadow-sm">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-slate-500 text-sm font-medium">Belum ada anggota tim yang ditambahkan.</p>
            </div>
        @endif

    </div>
</section>
