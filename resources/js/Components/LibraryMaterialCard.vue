<script setup>
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    material: { type: Object, required: true },
    saved: { type: Boolean, default: false },
});

const typeLabel = (type) => ({
    book: 'Buku digital',
    reading: 'Bacaan',
    module: 'Modul',
}[type] ?? type);

const toggleSaved = () => router.post(
    route('siswa.shelf.toggle', props.material.id),
    {},
    { preserveScroll: true },
);
</script>

<template>
    <article class="flex min-h-[306px] flex-col rounded-2xl border border-[#e4eae7] bg-white p-3">
        <div class="relative flex h-32 shrink-0 items-end overflow-hidden rounded-xl p-4" :class="`subject-tone-${material.tone || 'green'}`">
            <div class="absolute -right-4 -top-8 h-28 w-28 rounded-full border-[14px] border-white/25"></div>
            <span class="relative text-lg font-black">{{ material.coverText || material.subject }}</span>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-1.5">
            <span
                class="rounded-md px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider"
                :class="material.collectionCategory === 'general' ? 'bg-[#f3e8ff] text-[#6b21a8]' : 'bg-[#e0f2fe] text-[#0369a1]'"
            >
                {{ material.collectionCategory === 'general' ? '📖 Bacaan Umum' : '📚 Buku Pelajaran' }}
            </span>
            <span v-if="material.author" class="text-[10px] font-medium text-[#526764]">
                Karya {{ material.author }}
            </span>
            <span v-else class="text-[10px] font-medium text-[#526764]">
                {{ material.subject }} · {{ typeLabel(material.type) }}
            </span>
        </div>
        <h2 class="mt-2 text-[13px] font-extrabold leading-5 text-[#193b3a]">{{ material.title }}</h2>
        <p class="mt-2 line-clamp-1 text-[10px] text-[#92a19c]">
            {{ material.lastReadAt ? `Dibaca ${new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(material.lastReadAt))}` : material.description || 'Koleksi bacaan' }}
        </p>
        <Link v-if="material.quizzes?.length" :href="material.quizzes[0].href" class="mt-2 inline-flex w-fit items-center rounded-full bg-[#fff4df] px-2.5 py-1 text-[10px] font-bold text-[#76531b] hover:bg-[#ffedcf]">
            ✳ {{ material.quizzes.length }} kuis terkait
        </Link>
        <div class="mt-auto flex items-center justify-between gap-2 border-t border-[#edf1ee] pt-3">
            <Link :href="material.href" class="inline-flex min-h-8 items-center rounded-lg bg-[#e9f7f1] px-3 text-[10px] font-extrabold text-[#075f5d] hover:bg-[#d7eeea]">Baca sekarang →</Link>
            <button
                type="button"
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-[#e4eae7] text-base leading-none text-[#0c7774] hover:bg-[#e9f7f1]"
                :aria-label="saved ? `Hapus ${material.title} dari rak` : `Simpan ${material.title} ke rak`"
                :aria-pressed="saved"
                @click="toggleSaved"
            >{{ saved ? '♥' : '♡' }}</button>
        </div>
    </article>
</template>
