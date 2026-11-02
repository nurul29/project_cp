<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    submissions: { type: Array, default: () => [] },
});

const selectedSubmission = ref(null);

const gradeForm = useForm({
    score: '',
    feedback: '',
});

const openReview = (sub) => {
    selectedSubmission.value = sub;
    gradeForm.score = sub.score !== null ? sub.score : '';
    gradeForm.feedback = sub.feedback || '';
};

const submitGrade = () => {
    if (!selectedSubmission.value) return;
    gradeForm.post(route('guru.submissions.grade', selectedSubmission.value.id), {
        onSuccess: () => {
            selectedSubmission.value = null;
            gradeForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Pengumpulan Tugas — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Ruang guru</div>
                <h1>Pengumpulan tugas siswa</h1>
                <p>Periksa jawaban lembar kerja dan berikan penilaian serta umpan balik kepada siswa.</p>
            </div>
        </div>

        <section class="panel">
            <div class="panel-head">
                <h2>Daftar pengumpulan ({{ submissions.length }})</h2>
            </div>

            <div v-if="submissions.length" class="list-stack">
                <div
                    v-for="sub in submissions"
                    :key="sub.id"
                    class="list-item"
                >
                    <span class="avatar">
                        {{ sub.student?.name?.slice(0, 2)?.toUpperCase() || 'S' }}
                    </span>
                    <div style="flex: 1; min-width: 0;">
                        <h3 style="font-size: 14px; font-weight: 750;">
                            {{ sub.student?.name }} — {{ sub.assignment?.title }}
                        </h3>
                        <p style="font-size: 12px; color: #5e746f;">
                            {{ sub.student?.classroom?.name || 'Kelas VIII' }} • Dikumpulkan pada {{ new Date(sub.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="status" :class="sub.score === null ? 'wait' : ''">
                            {{ sub.score !== null ? `Nilai: ${sub.score}` : 'Perlu diperiksa' }}
                        </span>
                        <button
                            type="button"
                            class="admin-btn solid"
                            @click="openReview(sub)"
                        >
                            {{ sub.score !== null ? 'Ubah nilai' : 'Periksa jawaban' }}
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="teacher-quiet">
                <span aria-hidden="true">✓</span>
                <h3>Belum ada pengumpulan</h3>
                <p>Jawaban tugas siswa akan muncul di sini setelah mereka menyelesaikan tugas.</p>
            </div>
        </section>

        <!-- Modal Review Jawaban Siswa -->
        <div v-if="selectedSubmission" class="reader-dialog" style="display: block; position: fixed; inset: 0; z-index: 100; margin: auto; max-height: 90vh; overflow-y: auto;">
            <div class="reader-top">
                <div>
                    <span class="soft-pill">{{ selectedSubmission.assignment?.subject?.name || 'Tugas' }}</span>
                    <h2 style="margin: 8px 0 4px;">{{ selectedSubmission.student?.name }}</h2>
                    <p style="margin: 0; font-size: 12px; color: #5e746f;">{{ selectedSubmission.assignment?.title }}</p>
                </div>
                <button type="button" class="close-reader" @click="selectedSubmission = null">×</button>
            </div>

            <div style="margin: 16px 0;">
                <label style="font-size: 11px; font-weight: 800; color: #16766b; text-transform: uppercase; letter-spacing: .1em;">
                    Kertas Jawaban Siswa
                </label>
                <div class="answer-paper">
                    {{ selectedSubmission.answer || selectedSubmission.content || 'Tidak ada teks jawaban yang dilampirkan.' }}
                </div>
            </div>

            <form @submit.prevent="submitGrade" style="display: grid; gap: 14px; border-top: 1px solid #e1e9e4; padding-top: 16px;">
                <div class="field" style="margin: 0;">
                    <label for="grade-score">Nilai hasil pengerjaan (0 - 100) *</label>
                    <input
                        id="grade-score"
                        v-model="gradeForm.score"
                        type="number"
                        min="0"
                        max="100"
                        placeholder="Contoh: 88"
                        style="max-width: 140px;"
                        required
                    />
                </div>

                <div class="field" style="margin: 0;">
                    <label for="grade-feedback">Catatan &amp; umpan balik guru</label>
                    <textarea
                        id="grade-feedback"
                        v-model="gradeForm.feedback"
                        placeholder="Contoh: Langkah pengerjaan sudah tepat dan rapi."
                        style="min-height: 70px;"
                    ></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px;">
                    <button type="button" class="secondary" @click="selectedSubmission = null">Batal</button>
                    <button type="submit" class="primary" :disabled="gradeForm.processing">Simpan Penilaian →</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
