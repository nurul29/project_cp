<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    url: { type: String, default: '' },
    fileUrl: { type: String, default: '' },
    provider: { type: String, default: null },
    title: { type: String, required: true },
});

const useEmbedFallback = ref(false);

watch(() => [props.url, props.fileUrl], () => {
    useEmbedFallback.value = false;
});

const extractYoutubeId = (rawUrl) => {
    if (!rawUrl) return null;
    const match = rawUrl.match(/(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/|v\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i);
    return match ? match[1] : null;
};

const playback = computed(() => {
    if (props.fileUrl && /\.(mp4|webm|ogg|mov)(?:$|[?#])/i.test(props.fileUrl)) {
        return {
            type: 'direct',
            src: props.fileUrl,
            embedSrc: null,
        };
    }

    if (!props.url) return { type: 'unavailable' };

    const ytId = extractYoutubeId(props.url);
    if (ytId) {
        return {
            type: 'embed',
            embedSrc: `https://www.youtube.com/embed/${ytId}?rel=0&modestbranding=1&playsinline=1`,
        };
    }

    try {
        const parsed = new URL(props.url, window.location.origin);
        if (/\.(mp4|webm|ogg|mov)$/i.test(parsed.pathname)) {
            return {
                type: 'direct',
                src: parsed.toString(),
                embedSrc: null,
            };
        }

        const host = parsed.hostname.toLowerCase().replace(/^www\./, '');
        if (host === 'vimeo.com' || host === 'player.vimeo.com') {
            const videoId = parsed.pathname.split('/').filter((segment) => /^\d+$/.test(segment)).pop();
            if (videoId) {
                return {
                    type: 'embed',
                    embedSrc: `https://player.vimeo.com/video/${videoId}`,
                };
            }
        }

        return { type: 'link', href: parsed.toString() };
    } catch {
        return { type: 'unavailable' };
    }
});

const handleVideoError = () => {
    if (playback.value.embedSrc) {
        useEmbedFallback.value = true;
    }
};
</script>

<template>
    <div class="overflow-hidden bg-[#0f2322]">
        <div
            v-if="playback.type === 'youtube' || playback.type === 'direct'"
            class="flex items-center justify-between border-b border-white/10 bg-[#132c2b] px-4 py-2 text-xs text-white/90"
        >
            <span class="font-bold text-[#6ee7b7]">🎬 Pemutar Video SUNDIGITAL</span>
            <button
                v-if="playback.embedSrc"
                type="button"
                class="rounded-lg border border-white/20 bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-white/20"
                @click="useEmbedFallback = !useEmbedFallback"
            >
                {{ useEmbedFallback ? 'Gunakan Pemutar Langsung (MP4)' : 'Gunakan Mode Embed' }}
            </button>
        </div>

        <div class="aspect-video bg-black">
            <video
                v-if="(playback.type === 'direct' || (playback.type === 'youtube' && !useEmbedFallback)) && playback.src"
                :key="playback.src"
                class="h-full w-full bg-black"
                controls
                playsinline
                preload="metadata"
                @error="handleVideoError"
            >
                <source :src="playback.src" type="video/mp4" />
                Browser tidak mendukung pemutaran video ini.
            </video>

            <iframe
                v-else-if="playback.type === 'embed' || (playback.type === 'youtube' && useEmbedFallback)"
                :src="playback.embedSrc"
                :title="title"
                class="h-full w-full border-0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
            ></iframe>

            <div v-else class="flex h-full items-center justify-center p-6 text-center text-sm text-white">
                <a
                    v-if="playback.type === 'link'"
                    :href="playback.href"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="rounded-xl bg-white px-5 py-3 font-bold text-[#075f5d] hover:bg-[#e9f7f1]"
                >
                    Buka video ↗
                </a>
                <p v-else>Video tidak dapat diputar. Periksa tautan atau hubungi gurumu.</p>
            </div>
        </div>
    </div>
</template>
