<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SubjectCard from '@/Components/SubjectCard.vue';

defineProps({ subjects: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="Mata pelajaran" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="Jelajahi" title="Mata pelajaran" description="Pilih pelajaran untuk menemukan modul, bacaan, dan materi yang dibagikan gurumu." />
        <div v-if="subjects.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <SubjectCard v-for="subject in subjects" :key="subject.id" :subject="subject" />
        </div>
        <EmptyState v-else title="Mata pelajaran belum tersedia" description="Silakan kembali lagi nanti." />
    </AuthenticatedLayout>
</template>
