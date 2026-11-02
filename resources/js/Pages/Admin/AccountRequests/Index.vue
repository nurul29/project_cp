<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    requests: { type: Array, default: () => [] },
});

const reviewingRequest = ref(null);

const form = useForm({
    status: 'resolved',
    notes: '',
});

const openReview = (req) => {
    reviewingRequest.value = req;
    form.status = 'resolved';
    form.notes = req.notes || 'Identitas siswa/guru telah diverifikasi sesuai prosedur sekolah.';
};

const submitResolve = () => {
    if (!reviewingRequest.value) return;
    form.post(route('admin.account-requests.resolve', reviewingRequest.value.id), {
        onSuccess: () => {
            reviewingRequest.value = null;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Bantuan Akun — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Pengelolaan sekolah</div>
                <h1>Bantuan akun</h1>
                <p>Tinjau permohonan pemulihan kata sandi dan verifikasi identitas pengguna sekolah.</p>
            </div>
        </div>

        <div class="admin-inline-note">
            <span aria-hidden="true" style="font-size: 16px;">ⓘ</span>
            <p>
                Jangan meminta atau membagikan kata sandi melalui pesan tidak terenkripsi. Verifikasi identitas pemohon secara langsung atau melalui wali kelas sebelum mengatur ulang akses akun.
            </p>
        </div>

        <div v-if="requests.length" class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Pemohon</th>
                        <th>Jenis Bantuan</th>
                        <th>Waktu Masuk</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in requests" :key="r.id">
                        <td>
                            <strong>{{ r.username }}</strong>
                            <small class="cell-meta">{{ r.name }}</small>
                        </td>
                        <td>{{ r.type }}</td>
                        <td>{{ r.date }}</td>
                        <td>
                            <span class="admin-badge" :class="r.status === 'resolved' ? 'good' : 'warning'">
                                {{ r.status === 'resolved' ? 'Selesai' : 'Perlu ditinjau' }}
                            </span>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="admin-btn"
                                :class="r.status === 'pending' ? 'solid' : ''"
                                @click="openReview(r)"
                            >
                                {{ r.status === 'pending' ? 'Tinjau' : 'Lihat catatan' }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="muted">Tidak ada permohonan bantuan akun yang tercatat.</p>

        <!-- Modal Tinjau Permohonan -->
        <div v-if="reviewingRequest" class="reader-dialog" style="display: block; position: fixed; inset: 0; z-index: 100; margin: auto; max-width: 500px;">
            <div class="reader-top">
                <h2>Tinjau permohonan bantuan</h2>
                <button type="button" class="close-reader" @click="reviewingRequest = null">×</button>
            </div>

            <div style="background: #f8faf7; border: 1px solid #e1e9e4; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                <p style="margin: 0; font-size: 13px;">
                    Akun: <b>{{ reviewingRequest.username }}</b> ({{ reviewingRequest.name }})<br>
                    Jenis: {{ reviewingRequest.type }}<br>
                    Waktu: {{ reviewingRequest.date }}
                </p>
            </div>

            <form @submit.prevent="submitResolve" style="display: grid; gap: 14px;">
                <div class="field" style="margin: 0;">
                    <label>Keputusan penanganan *</label>
                    <select v-model="form.status">
                        <option value="resolved">Selesai (Identitas terverifikasi)</option>
                        <option value="rejected">Tolak permohonan</option>
                    </select>
                </div>

                <div class="field" style="margin: 0;">
                    <label>Catatan penanganan admin</label>
                    <textarea v-model="form.notes" style="min-height: 80px;" placeholder="Tuliskan catatan verifikasi..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="secondary" @click="reviewingRequest = null">Batal</button>
                    <button type="submit" class="primary" :disabled="form.processing">Simpan Hasil Tinjauan</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
