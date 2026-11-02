<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusPill from '@/Components/StatusPill.vue';

const props = defineProps({ assignment: { type: Object, required: true } });
const form = useForm({ answer: props.assignment.answer || '' });
const isGraded = props.assignment.status === 'graded';
const dateLabel = (date) => date ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'long', timeStyle: 'short' }).format(new Date(date)) : 'Tidak ada batas pengumpulan';
const submitAnswer = () => form.post(route('siswa.worksheets.submit', props.assignment.id), { preserveScroll: true });
</script>

<template>
    <Head :title="assignment.title" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="Lembar kerja" :title="assignment.title" :description="assignment.subject">
            <template v-if="assignment.status" #action><StatusPill :status="assignment.status" /></template>
        </PageHeader>
        <div class="grid gap-5 lg:grid-cols-[1.2fr_.8fr]">
            <article class="rounded-2xl border border-[#e4eae7] bg-white p-5 sm:p-7">
                <h2 class="font-extrabold">Petunjuk pengerjaan</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-[#667b79]">{{ assignment.instructions || 'Ikuti petunjuk yang disampaikan oleh gurumu.' }}</p>
                <div class="mt-6 rounded-xl bg-[#f7faf8] p-4">
                    <p class="text-xs font-bold text-[#667b79]">Batas pengumpulan</p>
                    <p class="mt-1 text-sm font-bold">{{ dateLabel(assignment.dueAt) }}</p>
                </div>
                <div v-if="assignment.feedback" class="mt-4 rounded-xl bg-[#e9f7f1] p-4">
                    <p class="text-xs font-bold text-[#075f5d]">Umpan balik guru · Nilai {{ assignment.score ?? '—' }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#193b3a]">{{ assignment.feedback }}</p>
                </div>
            </article>
            <section class="rounded-2xl border border-[#e4eae7] bg-white p-5 sm:p-7">
                <h2 class="font-extrabold">Jawabanmu</h2>
                <p class="mt-1 text-xs leading-5 text-[#667b79]">Kirim jawabanmu untuk disimpan di portofolio dan menunggu penilaian guru.</p>
                <textarea v-model="form.answer" :disabled="!assignment.canSubmit || isGraded" rows="8" maxlength="10000" class="mt-4 w-full rounded-xl border-[#dce6e1] text-sm focus:border-[#0c7774] focus:ring-[#0c7774] disabled:bg-[#f7faf8]" :placeholder="isGraded ? 'Tugas sudah dinilai.' : 'Tuliskan jawabanmu di sini…'" :aria-label="`Jawaban untuk ${assignment.title}`"></textarea>
                <p v-if="form.errors.answer" class="mt-2 text-sm font-semibold text-red-700" role="alert">{{ form.errors.answer }}</p>
                <button type="button" :disabled="!assignment.canSubmit || isGraded || form.processing || !form.answer.trim()" class="mt-3 min-h-11 w-full rounded-xl bg-[#0c7774] px-4 text-sm font-bold text-white hover:bg-[#075f5d] disabled:cursor-not-allowed disabled:bg-[#a6c4bb]" @click="submitAnswer">
                    {{ form.processing ? 'Mengirim…' : isGraded ? 'Tugas sudah dinilai' : assignment.status === 'pending' ? 'Kirim ulang jawaban' : 'Kumpulkan tugas' }}
                </button>
                <p v-if="assignment.status === 'pending'" class="mt-2 text-center text-[11px] leading-5 text-[#667b79]">Jawaban terkirim{{ assignment.submittedAt ? ` pada ${dateLabel(assignment.submittedAt)}` : '' }}. Kamu masih dapat memperbaruinya sebelum dinilai.</p>
                <p v-else-if="!assignment.canSubmit && !isGraded" class="mt-2 text-center text-[11px] leading-5 text-[#667b79]">Pengumpulan tugas ini sudah ditutup.</p>
            </section>
        </div>
        <Link :href="route('siswa.worksheets.index')" class="mt-5 inline-flex text-sm font-bold text-[#0c7774] hover:underline">← Kembali ke lembar kerja</Link>
    </AuthenticatedLayout>
</template>
