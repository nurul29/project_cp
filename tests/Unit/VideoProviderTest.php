<?php

namespace Tests\Unit;

use App\Enums\VideoProvider;
use PHPUnit\Framework\TestCase;

class VideoProviderTest extends TestCase
{
    public function test_video_provider_is_detected_from_the_exact_url_host(): void
    {
        $this->assertSame(VideoProvider::YouTube, VideoProvider::fromUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertSame(VideoProvider::YouTube, VideoProvider::fromUrl('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame(VideoProvider::Vimeo, VideoProvider::fromUrl('https://player.vimeo.com/video/12345'));
        $this->assertSame(VideoProvider::Direct, VideoProvider::fromUrl('https://cdn.example.com/lesson.mp4'));
        $this->assertSame(VideoProvider::Direct, VideoProvider::fromUrl('https://youtube.com.example.org/lesson.mp4'));
        $this->assertNull(VideoProvider::fromUrl('not a url'));
    }
}
