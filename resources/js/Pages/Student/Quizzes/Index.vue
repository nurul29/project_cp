<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusPill from '@/Components/StatusPill.vue';

defineProps({ quizzes: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="Kuis & games" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="Uji pemahaman" title="Kuis & games" description="Latihan pilihan ganda dari guru. Hasil kuis yang sudah dinilai akan tersimpan di sini." />
        <div v-if="quizzes.length" class="space-y-3">
            <article v-for="quiz in quizzes" :key="quiz.id" class="flex flex-wrap items-center gap-3 rounded-xl border border-[#e4eae7] bg-white p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#e9f7f1] text-lg">{{ quiz.subject.icon }}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold">{{ quiz.title }}</p>
                    <p class="mt-1 text-xs text-[#667b79]">{{ quiz.subject.name }} · {{ quiz.questionCount }} pertanyaan</p>
                    <p class="mt-1 text-xs font-semibold text-[#667b79]">
                        <Link v-if="quiz.material" :href="quiz.material.href" class="hover:text-[#0c7774] hover:underline">Terkait materi: {{ quiz.material.title }}</Link>
                        <template v-else-if="quiz.hasMaterial">Terkait materi</template>
                        <template v-else>Kuis mandiri</template>
                    </p>
                    <p v-if="quiz.attempt" class="mt-1 text-xs font-semibold text-[#0c7774]">Nilai: {{ quiz.attempt.score }} dari {{ quiz.attempt.total }}</p>
                </div>
                <StatusPill :status="quiz.attempt ? 'graded' : quiz.status" />
                <Link :href="quiz.href" class="inline-flex min-h-10 items-center rounded-xl bg-[#e9f7f1] px-3 text-xs font-bold text-[#075f5d] hover:bg-[#d7eeea]">{{ quiz.attempt ? 'Lihat hasil' : 'Lihat kuis' }}</Link>
            </article>
        </div>
        <EmptyState v-else title="Belum ada kuis" description="Kuis untuk kelasmu akan muncul di sini." icon="✳" />
    </AuthenticatedLayout>
</template>
