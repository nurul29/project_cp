<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    classrooms: { type: Array, default: () => [] },
    selectedClassroomId: { type: [Number, String], default: '' },
    assignments: { type: Array, default: () => [] },
    gradesMatrix: { type: Array, default: () => [] },
});

const onClassChange = (event) => {
    router.get(route('guru.grades.index'), {
        classroom_id: event.target.value,
    }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <Head title="Daftar Nilai — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Ruang guru</div>
                <h1>Daftar nilai &amp; rekap belajar</h1>
                <p>Rekap capaian belajar siswa berdasarkan tugas dan lembar kerja yang telah dinilai.</p>
            </div>
            <div class="page-actions">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label for="grade-classroom" style="font-size: 12px; font-weight: 700;">Pilih Kelas:</label>
                    <select
                        id="grade-classroom"
                        :value="selectedClassroomId"
                        style="border: 1px solid #dce6e2; border-radius: 9px; padding: 7px 12px; font-size: 12px;"
                        @change="onClassChange"
                    >
                        <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <section class="panel">
            <div class="panel-head">
                <h2>Tabel nilai siswa</h2>
            </div>

            <div v-if="gradesMatrix.length" class="table-wrap">
                <table class="grade-table">
                    <thead>
                        <tr>
                            <th style="min-width: 180px;">Nama Siswa</th>
                            <th>NIS</th>
                            <th v-for="asg in assignments" :key="asg.id">
                                {{ asg.title }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in gradesMatrix" :key="row.student_id">
                            <td>
                                <strong>{{ row.name }}</strong>
                            </td>
                            <td>{{ row.username }}</td>
                            <td v-for="asg in assignments" :key="asg.id">
                                <span v-if="row.scores[asg.id] !== undefined && row.scores[asg.id] !== null" style="font-weight: 800; color: #16766b;">
                                    {{ row.scores[asg.id] }}
                                </span>
                                <span v-else style="color: #94a39f; font-size: 11px;">
                                    Belum ada
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="muted">Belum ada data siswa pada kelas ini.</p>
        </section>
    </AuthenticatedLayout>
</template>
