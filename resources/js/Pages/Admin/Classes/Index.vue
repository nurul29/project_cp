<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    classrooms: { type: Array, default: () => [] },
    teachers: { type: Array, default: () => [] },
    schoolYears: { type: Array, default: () => [] },
    selectedSchoolYear: { type: String, default: 'Semua' },
});

const showModal = ref(false);
const editingClass = ref(null);
const blockedDeleteClass = ref(null);
const currentYearFilter = ref(props.selectedSchoolYear || 'Semua');

const filterByYear = () => {
    router.get(route('admin.classes.index'), {
        school_year: currentYearFilter.value !== 'Semua' ? currentYearFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const form = useForm({
    grade_level: 'VII',
    name: '',
    homeroom_teacher_id: '',
    academic_year: '2026/2027',
});

const academicYears = ['2024/2025', '2025/2026', '2026/2027', '2027/2028', '2028/2029'];

const onGradeChange = () => {
    if (!form.name || form.name.startsWith('VII') || form.name.startsWith('VIII') || form.name.startsWith('IX')) {
        const suffix = form.name.replace(/^(VII|VIII|IX)\s*/, '');
        form.name = form.grade_level + (suffix ? ' ' + suffix : ' A');
    }
};

const openCreateModal = () => {
    editingClass.value = null;
    form.reset();
    form.grade_level = 'VII';
    form.name = 'VII A';
    form.academic_year = '2026/2027';
    showModal.value = true;
};

const openEditModal = (c) => {
    editingClass.value = c;
    form.grade_level = c.grade_level || (c.name.startsWith('VIII') ? 'VIII' : (c.name.startsWith('IX') ? 'IX' : 'VII'));
    form.name = c.name;
    form.homeroom_teacher_id = c.homeroom_teacher_id || '';
    form.academic_year = c.academic_year || '2026/2027';
    showModal.value = true;
};

const submit = () => {
    if (editingClass.value) {
        form.put(route('admin.classes.update', editingClass.value.id), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.classes.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const deleteClass = (c) => {
    if (c.delete_reasons && c.delete_reasons.length > 0) {
        blockedDeleteClass.value = c;
        return;
    }

    if (confirm(`Yakin ingin menghapus kelas "${c.name}"? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(route('admin.classes.destroy', c.id), { preserveScroll: true });
    }
};

const toggleActiveClass = (c) => {
    const actionName = c.is_active ? 'menonaktifkan / mengarsipkan' : 'mengaktifkan kembali';
    if (confirm(`Yakin ingin ${actionName} kelas "${c.name}"?`)) {
        router.post(route('admin.classes.toggle-active', c.id), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Kelas & Wali Kelas — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Pengelolaan sekolah</div>
                <h1>Kelas &amp; wali kelas</h1>
                <p>Atur kelompok belajar dan penanggung jawab kelas untuk distribusi materi.</p>
            </div>
            <div class="page-actions" style="display: flex; gap: 10px; align-items: center;">
                <select v-model="currentYearFilter" @change="filterByYear" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; background: white;">
                    <option value="Semua">Semua Tahun Ajaran</option>
                    <option v-for="yr in schoolYears" :key="yr" :value="yr">{{ yr }}</option>
                </select>
                <button type="button" class="admin-btn solid" @click="openCreateModal">
                    + Tambah kelas
                </button>
            </div>
        </div>

        <!-- Interactive Card Grid matching Mockup -->
        <div v-if="classrooms.length" class="admin-card-grid">
            <article
                v-for="c in classrooms"
                :key="c.id"
                class="admin-class-card"
            >
                <div class="admin-card-top">
                    <span class="class-symbol" aria-hidden="true">▦</span>
                    <span class="admin-badge" :class="c.is_active ? 'good' : 'muted'" style="font-size: 11px;">
                        {{ c.is_active ? 'Aktif' : 'Nonaktif / Arsip' }}
                    </span>
                </div>

                <h2>{{ c.name }}</h2>
                <p>Wali kelas: <b>{{ c.teacher }}</b></p>

                <div class="class-meta">
                    <strong>{{ c.student_count }}</strong>
                    <span>siswa aktif<br>{{ c.academic_year }}</span>
                </div>

                <div class="admin-card-actions">
                    <button type="button" class="admin-btn" @click="openEditModal(c)">
                        Edit
                    </button>
                    <button type="button" class="admin-btn" :style="c.is_active ? 'color: #d97706;' : 'color: #059669;'" @click="toggleActiveClass(c)">
                        {{ c.is_active ? 'Arsipkan' : 'Aktifkan' }}
                    </button>
                    <button type="button" class="admin-btn danger" @click="deleteClass(c)">
                        Hapus
                    </button>
                </div>
            </article>
        </div>
        <p v-else class="muted">Belum ada kelas yang terdaftar.</p>

        <!-- Modal Tambah / Edit Kelas -->
        <div v-if="showModal" class="reader-dialog" style="display: block; position: fixed; inset: 0; z-index: 100; margin: auto; max-width: 480px;">
            <div class="reader-top">
                <h2>{{ editingClass ? 'Edit data kelas' : 'Tambah rombongan belajar' }}</h2>
                <button type="button" class="close-reader" @click="showModal = false">×</button>
            </div>

            <form @submit.prevent="submit" style="display: grid; gap: 14px;">
                <div class="field" style="margin: 0;">
                    <label>Tingkat kelas *</label>
                    <select v-model="form.grade_level" @change="onGradeChange" required>
                        <option value="VII">Kelas VII (Tujuh)</option>
                        <option value="VIII">Kelas VIII (Delapan)</option>
                        <option value="IX">Kelas IX (Sembilan)</option>
                    </select>
                </div>

                <div class="field" style="margin: 0;">
                    <label>Nama kelas / rombel *</label>
                    <input v-model="form.name" type="text" placeholder="Contoh: VII A, VIII B" required />
                    <small style="font-size: 11px; color: #667b79;">Huruf rombel (A, B, C, dst.) dapat diubah sesuai kebutuhan.</small>
                </div>

                <div class="field" style="margin: 0;">
                    <label>Wali kelas</label>
                    <select v-model="form.homeroom_teacher_id">
                        <option value="">Pilih guru...</option>
                        <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>

                <div class="field" style="margin: 0;">
                    <label>Tahun ajaran *</label>
                    <select v-model="form.academic_year" required>
                        <option v-for="yr in academicYears" :key="yr" :value="yr">{{ yr }}</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="secondary" @click="showModal = false">Batal</button>
                    <button type="submit" class="primary" :disabled="form.processing">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <!-- Modal Alasan Penghapusan Ditolak -->
        <div v-if="blockedDeleteClass" class="reader-dialog" style="display: block; position: fixed; inset: 0; z-index: 100; margin: auto; max-width: 520px; background: white; padding: 24px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #dc2626; margin: 0;">Penghapusan Kelas Ditolak</h3>
                <button type="button" style="background: none; border: none; font-size: 20px; cursor: pointer;" @click="blockedDeleteClass = null">×</button>
            </div>
            <p style="font-size: 13px; color: #475569; margin-bottom: 14px;">
                Kelas <b>{{ blockedDeleteClass.name }}</b> tidak dapat dihapus karena masih memiliki data terkait berikut:
            </p>
            <ul style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px 12px 32px; font-size: 13px; color: #991b1b; display: grid; gap: 8px;">
                <li v-for="(reason, idx) in blockedDeleteClass.delete_reasons" :key="idx">
                    {{ reason }}
                </li>
            </ul>
            <div style="margin-top: 16px; padding: 12px; background: #f8fafc; border-radius: 8px; font-size: 12px; color: #64748b;">
                💡 <b>Petunjuk:</b> Pemindahan siswa dan pelepasan penugasan guru tidak menghapus akun mereka. Jika kelas memiliki riwayat materi/tugas/kuis, gunakan opsi <b>"Arsipkan"</b> agar riwayat dan nilai siswa tetap aman.
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="secondary" @click="blockedDeleteClass = null">Tutup</button>
                <button v-if="blockedDeleteClass.is_active" type="button" class="admin-btn" style="background: #d97706; color: white;" @click="toggleActiveClass(blockedDeleteClass); blockedDeleteClass = null;">
                    Arsipkan Kelas Saja
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
