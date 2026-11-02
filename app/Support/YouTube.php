<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class YouTube
{
    /**
     * Ambil 11 karakter ID video dari berbagai format tautan YouTube.
     */
    public static function extractId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url)) {
            return $url;
        }

        if (preg_match('/(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/|v\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * URL pemutar embed resmi YouTube (diputar di dalam halaman SUNDIGITAL).
     */
    public static function embedUrl(?string $url): ?string
    {
        $id = self::extractId($url);

        return $id ? "https://www.youtube.com/embed/{$id}?rel=0&modestbranding=1&playsinline=1" : null;
    }

    /**
     * Cek lewat oEmbed resmi apakah pemilik video mengizinkan video diputar di situs lain.
     * Mengembalikan true jika tidak bisa dicek (jaringan gagal) agar tidak memblokir guru.
     */
    public static function isEmbeddable(?string $url): bool
    {
        $id = self::extractId($url);
        if (! $id) {
            return true;
        }

        return Cache::remember("yt_embeddable_{$id}", now()->addHours(6), function () use ($id) {
            $endpoint = 'https://www.youtube.com/oembed?format=json&url='
                .urlencode("https://www.youtube.com/watch?v={$id}");

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT => 4,
            ]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // 401/403 = embed dinonaktifkan pemilik, 404 = video tidak ada / privat
            return ! in_array($code, [401, 403, 404], true);
        });
    }
}
