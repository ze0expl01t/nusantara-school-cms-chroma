@twillBlockTitle('Fasilitas')
@twillBlockIcon('image-text')
@twillBlockGroup('app')

@formField('input', [
    'name' => 'title_main',
    'label' => 'Judul Utama (Contoh: Pendidikan)',
    'translatable' => true
])

@formField('input', [
    'name' => 'title_highlight',
    'label' => 'Judul Sorotan/Hijau (Contoh: & Fasilitas)',
    'translatable' => true
])

@formField('input', [
    'name' => 'subtitle',
    'label' => 'Subjudul (Opsional)',
    'translatable' => true
])

{{-- Memanggil repeater untuk card fasilitas --}}
@formField('repeater', ['type' => 'card_item'])
