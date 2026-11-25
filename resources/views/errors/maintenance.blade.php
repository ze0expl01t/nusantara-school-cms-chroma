@include(\x27errors.layout\x27, [
    \x27statusCode\x27 => 503,
    \x27badgeLabel\x27 => \x27Maintenance Mode\x27,
    \x27title\x27 => $title ?? \x27Pemeliharaan Sistem\x27,
    \x27desc\x27 => $message ?? \x27Kami sedang melakukan pemeliharaan rutin. Silakan kembali beberapa saat lagi.\x27,
    \x27extraInfo\x27 => $eta ?? null,
    \x27showCode\x27 => false,
    \x27hideActions\x27 => true,
])