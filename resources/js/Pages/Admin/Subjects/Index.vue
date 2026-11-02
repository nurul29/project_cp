<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    subjects: { type: Array, default: () => [] },
});

const showModal = ref(false);
const editingSubject = ref(null);

const form = useForm({
    name: '',
    code: '',
    description: '',
});

const openCreateModal = () => {
    editingSubject.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (s) => {
    editingSubject.value = s;
    form.name = s.name;
    form.code = s.code;
    form.description = s.description || '';
    showModal.value = true;
};

const submit = () => {
    if (editingSubject.value) {
        form.put(route('admin.subjects.update', editingSubject.value.id), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.subjects.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const deleteSubject = (s) => {
    if (confirm(`Yakin ingin menghapus mata pelajaran "${s.name}"?`)) {
        router.delete(route('admin.subjects.destroy', s.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Mata Pelajaran — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Pengelolaan sekolah</div>
                <h1>Mata pelajaran</h1>
                <p>Lengkapi daftar pelajaran dan klasifikasi kurikulum perpustakaan.</p>
            </div>
            <div class="page-actions">
                <button type="button" class="admin-btn solid" @click="openCreateModal">
                    + Tambah pelajaran
                </button>
            </div>
        </div>

        <div v-if="subjects.length" class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 120px;">Kode</th>
                        <th>Mata pelajaran</th>
                        <th>Guru pengampu</th>
                        <th style="width: 140px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in subjects" :key="s.id">
                        <td>
                            <span class="admin-code">{{ s.code }}</span>
                        </td>
                        <td>
                            <strong>{{ s.name }}</strong>
                        </td>
                        <td>{{ s.teacher }}</td>
                        <td>
                            <div class="row-actions">
                                <button type="button" class="admin-btn" @click="openEditModal(s)">Edit</button>
                                <button type="button" class="admin-btn danger" @click="deleteSubject(s)">Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="muted">Belum ada mata pelajaran.</p>

        <!-- Modal Tambah / Edit Mata Pelajaran -->
        <div v-if="showModal" class="reader-dialog" style="display: block; position: fixed; inset: 0; z-index: 100; margin: auto; max-width: 480px;">
            <div class="reader-top">
                <h2>{{ editingSubject ? 'Edit mata pelajaran' : 'Tambah mata pelajaran' }}</h2>
                <button type="button" class="close-reader" @click="showModal = false">×</button>
            </div>

            <form @submit.prevent="submit" style="display: grid; gap: 14px;">
                <div class="field" style="margin: 0;">
                    <label>Nama mata pelajaran *</label>
                    <input v-model="form.name" type="text" placeholder="Contoh: Matematika" required />
                    <p v-if="form.errors.name" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ form.errors.name }}</p>
                </div>

                <div class="field" style="margin: 0;">
                    <label>Kode mata pelajaran *</label>
                    <input v-model="form.code" type="text" placeholder="Contoh: MP01, MTK" required />
                    <p v-if="form.errors.code" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ form.errors.code }}</p>
                </div>

                <div class="field" style="margin: 0;">
                    <label>Deskripsi singkat</label>
                    <textarea v-model="form.description" placeholder="Fokus materi atau bidang ilmu..." style="min-height: 80px;"></textarea>
                    <p v-if="form.errors.description" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ form.errors.description }}</p>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="secondary" @click="showModal = false">Batal</button>
                    <button type="submit" class="primary" :disabled="form.processing">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
