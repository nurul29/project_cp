<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusPill from '@/Components/StatusPill.vue';

defineProps({ items: { type: Array, default: () => [] } });
const dateLabel = (date) => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(date));
</script>

<template>
    <Head title="Portofolio" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="Kumpulan karya" title="Portofolio" description="Lihat tugas dan karya belajarmu beserta status penilaiannya." />
        <div v-if="items.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article v-for="item in items" :key="item.id" class="rounded-2xl border border-[#e4eae7] bg-white p-5">
                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#e9f7f1] text-xl">{{ item.icon }}</span>
                    <StatusPill :status="item.status" />
                </div>
                <p class="mt-4 text-xs font-bold text-[#0c7774]">{{ item.subject }} · {{ item.type }}</p>
                <h2 class="mt-1 font-extrabold">{{ item.title }}</h2>
                <p class="mt-3 text-xs text-[#667b79]">Ditambahkan {{ dateLabel(item.createdAt) }}</p>
                <p v-if="item.score !== null" class="mt-3 text-sm font-bold text-[#075f5d]">Nilai: {{ item.score }}</p>
            </article>
        </div>
        <EmptyState v-else title="Portofoliomu masih kosong" description="Tugas yang kamu kumpulkan akan muncul di sini." icon="▧" />
    </AuthenticatedLayout>
</template>
