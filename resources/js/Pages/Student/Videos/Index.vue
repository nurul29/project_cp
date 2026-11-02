<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ContentCard from '@/Components/ContentCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';

defineProps({ videos: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="Video belajar" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="Tonton dan pahami" title="Video belajar" description="Video pembelajaran yang dibagikan gurumu untuk kelas." />
        <div v-if="videos.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <ContentCard v-for="video in videos" :key="video.id" :item="video" action-label="Tonton video" />
        </div>
        <EmptyState v-else title="Belum ada video belajar" description="Video baru dari gurumu akan muncul di sini." icon="▷" />
    </AuthenticatedLayout>
</template>
