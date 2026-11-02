<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, defineAsyncComponent } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const CollectionReader = defineAsyncComponent(() => import('@/Components/CollectionReader.vue'));
const VideoPlayer = defineAsyncComponent(() => import('@/Components/VideoPlayer.vue'));

const props = defineProps({ material: { type: Object, required: true } });
const pdfSource = computed(() => {
    const source = props.material.fileUrl || props.material.url;
    return source && /\.pdf(?:$|[?#])/i.test(source) ? source : null;
});
</script>

<template>
    <Head :title="material.title" />
    <AuthenticatedLayout>
        <PageHeader :eyebrow="material.subject" :title="material.title" :description="material.description || 'Materi belajar yang dibagikan untuk kelasmu.'" />
        <CollectionReader
            v-if="pdfSource"
            :source="pdfSource"
            :title="material.title"
            :description="material.description"
            :quizzes="material.quizzes || []"
        />
        <article v-else class="overflow-hidden rounded-3xl border border-[#e4eae7] bg-white">
            <VideoPlayer
                v-if="material.type === 'video'"
                :url="material.url"
                :file-url="material.fileUrl"
                :provider="material.videoProvider"
                :title="material.title"
            />
            <div v-else class="p-6 sm:p-10">
                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-[#e9f7f1] text-4xl">{{ material.icon }}</div>
                <p class="mt-6 text-sm leading-7 text-[#667b79]">{{ material.description || 'Buka tautan materi dari gurumu untuk mulai belajar.' }}</p>
                <a v-if="material.url" :href="material.url" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-[#0c7774] px-5 text-sm font-bold text-white hover:bg-[#075f5d]">Buka materi di tab baru ↗</a>
                <a v-else-if="material.fileUrl" :href="material.fileUrl" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-[#0c7774] px-5 text-sm font-bold text-white hover:bg-[#075f5d]">Buka file ↗</a>
                <p v-else class="mt-5 rounded-xl bg-[#fff4df] p-4 text-sm text-[#76531b]">File materi belum ditautkan. Hubungi gurumu untuk informasi lebih lanjut.</p>
            </div>
        </article>
        <section v-if="material.quizzes?.length && !pdfSource" class="mt-5 rounded-2xl border border-[#e4eae7] bg-white p-5">
            <h2 class="text-sm font-extrabold text-[#193b3a]">Kuis terkait</h2>
            <ul class="mt-3 space-y-2">
                <li v-for="quiz in material.quizzes" :key="quiz.id">
                    <Link :href="quiz.href" class="inline-flex min-h-10 items-center rounded-lg bg-[#e9f7f1] px-3 text-xs font-bold text-[#075f5d] hover:bg-[#d7eeea]">{{ quiz.title }} →</Link>
                </li>
            </ul>
        </section>
        <Link :href="route('siswa.catalog.index', { subject: material.subjectSlug })" class="mt-5 inline-flex text-sm font-bold text-[#0c7774] hover:underline">← Kembali ke perpustakaan {{ material.subject }}</Link>
    </AuthenticatedLayout>
</template>
