<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import LibraryMaterialCard from '@/Components/LibraryMaterialCard.vue';

defineProps({ materials: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="Rak saya" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-[944px]">
            <header class="mb-5">
                <p class="text-[10px] font-extrabold uppercase tracking-[.16em] text-[#0c7774]">Ruang siswa</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#193b3a] sm:text-3xl">Rak saya</h1>
                <p class="mt-2 text-xs leading-5 text-[#667b79]">Koleksi bacaan yang kamu simpan agar mudah ditemukan lagi.</p>
            </header>
            <p class="mb-3 text-[11px] font-bold text-[#193b3a]">{{ materials.length }} koleksi tersimpan</p>
            <div v-if="materials.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <LibraryMaterialCard v-for="material in materials" :key="material.id" :material="material" saved />
            </div>
            <EmptyState v-else title="Rakmu masih kosong" description="Simpan buku dari katalog dengan tombol hati, lalu koleksi itu akan muncul di sini." icon="♡" />
        </div>
    </AuthenticatedLayout>
</template>
