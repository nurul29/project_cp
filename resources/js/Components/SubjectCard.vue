<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ subject: { type: Object, required: true } });
const tone = (subject) => `subject-tone-${subject.tone || 'green'}`;
</script>

<template>
    <Link :href="route('siswa.library.index', { subject: subject.slug })" class="group rounded-2xl border border-[#e4eae7] bg-white p-5 transition hover:-translate-y-0.5 hover:border-[#b8dcd0] hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#0c7774]">
        <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-xl font-bold" :class="tone(subject)">{{ subject.icon }}</span>
        <h2 class="mt-4 font-bold text-[#193b3a] group-hover:text-[#0c7774]">{{ subject.name }}</h2>
        <p class="mt-1 line-clamp-2 min-h-10 text-xs leading-5 text-[#667b79]">{{ subject.description }}</p>
        <p v-if="subject.materials_count !== undefined" class="mt-3 text-[11px] font-semibold text-[#0c7774]">{{ subject.materials_count }} materi tersedia</p>
    </Link>
</template>
