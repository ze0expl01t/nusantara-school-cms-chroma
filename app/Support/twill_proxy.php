<?php

use Illuminate\Support\Collection;
use ImageProxy\Services\ImageProxyManager;

/**
 * =====================================================================
 * TWILL PROXY HELPERS — Responsive WebP Images
 * =====================================================================
 *
 * Helper untuk render `<img>` dari Twill model dengan:
 *   - Konversi WebP otomatis (via rafoabbas/laravel-image-proxy)
 *   - Responsive srcset multi-ukuran
 *   - Support extra HTML attributes (class, onerror, dll.)
 *   - Fallback URL jika media tidak ada
 *
 * @see  https://github.com/rafoabbas/laravel-image-proxy
 * =====================================================================
 */

/**
 * ---------------------------------------------------------------------
 * Ambil Media object dari Twill model berdasarkan role.
 * ---------------------------------------------------------------------
 *
 * Twill v3.x menyimpan media di relationship `medias` dengan pivot role.
 *
 * @param  mixed  $model  Twill model (Block, Page, dll.)
 * @param  string  $role  Media role ('cover', 'image', 'hero_image', dll.)
 * @return object|null Media object atau null jika tidak ada
 */
if (! function_exists('twill_media')) {
    function twill_media($model, string $role)
    {
        if (! $model || ! isset($model->medias)) {
            return null;
        }

        $medias = $model->medias;

        if ($medias instanceof Collection) {
            return $medias->first(function ($m) use ($role) {
                return ($m->pivot->role ?? null) === $role;
            });
        }

        return null;
    }
}

/**
 * ---------------------------------------------------------------------
 * Ambil path relatif dari Media.
 * ---------------------------------------------------------------------
 *
 * Twill v3.x menyimpan `uuid + filename` di field `uuid`.
 * Contoh: uuid = "93ef00db-.../bg2.jpg" → path = "uploads/93ef00db-.../bg2.jpg"
 *
 * @param  object|null  $media
 * @return string|null
 */
if (! function_exists('twill_media_path')) {
    function twill_media_path($media): ?string
    {
        if (! $media || empty($media->uuid)) {
            return null;
        }

        return 'uploads/'.ltrim($media->uuid, '/');
    }
}

/**
 * ---------------------------------------------------------------------
 * Render tag <img> dengan fallback simple (jika media belum ada).
 * ---------------------------------------------------------------------
 *
 * @param  string  $src  URL gambar
 * @param  string  $alt  Alt text
 * @param  array  $attrs  HTML attributes (class, loading, dll.)
 * @return string HTML <img>
 */
if (! function_exists('twill_img_fallback')) {
    function twill_img_fallback(string $src, string $alt = '', array $attrs = []): string
    {
        if (empty($src)) {
            return '';
        }

        $html = '<img src="'.e($src).'" alt="'.e($alt).'"';
        foreach ($attrs as $k => $v) {
            $html .= ' '.e($k).'="'.e($v).'"';
        }
        $html .= '>';

        return $html;
    }
}

/**
 * ---------------------------------------------------------------------
 * Render responsive WebP <img> dari Twill model + role.
 * ---------------------------------------------------------------------
 *
 * @param  mixed  $model  Twill model (Block, Page, dll.)
 * @param  string  $role  Media role
 * @param  array  $widths  Widths untuk srcset (default: [640, 1024, 1536])
 * @param  string  $sizes  sizes attribute (default: '100vw')
 * @param  string  $alt  Alt text (fallback ke media alt_text)
 * @param  array  $options  Options untuk image-proxy (format, fit, dll.)
 * @param  string|null  $fallbackUrl  URL fallback (mis. Unsplash)
 * @param  array  $attributes  Extra HTML attributes (class, onerror, id, dll.)
 * @return string HTML <img> atau string kosong
 */
if (! function_exists('twill_proxy_img')) {
    function twill_proxy_img(
        $model,
        string $role,
        array $widths = [640, 1024, 1536],
        string $sizes = '100vw',
        string $alt = '',
        array $options = [],
        ?string $fallbackUrl = null,
        array $attributes = []
    ): string {
        $alt = (string) ($alt ?? '');
        $media = twill_media($model, $role);

        // ─── Fallback: tidak ada media ─────────────────────────────
        if (! $media) {
            if ($fallbackUrl) {
                // Merge default attributes
                $fallbackAttrs = array_merge(
                    ['loading' => 'lazy', 'decoding' => 'async'],
                    $attributes
                );

                return twill_img_fallback($fallbackUrl, $alt, $fallbackAttrs);
            }

            return '';
        }

        $path = twill_media_path($media);
        if (! $path) {
            return '';
        }

        // ─── Auto-alt dari media ───────────────────────────────────
        if (empty($alt)) {
            $alt = $media->alt_text ?? $media->title ?? '';
        }

        try {
            $proxy = app(ImageProxyManager::class);

            // Default options: format WebP
            $defaultOpts = ['format' => 'webp'];
            $mergedOpts = array_merge($defaultOpts, $options);

            // Render base HTML
            $html = $proxy->srcsetTag($path, $widths, $mergedOpts, $sizes, $alt);

            // auto-inject width/height dari media (untuk CLS)
            if (! empty($media->width) && ! empty($media->height)) {
                if (! preg_match('/\swidth=/', $html)) {
                    $html = preg_replace(
                        '/<img\s/',
                        '<img width="'.(int) $media->width.'" height="'.(int) $media->height.'" ',
                        $html,
                        1
                    );
                }
            }

            // ─── Inject extra attributes ───────────────────────────
            if (! empty($attributes)) {
                foreach ($attributes as $key => $val) {
                    $key = e($key);
                    $val = e($val);

                    // Jika attribute sudah ada di tag, replace value-nya
                    if (preg_match('/\s'.preg_quote($key, '/').'="[^"]*"/', $html)) {
                        $html = preg_replace(
                            '/\s'.preg_quote($key, '/').'="[^"]*"/',
                            ' '.$key.'="'.$val.'"',
                            $html,
                            1
                        );
                    } else {
                        // Tambah attribute baru setelah `<img `
                        $html = preg_replace(
                            '/<img\s/',
                            '<img '.$key.'="'.$val.'" ',
                            $html,
                            1
                        );
                    }
                }
            }

            return $html;
        } catch (Throwable $e) {
            Log::warning('twill_proxy_img failed', [
                'role' => $role,
                'path' => $path,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Fallback ke URL original kalau proxy error
            if ($fallbackUrl) {
                return twill_img_fallback($fallbackUrl, $alt, $attributes);
            }

            return '';
        }
    }
}

/**
 * ---------------------------------------------------------------------
 * Render <picture> dengan WebP + AVIF + JPEG fallback.
 * ---------------------------------------------------------------------
 *
 * Lebih optimal untuk browser compatibility maksimal.
 *
 * @param  mixed  $model
 * @param  string  $role
 * @param  array  $widths
 * @param  string  $sizes
 * @param  string  $alt
 * @param  string|null  $fallbackUrl
 * @param  array  $attributes
 * @return string
 */
if (! function_exists('twill_proxy_picture')) {
    function twill_proxy_picture(
        $model,
        string $role,
        array $widths = [640, 1024, 1536],
        string $sizes = '100vw',
        string $alt = '',
        ?string $fallbackUrl = null,
        array $attributes = []
    ): string {
        $media = twill_media($model, $role);

        if (! $media) {
            if ($fallbackUrl) {
                return twill_img_fallback($fallbackUrl, $alt, $attributes);
            }

            return '';
        }

        $path = twill_media_path($media);
        if (! $path) {
            return '';
        }

        if (empty($alt)) {
            $alt = $media->alt_text ?? $media->title ?? '';
        }

        try {
            $proxy = app(ImageProxyManager::class);

            // Bangun srcset untuk AVIF + WebP
            $buildSrcset = function (string $format) use ($proxy, $path, $widths) {
                $items = [];
                foreach ($widths as $w) {
                    $url = $proxy->url($path, ['format' => $format, 'w' => $w]);
                    $items[] = $url.' '.$w.'w';
                }

                return implode(', ', $items);
            };

            $avifSrcset = $buildSrcset('avif');
            $webpSrcset = $buildSrcset('webp');
            $jpgUrl = $proxy->url($path, ['format' => 'jpg', 'w' => end($widths)]);

            // Build class / extra attrs
            $extraAttrs = '';
            foreach ($attributes as $k => $v) {
                $extraAttrs .= ' '.e($k).'="'.e($v).'"';
            }

            return <<<HTML
<picture>
    <source srcset="{$avifSrcset}" type="image/avif" sizes="{$sizes}">
    <source srcset="{$webpSrcset}" type="image/webp" sizes="{$sizes}">
    <img src="{$jpgUrl}" srcset="{$webpSrcset}" sizes="{$sizes}" alt="{$alt}" loading="lazy" decoding="async"{$extraAttrs}>
</picture>
HTML;
        } catch (Throwable $e) {
            Log::warning('twill_proxy_picture failed', [
                'role' => $role,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            if ($fallbackUrl) {
                return twill_img_fallback($fallbackUrl, $alt, $attributes);
            }

            return '';
        }
    }
}
