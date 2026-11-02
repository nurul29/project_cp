<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ item: { type: Object, required: true }, actionLabel: { type: String, default: 'Buka materi' } });
const tone = (item) => `subject-tone-${item.tone || 'green'}`;
</script>

<template>
    <article class="overflow-hidden rounded-2xl border border-[#e4eae7] bg-white">
        <div class="flex h-28 items-end justify-between p-4" :class="tone(item)">
            <span class="text-3xl">{{ item.icon }}</span>
            <span class="rounded-full bg-white/80 px-2.5 py-1 text-[10px] font-bold text-[#075f5d]">{{ item.coverText || item.type }}</span>
        </div>
        <div class="p-4">
            <p class="text-[11px] font-semibold text-[#0c7774]">{{ item.subject }}</p>
            <h2 class="mt-1 font-bold leading-5 text-[#193b3a]">{{ item.title }}</h2>
            <p v-if="item.description" class="mt-2 line-clamp-2 text-xs leading-5 text-[#667b79]">{{ item.description }}</p>
            <Link :href="item.href" class="mt-4 inline-flex min-h-10 items-center rounded-xl bg-[#e9f7f1] px-3 text-xs font-bold text-[#075f5d] hover:bg-[#d7eeea] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#0c7774]">{{ actionLabel }}</Link>
        </div>
    </article>
</template>
