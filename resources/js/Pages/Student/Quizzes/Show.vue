<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusPill from '@/Components/StatusPill.vue';

const props = defineProps({ quiz: { type: Object, required: true } });
const currentIndex = ref(0);
const selectedOptions = ref({});
const retrying = ref(false);
const finishConfirmation = ref(false);
const form = useForm({ answers: [] });
const currentQuestion = computed(() => props.quiz.questions[currentIndex.value]);
const unansweredQuestions = computed(() => props.quiz.questions.filter(
    (question) => selectedOptions.value[question.id] === undefined,
));
const progress = computed(() => props.quiz.questions.length ? Math.round(((currentIndex.value + 1) / props.quiz.questions.length) * 100) : 0);

const submitQuiz = () => {
    form.answers = props.quiz.questions.map((question) => ({
        question_id: question.id,
        answer_index: Number(selectedOptions.value[question.id]),
    }));
    form.post(route('siswa.quizzes.finish', props.quiz.id), {
        preserveScroll: true,
        onSuccess: () => {
            retrying.value = false;
            finishConfirmation.value = false;
        },
    });
};

const requestFinish = () => {
    if (unansweredQuestions.value.length) {
        currentIndex.value = props.quiz.questions.findIndex(
            (question) => selectedOptions.value[question.id] === undefined,
        );
    }
    finishConfirmation.value = true;
};

const moveNext = () => {
    if (currentIndex.value < props.quiz.questions.length - 1) {
        currentIndex.value++;
        return;
    }

    requestFinish();
};

const goToQuestion = (index) => {
    currentIndex.value = index;
    finishConfirmation.value = false;
};

const retryQuiz = () => {
    currentIndex.value = 0;
    selectedOptions.value = {};
    finishConfirmation.value = false;
    form.reset();
    form.clearErrors();
    retrying.value = true;
};
</script>

<template>
    <Head :title="quiz.title" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="Kuis & games · pilihan ganda" :title="quiz.title" :description="quiz.subject?.name || 'Latihan untuk kelasmu'">
            <template v-if="quiz.attempt" #action><StatusPill status="graded" /></template>
        </PageHeader>
        <section v-if="quiz.attempt && !retrying" class="rounded-3xl border border-[#cde9dc] bg-white p-6 text-center sm:p-10">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#e9f7f1] text-3xl text-[#0c7774]">✓</span>
            <p class="mt-5 text-xs font-bold uppercase tracking-[.15em] text-[#0c7774]">Hasil kuis</p>
            <h2 class="mt-2 text-3xl font-extrabold">{{ quiz.attempt.score }} <span class="text-lg font-semibold text-[#667b79]">/ {{ quiz.attempt.total }}</span></h2>
            <p class="mt-2 text-sm text-[#667b79]">Kuis ini sudah dinilai. Pelajari kunci dan pembahasan sebelum mencoba lagi.</p>
            <div class="mt-5 flex flex-wrap justify-center gap-3">
                <button v-if="quiz.status === 'published'" type="button" class="inline-flex min-h-11 items-center rounded-xl bg-[#0c7774] px-5 text-sm font-bold text-white hover:bg-[#075f5d]" @click="retryQuiz">Coba lagi</button>
                <Link :href="route('siswa.quizzes.index')" class="inline-flex min-h-11 items-center rounded-xl border border-[#dce6e1] px-5 text-sm font-bold text-[#193b3a] hover:bg-[#f7faf8]">Kembali ke daftar kuis</Link>
            </div>
        </section>
        <EmptyState v-else-if="!quiz.questions.length" title="Kuis belum memiliki soal" description="Guru akan menambahkan soal sebelum kuis dapat dikerjakan." icon="✳" />
        <section v-else class="mx-auto max-w-3xl rounded-3xl border border-[#e4eae7] bg-white p-5 sm:p-8">
            <template v-if="quiz.status === 'published'">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm font-bold">Pertanyaan {{ currentIndex + 1 }} <span class="font-medium text-[#667b79]">dari {{ quiz.questions.length }}</span></p>
                    <p class="text-xs font-semibold text-[#0c7774]">{{ progress }}% · {{ quiz.questions.length - unansweredQuestions.length }} terjawab</p>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#e9f7f1]" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100" aria-label="Posisi kuis">
                    <div class="h-full rounded-full bg-[#0c7774] transition-[width]" :style="{ width: `${progress}%` }"></div>
                </div>
                <nav class="mt-4 flex flex-wrap gap-2" aria-label="Navigasi pertanyaan">
                    <button
                        v-for="(question, index) in quiz.questions"
                        :key="question.id"
                        type="button"
                        class="grid h-9 w-9 place-items-center rounded-lg border text-xs font-bold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0c7774]"
                        :class="[
                            currentIndex === index ? 'border-[#0c7774] bg-[#0c7774] text-white' : 'border-[#dce6e1] text-[#193b3a]',
                            selectedOptions[question.id] !== undefined && currentIndex !== index ? 'bg-[#e9f7f1]' : '',
                        ]"
                        :aria-label="`Pertanyaan ${index + 1}${selectedOptions[question.id] !== undefined ? ', sudah dijawab' : ', belum dijawab'}`"
                        :aria-current="currentIndex === index ? 'step' : undefined"
                        @click="goToQuestion(index)"
                    >{{ index + 1 }}</button>
                </nav>
                <h2 class="mt-7 text-lg font-extrabold leading-7 sm:text-xl">{{ currentQuestion.body }}</h2>
                <fieldset class="mt-5 space-y-3">
                    <legend class="sr-only">Pilih satu jawaban</legend>
                    <label v-for="(option, index) in currentQuestion.options" :key="index" class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 text-sm transition" :class="selectedOptions[currentQuestion.id] === index ? 'border-[#0c7774] bg-[#e9f7f1]' : 'border-[#e4eae7] hover:border-[#b8dcd0]'">
                        <input v-model="selectedOptions[currentQuestion.id]" type="radio" :name="`question-${currentQuestion.id}`" :value="index" class="border-[#9bbab0] text-[#0c7774] focus:ring-[#0c7774]" />
                        <span>{{ option }}</span>
                    </label>
                </fieldset>
                <p v-if="form.errors.answers" class="mt-3 text-sm font-semibold text-red-700" role="alert">{{ form.errors.answers }}</p>

                <section v-if="finishConfirmation" class="mt-6 rounded-xl border border-[#e4eae7] bg-[#f7faf8] p-4" role="alertdialog" aria-labelledby="finish-title">
                    <h3 id="finish-title" class="text-sm font-extrabold text-[#193b3a]">Periksa jawaban sebelum dikirim</h3>
                    <template v-if="unansweredQuestions.length">
                        <p class="mt-2 text-sm leading-5 text-[#76531b]">Masih ada {{ unansweredQuestions.length }} pertanyaan yang belum dijawab. Semua soal harus dijawab sebelum kuis dapat dikumpulkan.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" class="min-h-10 rounded-lg bg-[#e9f7f1] px-3 text-xs font-bold text-[#075f5d]" @click="finishConfirmation = false">Jawab pertanyaan {{ currentIndex + 1 }}</button>
                            <button type="button" class="min-h-10 rounded-lg border border-[#dce6e1] px-3 text-xs font-bold text-[#193b3a]" @click="finishConfirmation = false">Lanjutkan nanti</button>
                        </div>
                    </template>
                    <template v-else>
                        <p class="mt-2 text-sm leading-5 text-[#667b79]">Semua pertanyaan sudah dijawab. Setelah dikirim, jawaban akan dinilai.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" class="min-h-10 rounded-lg border border-[#dce6e1] px-3 text-xs font-bold text-[#193b3a]" @click="finishConfirmation = false">Periksa lagi</button>
                            <button type="button" class="min-h-10 rounded-lg bg-[#0c7774] px-3 text-xs font-bold text-white disabled:opacity-50" :disabled="form.processing" @click="submitQuiz">{{ form.processing ? 'Mengirim…' : 'Ya, kirim jawaban' }}</button>
                        </div>
                    </template>
                </section>

                <div class="mt-6 flex flex-wrap justify-between gap-3">
                    <button type="button" class="min-h-11 rounded-xl border border-[#dce6e1] px-4 text-sm font-bold text-[#193b3a] disabled:opacity-40" :disabled="currentIndex === 0 || form.processing" @click="goToQuestion(currentIndex - 1)">Pertanyaan sebelumnya</button>
                    <button type="button" class="min-h-11 rounded-xl bg-[#0c7774] px-5 text-sm font-bold text-white hover:bg-[#075f5d] disabled:opacity-50" :disabled="form.processing" @click="moveNext">
                        {{ currentIndex === quiz.questions.length - 1 ? 'Periksa dan kirim' : 'Pertanyaan berikutnya' }}
                    </button>
                </div>
            </template>
            <div v-else class="py-6 text-center">
                <h2 class="text-xl font-extrabold">Kuis tidak aktif</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#667b79]">Kuis ini sudah ditutup dan tidak dapat dikerjakan.</p>
            </div>
        </section>

        <section v-if="quiz.attempt && !retrying && quiz.questions?.length" id="quiz-review" class="mx-auto mt-6 max-w-3xl space-y-4">
            <header>
                <p class="text-[10px] font-extrabold uppercase tracking-[.15em] text-[#0c7774]">Belajar dari hasil</p>
                <h2 class="mt-1 text-lg font-extrabold text-[#193b3a]">Kunci jawaban dan pembahasan</h2>
            </header>
            <article v-for="(question, index) in quiz.questions" :key="question.id" class="rounded-2xl border border-[#e4eae7] bg-white p-5">
                <p class="text-xs font-bold text-[#0c7774]">Pertanyaan {{ index + 1 }}</p>
                <h3 class="mt-2 text-sm font-extrabold leading-6 text-[#193b3a]">{{ question.body }}</h3>
                <ul class="mt-3 space-y-2">
                    <li v-for="(option, optionIndex) in question.options" :key="optionIndex" class="rounded-lg border p-3 text-sm" :class="optionIndex === question.correctIndex ? 'border-[#b8dcc9] bg-[#e9f7f1] font-bold text-[#075f5d]' : 'border-[#e4eae7] text-[#667b79]'">
                        <span>{{ String.fromCharCode(65 + optionIndex) }}. {{ option }}</span>
                        <span v-if="optionIndex === question.correctIndex" class="ml-2 text-xs">Kunci jawaban</span>
                    </li>
                </ul>
                <p v-if="question.explanation" class="mt-3 rounded-lg bg-[#f7faf8] p-3 text-sm leading-6 text-[#526764]"><strong>Pembahasan:</strong> {{ question.explanation }}</p>
                <p v-else class="mt-3 text-xs text-[#667b79]">Belum ada pembahasan untuk soal ini.</p>
            </article>
        </section>
    </AuthenticatedLayout>
</template>
