@php
    $viewData = $this->getViewData();
    $quiz = $viewData['quiz'];
    $questions = $viewData['questions'];
    $totalStudentsCount = $viewData['totalStudentsCount'];
    $completedCount = $viewData['completedCount'];
    $pendingCount = $viewData['pendingCount'];
    $participationRate = $viewData['participationRate'];
    $avgScore = $viewData['avgScore'];
    $maxScore = $viewData['maxScore'];
    $minScore = $viewData['minScore'];
    $studentReports = $viewData['studentReports'];
    $selectedAttempt = $viewData['selectedAttempt'];

    $currentQuestion = $questions->get($this->previewQuestionIndex);
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Banner Informasi Utama Kuis --}}
        <div class="rounded-2xl border border-teal-100 bg-gradient-to-r from-teal-50/80 via-white to-emerald-50/60 p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800">
                            <span>{{ $quiz->subject?->icon ?? '📖' }}</span>
                            {{ $quiz->subject?->name ?? 'Mata Pelajaran' }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800">
                            Kelas {{ $quiz->classroom?->name ?? '-' }}
                        </span>
                        @if ($quiz->status?->value === 'published' || $quiz->status === 'published')
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                ● Terbit (Aktif di Murid)
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">
                                Draf
                            </span>
                        @endif
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 border border-blue-200">
                            Versi {{ $quiz->revision ?? 1 }}
                        </span>
                    </div>

                    <h1 class="mt-2.5 text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">
                        {{ $quiz->title }}
                    </h1>

                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600">
                        @if ($quiz->material)
                            <span>📖 Tertaut ke Buku: <strong>{{ $quiz->material->title }}</strong></span>
                        @else
                            <span>✦ Kuis Mandiri (Tidak tertaut ke buku spesifik)</span>
                        @endif
                        <span class="text-gray-300">•</span>
                        <span>Total Butir: <strong>{{ $questions->count() }} Soal</strong></span>
                        <span class="text-gray-300">•</span>
                        <span>Dibuat: {{ $quiz->created_at?->translatedFormat('d F Y') }}</span>
                    </div>

                    @if ($quiz->instructions)
                        <div class="mt-3 rounded-xl bg-white/80 border border-teal-200/60 p-3 text-xs leading-relaxed text-gray-700">
                            <span class="font-bold text-teal-900">Petunjuk Pengerjaan:</span> {{ $quiz->instructions }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4 Kartu Metrik Hasil Pengerjaan Siswa --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-gray-500">Total Siswa Kelas</p>
                <p class="mt-1 text-2xl font-black text-gray-900">{{ $totalStudentsCount }}</p>
                <p class="text-[11px] text-gray-400">Target pengerjaan kelas</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-gray-500">Sudah Mengerjakan</p>
                <p class="mt-1 text-2xl font-black text-emerald-600">{{ $completedCount }}</p>
                <p class="text-[11px] font-semibold text-emerald-700">{{ $participationRate }}% Partisipasi siswa</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-gray-500">Belum Mengerjakan</p>
                <p class="mt-1 text-2xl font-black text-amber-600">{{ $pendingCount }}</p>
                <p class="text-[11px] text-amber-700">Perlu diingatkan</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-gray-500">Rata-rata Nilai Kelas</p>
                <p class="mt-1 text-2xl font-black text-indigo-600">{{ $avgScore }}</p>
                <p class="text-[11px] text-gray-400">Tertinggi: {{ $maxScore }} • Terendah: {{ $minScore }}</p>
            </div>
        </div>

        {{-- Navigasi Tab Utama --}}
        <div class="flex border-b border-gray-200">
            <button
                type="button"
                wire:click="setTab(0)"
                class="flex items-center gap-2 border-b-2 px-5 py-3 text-sm font-bold transition-all {{ $activeTab === 0 ? 'border-teal-600 text-teal-700 bg-teal-50/30' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                <span class="text-base">📱</span>
                <span>Pratinjau Tampilan Murid</span>
                <span class="rounded-full bg-teal-100 px-2 py-0.5 text-xs font-extrabold text-teal-800">
                    {{ $questions->count() }} Soal
                </span>
            </button>
            <button
                type="button"
                wire:click="setTab(1)"
                class="flex items-center gap-2 border-b-2 px-5 py-3 text-sm font-bold transition-all {{ $activeTab === 1 ? 'border-teal-600 text-teal-700 bg-teal-50/30' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                <span class="text-base">👥</span>
                <span>Informasi Pengerjaan Siswa</span>
                <span class="rounded-full {{ $completedCount > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }} px-2 py-0.5 text-xs font-extrabold">
                    {{ $completedCount }} / {{ $totalStudentsCount }}
                </span>
            </button>
        </div>

        {{-- KONTEN TAB 0: PRATINJAU TAMPILAN MURID --}}
        @if ($activeTab === 0)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Simulasi Tampilan di Layar Siswa</h3>
                        <p class="text-xs text-gray-500">Guru dapat melihat tampilan butir soal persis seperti yang dihadapi siswa di aplikasi</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-md bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 border border-teal-200">
                        <span>💡</span> Kunci jawaban dan pembahasan ditandai untuk referensi guru
                    </span>
                </div>

                @if ($questions->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                        <span class="text-4xl">📝</span>
                        <h4 class="mt-2 text-sm font-bold text-gray-800">Kuis Belum Memiliki Soal</h4>
                        <p class="mt-1 text-xs text-gray-500">Klik tombol "Ubah Kuis" di atas untuk mulai menambahkan pertanyaan pilihan ganda.</p>
                    </div>
                @else
                    {{-- Simulator Navigasi Nomor Soal --}}
                    <div class="mt-5">
                        <p class="text-xs font-semibold text-gray-500">Navigasi Butir Pertanyaan:</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($questions as $index => $q)
                                <button
                                    type="button"
                                    wire:click="setQuestionIndex({{ $index }})"
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border text-xs font-black transition-all {{ $this->previewQuestionIndex === $index ? 'border-teal-600 bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100' }}"
                                >
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Kotak Soal Aktif --}}
                    @if ($currentQuestion)
                        <div class="mt-6 rounded-2xl border border-teal-100 bg-slate-50/50 p-6">
                            <div class="flex items-center justify-between border-b border-gray-200/80 pb-3">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-teal-700">
                                    Pertanyaan {{ $this->previewQuestionIndex + 1 }} dari {{ $questions->count() }}
                                </span>
                                <span class="text-xs text-gray-500 font-medium">Pilihan Ganda</span>
                            </div>

                            <div class="mt-4">
                                <h4 class="text-base font-bold leading-relaxed text-gray-900 sm:text-lg">
                                    {{ $currentQuestion->body }}
                                </h4>
                            </div>

                            {{-- Pilihan Ganda A, B, C, D --}}
                            <div class="mt-5 space-y-3">
                                @php
                                    $options = is_array($currentQuestion->options) ? $currentQuestion->options : [];
                                    $labels = ['A', 'B', 'C', 'D'];
                                @endphp
                                @foreach ($options as $optIndex => $optionText)
                                    @php
                                        $isCorrect = (int) $currentQuestion->correct_index === $optIndex;
                                    @endphp
                                    <div class="flex items-center justify-between rounded-xl border p-4 transition-all {{ $isCorrect ? 'border-teal-500 bg-teal-50/70 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg font-black text-xs {{ $isCorrect ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $labels[$optIndex] ?? ($optIndex + 1) }}
                                            </span>
                                            <span class="text-sm font-medium {{ $isCorrect ? 'font-bold text-teal-950' : 'text-gray-800' }}">
                                                {{ $optionText }}
                                            </span>
                                        </div>
                                        @if ($isCorrect)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800">
                                                <span>✓</span> Kunci Jawaban Benar
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Pembahasan Soal --}}
                            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                                <div class="flex items-start gap-2.5">
                                    <span class="text-lg text-emerald-600">💡</span>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                                            Pembahasan Soal (Dilihat siswa setelah selesai kuis):
                                        </p>
                                        <p class="mt-1 text-xs leading-relaxed text-emerald-950">
                                            {{ $currentQuestion->explanation ?: 'Belum ada penjelasan/pembahasan yang ditambahkan untuk soal ini.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Navigasi Bawah --}}
                            <div class="mt-6 flex items-center justify-between border-t border-gray-200/80 pt-4">
                                <button
                                    type="button"
                                    wire:click="prevQuestion"
                                    @if ($this->previewQuestionIndex === 0) disabled @endif
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <span>←</span> Soal Sebelumnya
                                </button>
                                <span class="text-xs font-semibold text-gray-500">
                                    {{ $this->previewQuestionIndex + 1 }} / {{ $questions->count() }}
                                </span>
                                <button
                                    type="button"
                                    wire:click="nextQuestion"
                                    @if ($this->previewQuestionIndex >= $questions->count() - 1) disabled @endif
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-teal-700 disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    Soal Berikutnya <span>→</span>
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        {{-- KONTEN TAB 1: INFORMASI PENGERJAAN SISWA --}}
        @if ($activeTab === 1)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 border-b border-gray-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Daftar Siswa Kelas {{ $quiz->classroom?->name }}</h3>
                        <p class="text-xs text-gray-500">Pantau siswa yang telah menyelesaikan tugas dan siapa yang belum mengumpulkan</p>
                    </div>

                    {{-- Search & Filter --}}
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full sm:w-64">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchStudent"
                                placeholder="Cari nama atau NISN siswa..."
                                class="w-full rounded-xl border border-gray-300 px-3.5 py-1.5 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            />
                        </div>
                        <select
                            wire:model.live="statusFilter"
                            class="rounded-xl border border-gray-300 px-3 py-1.5 text-xs text-gray-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        >
                            <option value="all">Semua Siswa</option>
                            <option value="completed">Sudah Mengerjakan</option>
                            <option value="not_completed">Belum Mengerjakan</option>
                        </select>
                    </div>
                </div>

                {{-- Tabel Siswa --}}
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/70 text-gray-600">
                                <th class="py-3 px-4 font-bold">No</th>
                                <th class="py-3 px-4 font-bold">Nama Siswa</th>
                                <th class="py-3 px-4 font-bold">NISN / Username</th>
                                <th class="py-3 px-4 font-bold">Status Pengerjaan</th>
                                <th class="py-3 px-4 font-bold">Nilai / Skor</th>
                                <th class="py-3 px-4 font-bold">Waktu Selesai</th>
                                <th class="py-3 px-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($studentReports as $idx => $student)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="py-3 px-4 text-gray-500 font-medium">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4 font-bold text-gray-900">
                                        {{ $student['name'] }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">{{ $student['username'] }}</td>
                                    <td class="py-3 px-4">
                                        @if ($student['is_completed'])
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                                ● Selesai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                                ○ Belum Mengerjakan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-bold">
                                        @if ($student['is_completed'])
                                            <span class="{{ $student['score'] >= 75 ? 'text-emerald-700' : 'text-amber-700' }} text-sm font-black">
                                                {{ $student['score'] }} <span class="text-[10px] font-normal text-gray-400">/ 100</span>
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-500">
                                        {{ $student['finished_at'] ? \Carbon\Carbon::parse($student['finished_at'])->translatedFormat('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if ($student['is_completed'])
                                            <button
                                                type="button"
                                                wire:click="openAttemptDetail({{ $student['attempt_id'] }})"
                                                class="inline-flex items-center gap-1 rounded-lg border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-800 hover:bg-teal-100 transition"
                                            >
                                                🔍 Lembar Jawaban
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs italic">Menunggu pengerjaan</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500">
                                        Tidak ada data siswa yang cocok dengan pencarian / filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- MODAL DETAIL LEMBAR JAWABAN SISWA --}}
        @if ($selectedAttempt)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-wider text-teal-700">Lembar Jawaban Siswa</span>
                            <h3 class="text-lg font-bold text-gray-900">{{ $selectedAttempt->student?->name }}</h3>
                            <p class="text-xs text-gray-500">
                                NISN: {{ $selectedAttempt->student?->username }} • Selesai: {{ $selectedAttempt->finished_at?->translatedFormat('d F Y, H:i') }} WIB
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500 font-semibold">Skor Akhir</span>
                            <p class="text-2xl font-black text-teal-600">{{ $selectedAttempt->score }} <span class="text-xs text-gray-400">/ 100</span></p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        @php
                            $studentAnswers = collect($selectedAttempt->answers)->keyBy('question_id');
                        @endphp
                        @foreach ($questions as $qIndex => $question)
                            @php
                                $ans = $studentAnswers->get($question->id);
                                $studentAnswerIndex = isset($ans['answer_index']) ? (int) $ans['answer_index'] : null;
                                $isCorrect = $studentAnswerIndex !== null && $studentAnswerIndex === $question->correct_index;
                                $labels = ['A', 'B', 'C', 'D'];
                            @endphp
                            <div class="rounded-xl border p-4 {{ $isCorrect ? 'border-emerald-200 bg-emerald-50/40' : 'border-rose-200 bg-rose-50/40' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-700">Soal {{ $qIndex + 1 }}</span>
                                    @if ($isCorrect)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                            ✓ Benar (+{{ round(100 / max(1, $questions->count())) }} poin)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800">
                                            ✗ Salah
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-2 text-sm font-bold text-gray-900">{{ $question->body }}</p>

                                <div class="mt-3 space-y-1.5">
                                    @foreach ($question->options as $optIdx => $optText)
                                        <div class="flex items-center gap-2 text-xs rounded-lg p-2 {{ $optIdx === $question->correct_index ? 'bg-emerald-100/70 font-bold text-emerald-900' : ($optIdx === $studentAnswerIndex ? 'bg-rose-100/70 font-bold text-rose-900' : 'text-gray-600') }}">
                                            <span class="font-bold">{{ $labels[$optIdx] ?? ($optIdx + 1) }}.</span>
                                            <span>{{ $optText }}</span>
                                            @if ($optIdx === $question->correct_index)
                                                <span class="ml-auto text-[10px] text-emerald-700 font-extrabold">(Kunci Benar)</span>
                                            @endif
                                            @if ($optIdx === $studentAnswerIndex && ! $isCorrect)
                                                <span class="ml-auto text-[10px] text-rose-700 font-extrabold">(Jawaban Siswa)</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end border-t border-gray-100 pt-4">
                        <button
                            type="button"
                            wire:click="closeAttemptDetail"
                            class="rounded-xl bg-gray-200 px-5 py-2 text-xs font-bold text-gray-800 hover:bg-gray-300 transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
