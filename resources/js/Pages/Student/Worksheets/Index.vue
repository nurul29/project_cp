<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AssignmentLine from '@/Components/AssignmentLine.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';

defineProps({ assignments: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="Lembar kerja" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="Aktivitas kelas" title="Lembar kerja" description="Lihat instruksi, batas pengumpulan, dan status tugasmu." />
        <div v-if="assignments.length" class="space-y-3">
            <AssignmentLine v-for="assignment in assignments" :key="assignment.id" :item="assignment" />
        </div>
        <EmptyState v-else title="Belum ada lembar kerja" description="Saat guru memberikan tugas, tugas tersebut akan muncul di sini." icon="☷" />
    </AuthenticatedLayout>
</template>
