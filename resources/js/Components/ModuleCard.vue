<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ subject: { type: Object, required: true } });
const tone = (subject) => `subject-tone-${subject.tone || 'green'}`;
</script>

<template>
    <Link :href="route('siswa.library.index', { subject: subject.slug })" class="flex items-center gap-3 rounded-2xl border border-[#e4eae7] bg-white p-4 transition hover:border-[#b8dcd0] hover:shadow-sm">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg" :class="tone(subject)">{{ subject.icon }}</span>
        <span class="min-w-0">
            <span class="block truncate text-sm font-bold">{{ subject.name }}</span>
            <span class="block truncate text-xs text-[#667b79]">Lihat materi belajar</span>
        </span>
        <span class="ml-auto text-[#0c7774]" aria-hidden="true">→</span>
    </Link>
</template>
