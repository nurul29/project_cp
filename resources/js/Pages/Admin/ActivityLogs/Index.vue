<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    logs: { type: Object, default: () => ({ data: [] }) },
});
</script>

<template>
    <Head title="Riwayat Aktivitas — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Pengelolaan sekolah</div>
                <h1>Riwayat aktivitas</h1>
                <p>Catatan perubahan dan penelusuran aksi pada pengelolaan perpustakaan sekolah.</p>
            </div>
        </div>

        <section class="admin-activity-list">
            <div v-if="logs.data?.length">
                <article v-for="log in logs.data" :key="log.id">
                    <span class="timeline-dot"></span>
                    <div>
                        <strong>{{ log.text }}</strong>
                        <p>{{ log.actor }} <span>• {{ log.date }}</span></p>
                    </div>
                </article>
            </div>
            <p v-else class="muted" style="padding: 20px 0;">Belum ada riwayat aktivitas tercatat.</p>
        </section>
    </AuthenticatedLayout>
</template>
