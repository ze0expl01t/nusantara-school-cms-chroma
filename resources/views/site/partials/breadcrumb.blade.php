{{-- ============================================================
     Breadcrumb Navigation + JSON-LD Structured Data
     -----------------------------------------------------------
     Variable: $items (array)
     Format: [
         ['label' => 'Fasilitas', 'url' => url('/fasilitas')],
         ['label' => $title, 'url' => null], // current page
     ]
     ============================================================ --}}

@php
    $items = $items ?? [];
@endphp

@if(count($items) > 0)

    {{-- ============ JSON-LD Structured Data ============ --}}
    @php
        $breadcrumbData = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => url('/'),
                ],
            ],
        ];

        foreach ($items as $index => $bcItem) {
            $listItem = [
                '@type' => 'ListItem',
                'position' => $index + 2,
                'name' => strip_tags($bcItem['label']),
            ];
            if (!empty($bcItem['url'])) {
                $listItem['item'] = $bcItem['url'];
            }
            $breadcrumbData['itemListElement'][] = $listItem;
        }
    @endphp

    <script type="application/ld+json">
        {!! json_encode($breadcrumbData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- ============ Visual Breadcrumb ============ --}}
    <nav aria-label="Breadcrumb" class="w-full">
        <ol class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs sm:text-sm">
            {{-- Home --}}
            <li>
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-1.5 text-slate-500 hover:text-slate-950 font-medium transition-colors"
                   aria-label="Home">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="hidden sm:inline">Home</span>
                </a>
            </li>

            {{-- Items --}}
            @foreach($items as $bcItem)
                <li class="inline-flex items-center gap-1.5 sm:gap-2 min-w-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>

                    @if(!empty($bcItem['url']) && !$loop->last)
                        <a href="{{ $bcItem['url'] }}"
                           class="text-slate-500 hover:text-slate-950 font-medium transition-colors truncate max-w-[120px] sm:max-w-[200px]"
                           title="{{ strip_tags($bcItem['label']) }}">
                            {{ $bcItem['label'] }}
                        </a>
                    @else
                        <span class="text-slate-900 font-semibold truncate max-w-[180px] sm:max-w-md"
                              aria-current="page"
                              title="{{ strip_tags($bcItem['label']) }}">
                            {{ $bcItem['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

@endif
