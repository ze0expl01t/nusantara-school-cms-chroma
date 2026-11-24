<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    |
    | NOTE: 'create' => false untuk semua module — tombol "Create new"
    | tidak ditampilkan di dashboard karena sudah ada di navigation utama.
    | Untuk menampilkan kembali, ubah nilai 'create' jadi true.
    |
    */

    'dashboard' => [
        'auth_activity_log' => [
            'login' => true,
            'logout' => true,
        ],

        'modules' => [
            'pages' => [
                'name' => 'pages', 'label' => 'Halaman', 'label_singular' => 'Halaman',
                'count' => true, 'create' => true, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'posts' => [
                'name' => 'posts', 'label' => 'Berita', 'label_singular' => 'Berita',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'brosurs' => [
                'name' => 'brosurs', 'label' => 'Brosur', 'label_singular' => 'Brosur',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'projects' => [
                'name' => 'projects', 'label' => 'Project', 'label_singular' => 'Project',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'agendas' => [
                'name' => 'agendas', 'label' => 'Agenda', 'label_singular' => 'Agenda',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'extracurriculars' => [
                'name' => 'extracurriculars', 'label' => 'Ekskul', 'label_singular' => 'Ekskul',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'facilities' => [
                'name' => 'facilities', 'label' => 'Fasilitas', 'label_singular' => 'Fasilitas',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'teamMembers' => [
                'name' => 'teamMembers', 'label' => 'Tim & Pengajar', 'label_singular' => 'Anggota',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'jurusans' => [
                'name' => 'jurusans', 'label' => 'Jurusan', 'label_singular' => 'Jurusan',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'achievements' => [
                'name' => 'achievements', 'label' => 'Prestasi', 'label_singular' => 'Prestasi',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
            'jobVacancies' => [
                'name' => 'jobVacancies', 'label' => 'Bursa Kerja', 'label_singular' => 'Lowongan',
                'count' => true, 'create' => false, 'activity' => true,
                'draft' => true, 'search' => true,
            ],
        
            'menuLinks' => [
                'name' => 'menuLinks', 'label' => 'Menu', 'label_singular' => 'Menu',
                'count' => false, 'create' => false, 'activity' => false,
                'draft' => false, 'search' => false,
            ],
            'newsletterSubscribers' => [
                'name' => 'newsletterSubscribers', 'label' => 'Newsletter', 'label_singular' => 'Newsletter',
                'count' => true, 'create' => false, 'activity' => false,
                'draft' => false, 'search' => true,
            ],
],

        'analytics' => [
            'enabled' => false,
            'service_account_credentials_json' => storage_path('app/analytics/service-account-credentials.json'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Block Editor
    |--------------------------------------------------------------------------
    */

    'block_editor' => [
        'use_twill_blocks' => [],

        'crops' => [
            'cover' => [
                'desktop' => [['name' => 'desktop', 'ratio' => 16 / 10]],
                'mobile' => [['name' => 'mobile',  'ratio' => 16 / 10]],
            ],
            'highlight' => [
                'desktop' => [['name' => 'desktop', 'ratio' => 16 / 9]],
                'mobile' => [['name' => 'mobile',  'ratio' => 1]],
            ],
            'background_image' => [
                'desktop' => [['name' => 'desktop', 'ratio' => 16 / 9]],
            ],
            'hero_image' => [
                'desktop' => [['name' => 'desktop', 'ratio' => 4 / 3]],
            ],
            'app_logo' => [
                'default' => [['name' => 'default', 'ratio' => 1]],
            ],
            'photo' => [
                'default' => [['name' => 'default',  'ratio' => 3 / 4]],
                'desktop' => [['name' => 'desktop',  'ratio' => 3 / 4]],
                'portrait' => [['name' => 'portrait', 'ratio' => 3 / 4]],
            ],
            'gallery_image' => [
                'desktop' => [['name' => 'desktop', 'ratio' => 16 / 9]],
            ],
            'hero_5_image' => [
                'desktop' => [['name' => 'desktop', 'ratio' => 16 / 9]],
            ],
            'image' => [
                'desktop' => [['name' => 'desktop', 'ratio' => 4 / 3]],
                'mobile' => [['name' => 'mobile',  'ratio' => 4 / 3]],
            ],
            'logo' => [
                'default' => [['name' => 'default', 'ratio' => null]],
            ],
            'logo_white' => [
                'default' => [['name' => 'default', 'ratio' => null]],
            ],
            'og_image' => [
                'default' => [['name' => 'default', 'ratio' => 1200 / 630]],
            ],
            'avatar' => [
                'default' => [['name' => 'default', 'ratio' => 1]],
                'desktop' => [['name' => 'desktop', 'ratio' => 1]],
            ],
        ],

        'files' => [
            'favicon_file',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Glide Image Rendering (Thumbnail & Crops)
    |--------------------------------------------------------------------------
    */

    'glide' => [
        'base_path' => 'twill-img',
    ],
];
