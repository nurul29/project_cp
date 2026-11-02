<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import GradeTable from '@/Components/GradeTable.vue';
import PageHeader from '@/Components/PageHeader.vue';

defineProps({ grades: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="Nilai saya" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="Hasil belajar" title="Nilai saya" description="Rekap nilai dan skor dari kuis pilihan ganda yang telah kamu selesaikan." />
        <GradeTable v-if="grades.length" :grades="grades" />
        <EmptyState v-else title="Belum ada nilai kuis" description="Nilai akan otomatis muncul di sini segera setelah kamu menyelesaikan kuis pilihan ganda." icon="▥" />
    </AuthenticatedLayout>
</template>
