<?php

namespace App\Enums;

enum VideoProvider: string
{
    case YouTube = 'youtube';
    case Vimeo = 'vimeo';
    case Direct = 'direct';

    public function label(): string
    {
        return match ($this) {
            self::YouTube => 'YouTube',
            self::Vimeo => 'Vimeo',
            self::Direct => 'Video Langsung (MP4 / WebM)',
        };
    }

    /**
     * Detect provider from a URL string.
     */
    public static function fromUrl(?string $url): ?self
    {
        if (empty($url)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host)) {
            return null;
        }

        $host = strtolower(preg_replace('/^www\./', '', $host));

        if (in_array($host, ['youtube.com', 'm.youtube.com', 'youtu.be', 'youtube-nocookie.com'], true)) {
            return self::YouTube;
        }

        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            return self::Vimeo;
        }

        return self::Direct;
    }
}
