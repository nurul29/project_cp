<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    settings: { type: Object, default: () => ({ school_name: '', school_year: '', semester: '' }) },
});

const form = useForm({
    school_name: props.settings.school_name || 'SMP Negeri 16 Depok',
    school_year: props.settings.school_year || '2026/2027',
    semester: props.settings.semester || 'Ganjil',
});

const submit = () => {
    form.post(route('admin.settings.update'));
};
</script>

<template>
    <Head title="Pengaturan Sekolah — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Pengelolaan sekolah</div>
                <h1>Pengaturan sekolah</h1>
                <p>Identitas dan periode belajar yang digunakan pada seluruh modul pembelajaran.</p>
            </div>
        </div>

        <section class="panel" style="max-width: 650px;">
            <div class="panel-head">
                <h2>Identitas &amp; tahun ajaran</h2>
                <span class="admin-badge good">Pengaturan umum</span>
            </div>

            <form @submit.prevent="submit" style="display: grid; gap: 16px; margin-top: 14px;">
                <div class="field" style="margin: 0;">
                    <label for="setting-name">Nama institusi sekolah *</label>
                    <input
                        id="setting-name"
                        v-model="form.school_name"
                        type="text"
                        placeholder="Contoh: SMP Negeri 16 Depok"
                        required
                    />
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="field" style="margin: 0;">
                        <label for="setting-year">Tahun ajaran *</label>
                        <input
                            id="setting-year"
                            v-model="form.school_year"
                            type="text"
                            placeholder="Contoh: 2026/2027"
                            required
                        />
                    </div>
                    <div class="field" style="margin: 0;">
                        <label for="setting-semester">Semester aktif *</label>
                        <select id="setting-semester" v-model="form.semester" required>
                            <option value="Ganjil">Semester Ganjil</option>
                            <option value="Genap">Semester Genap</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <button type="submit" class="primary" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan pengaturan' }}
                    </button>
                </div>
            </form>
        </section>
    </AuthenticatedLayout>
</template>
