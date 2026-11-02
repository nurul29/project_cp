<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    assignments: { type: Array, default: () => [] },
    quizzes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    classrooms: { type: Array, default: () => [] },
});

const showModal = ref(false);

const form = useForm({
    title: '',
    subject_id: props.subjects[0]?.id || '',
    classroom_id: props.classrooms[0]?.id || '',
    instructions: '',
    due_at: '',
});

const submit = () => {
    form.post(route('guru.tasks.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Tugas & Kuis — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Ruang guru</div>
                <h1>Tugas &amp; kuis</h1>
                <p>Rancang aktivitas lembar kerja dan kuis interaktif untuk siswa.</p>
            </div>
            <div class="page-actions">
                <button type="button" class="primary" @click="showModal = true">
                    + Buat tugas baru
                </button>
            </div>
        </div>

        <div style="display: grid; gap: 24px;">
            <!-- Assignments List -->
            <section class="panel">
                <div class="panel-head">
                    <h2>Daftar lembar kerja &amp; tugas ({{ assignments.length }})</h2>
                </div>

                <div v-if="assignments.length" class="list-stack">
                    <div
                        v-for="task in assignments"
                        :key="task.id"
                        class="list-item"
                    >
                        <span class="tile-icon peach">📝</span>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 14px; font-weight: 750;">{{ task.title }}</h3>
                            <p style="font-size: 12px; color: #5e746f;">
                                {{ task.subject?.name }} • {{ task.classroom?.name }} • Batas: {{ task.due_at ? new Date(task.due_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Tidak terbatas' }}
                            </p>
                        </div>
                        <span class="status">Aktif</span>
                    </div>
                </div>
                <div v-else class="teacher-quiet">
                    <span aria-hidden="true">📝</span>
                    <h3>Belum ada tugas dibuat</h3>
                    <p>Tekan tombol di atas untuk membuat tugas lembar kerja pertama.</p>
                </div>
            </section>

            <!-- Quizzes List -->
            <section class="panel">
                <div class="panel-head">
                    <h2>Kuis interaktif terhubung ({{ quizzes.length }})</h2>
                </div>

                <div v-if="quizzes.length" class="list-stack">
                    <div
                        v-for="quiz in quizzes"
                        :key="quiz.id"
                        class="list-item"
                    >
                        <span class="tile-icon blue">🎮</span>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 14px; font-weight: 750;">{{ quiz.title }}</h3>
                            <p style="font-size: 12px; color: #5e746f;">
                                {{ quiz.subject?.name }} • {{ quiz.classroom?.name || 'Semua kelas' }} • {{ quiz.attempts?.length || 0 }} pengerjaan siswa
                            </p>
                        </div>
                        <span class="status">Tersedia</span>
                    </div>
                </div>
                <p v-else class="muted">Belum ada kuis yang ditugaskan.</p>
            </section>
        </div>

        <!-- Modal Tambah Tugas -->
        <div v-if="showModal" class="reader-dialog" style="display: block; position: fixed; inset: 0; z-index: 100; margin: auto; max-height: 90vh; overflow-y: auto;">
            <div class="reader-top">
                <h2>Buat aktivitas tugas</h2>
                <button type="button" class="close-reader" @click="showModal = false">×</button>
            </div>

            <form @submit.prevent="submit" style="display: grid; gap: 14px;">
                <div class="field" style="margin: 0;">
                    <label for="task-title">Judul tugas / lembar kerja *</label>
                    <input
                        id="task-title"
                        v-model="form.title"
                        type="text"
                        placeholder="Contoh: Latihan Segitiga Siku-siku"
                        required
                    />
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="field" style="margin: 0;">
                        <label for="task-subject">Mata pelajaran *</label>
                        <select id="task-subject" v-model="form.subject_id" required>
                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="field" style="margin: 0;">
                        <label for="task-class">Kelas tujuan *</label>
                        <select id="task-class" v-model="form.classroom_id" required>
                            <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="field" style="margin: 0;">
                    <label for="task-due">Batas pengumpulan</label>
                    <input
                        id="task-due"
                        v-model="form.due_at"
                        type="date"
                    />
                </div>

                <div class="field" style="margin: 0;">
                    <label for="task-instructions">Petunjuk &amp; soal pengerjaan *</label>
                    <textarea
                        id="task-instructions"
                        v-model="form.instructions"
                        placeholder="Tuliskan petunjuk atau soal yang harus dijawab oleh siswa..."
                        style="min-height: 100px;"
                        required
                    ></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="secondary" @click="showModal = false">Batal</button>
                    <button type="submit" class="primary" :disabled="form.processing">Simpan &amp; Terbitkan</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
