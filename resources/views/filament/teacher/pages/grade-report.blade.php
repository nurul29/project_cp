<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Navigation Tabs --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-4">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="setMode('summary')"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $mode === 'summary' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}"
                >
                    <x-heroicon-m-user-group class="w-4 h-4" />
                    <span>Ringkasan Kelas</span>
                </button>
                <button
                    type="button"
                    wire:click="setMode('detail')"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $mode === 'detail' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}"
                >
                    <x-heroicon-m-clipboard-document-check class="w-4 h-4" />
                    <span>Nilai per Kuis</span>
                </button>
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400 self-center sm:self-auto">
                Laporan pemantauan hasil belajar siswa terkini
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            {{-- Class Selector --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Pilih Kelas</label>
                <select
                    wire:model.live="selectedClassroomId"
                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                >
                    @forelse($this->getClassrooms() as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @empty
                        <option value="">Belum ada kelas diampu</option>
                    @endforelse
                </select>
            </div>

            {{-- Quiz Selector (Only for Detail Mode) --}}
            @if($mode === 'detail')
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Pilih Kuis</label>
                    <select
                        wire:model.live="selectedQuizId"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                    >
                        @forelse($this->getQuizzes() as $quiz)
                            <option value="{{ $quiz->id }}">{{ $quiz->title }} ({{ $quiz->subject?->name ?? 'Umum' }})</option>
                        @empty
                            <option value="">Tidak ada kuis terbit</option>
                        @endforelse
                    </select>
                </div>
            @endif

            {{-- Student Search --}}
            <div class="{{ $mode === 'summary' ? 'md:col-span-2' : '' }}">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Cari Siswa</label>
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Ketik nama atau NIS / username..."
                        class="w-full pl-9 text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                    />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <x-heroicon-m-magnifying-glass class="w-4 h-4" />
                    </div>
                </div>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Status Pengerjaan</label>
                <select
                    wire:model.live="statusFilter"
                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                >
                    <option value="all">Semua Status</option>
                    <option value="done">{{ $mode === 'summary' ? 'Semua Kuis Lengkap' : 'Sudah Mengerjakan' }}</option>
                    <option value="pending">{{ $mode === 'summary' ? 'Belum Lengkap' : 'Belum Mulai' }}</option>
                </select>
            </div>
        </div>

        {{-- TAB 1: SUMMARY MODE --}}
        @if($mode === 'summary')
            @php $summary = $this->getSummaryData(); @endphp

            {{-- Metric Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Siswa Aktif</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $summary['metrics']['total_students'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Terdaftar di kelas</div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Kuis Kelas</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $summary['metrics']['total_quizzes'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Kuis berstatus terbit</div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                    <div class="text-xs font-medium text-green-600 dark:text-green-400">Kuis Lengkap (100%)</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $summary['metrics']['complete_count'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Siswa telah tuntas</div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                    <div class="text-xs font-medium text-amber-600 dark:text-amber-400">Perlu Pengerjaan</div>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $summary['metrics']['pending_count'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Siswa belum selesai</div>
                </div>
            </div>

            {{-- Summary Table --}}
            <div class="overflow-hidden bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                            <th class="py-3 px-4">Siswa</th>
                            <th class="py-3 px-4 text-center">Progress Kuis</th>
                            <th class="py-3 px-4 text-center">Rata-rata Nilai</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($summary['rows'] as $row)
                            <tr class="hover:bg-gray-50/75 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $row['student']->name }}</div>
                                    <div class="text-xs text-gray-500">NIS / Akun: {{ $row['student']->username }}</div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                        {{ $row['completed_count'] }} / {{ $row['total_quizzes'] }} Kuis
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($row['average_score'] !== null)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $row['average_score'] >= 75 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                            {{ $row['average_score'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">Belum ada nilai</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($row['is_complete'])
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 dark:text-green-400">
                                            <x-heroicon-m-check-circle class="w-4 h-4" /> Lengkap
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 dark:text-amber-400">
                                            <x-heroicon-m-clock class="w-4 h-4" /> Belum Lengkap
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data siswa yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- TAB 2: DETAIL PER QUIZ MODE --}}
        @if($mode === 'detail')
            @php $quizData = $this->getQuizDetailData(); @endphp

            @if($quizData['quiz'])
                {{-- Metric Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Partisipasi Siswa</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $quizData['metrics']['percent_done'] }}%</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $quizData['metrics']['done_count'] }} dari {{ $quizData['metrics']['total_students'] }} siswa</div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                        <div class="text-xs font-medium text-green-600 dark:text-green-400">Sudah Mengerjakan</div>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $quizData['metrics']['done_count'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">Siswa telah submit</div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                        <div class="text-xs font-medium text-amber-600 dark:text-amber-400">Belum Mulai</div>
                        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $quizData['metrics']['pending_count'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">Perlu diingatkan</div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                        <div class="text-xs font-medium text-primary-600 dark:text-primary-400">Rata-rata Nilai Kelas</div>
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $quizData['metrics']['average_score'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">Skala 0 - 100</div>
                    </div>
                </div>

                {{-- Detail Table --}}
                <div class="overflow-hidden bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                                <th class="py-3 px-4">Siswa</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Nilai Tertinggi</th>
                                <th class="py-3 px-4 text-center">Nilai Terakhir</th>
                                <th class="py-3 px-4 text-center">Percobaan</th>
                                <th class="py-3 px-4 text-center">Waktu Pengerjaan</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($quizData['rows'] as $row)
                                <tr class="hover:bg-gray-50/75 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $row['student']->name }}</div>
                                        <div class="text-xs text-gray-500">NIS: {{ $row['student']->username }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row['status'] === 'done')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                                Sudah Selesai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                                Belum Mulai
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row['best_score'] !== null)
                                            <span class="text-sm font-bold {{ $row['best_score'] >= 75 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                                                {{ $row['best_score'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row['latest_score'] !== null)
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $row['latest_score'] }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center text-xs text-gray-600 dark:text-gray-400">
                                        {{ $row['attempts_count'] > 0 ? $row['attempts_count'] . 'x' : '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-xs text-gray-500">
                                        {{ $row['latest_date'] }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if($row['latest_attempt_id'])
                                            <button
                                                type="button"
                                                wire:click="viewAttempt({{ $row['latest_attempt_id'] }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 dark:bg-primary-950 dark:text-primary-300 dark:hover:bg-primary-900 transition-colors"
                                            >
                                                <x-heroicon-m-eye class="w-3.5 h-3.5" />
                                                <span>Lembar Jawaban</span>
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Tidak ada aksi</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada siswa yang cocok dengan kriteria filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                    <x-heroicon-o-academic-cap class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                    <h3 class="font-semibold text-gray-900 dark:text-white">Belum Ada Kuis Terpilih</h3>
                    <p class="text-sm text-gray-500 mt-1">Pilih kuis terbit pada filter di atas untuk melihat detail perolehan nilai siswa.</p>
                </div>
            @endif
        @endif

        {{-- ATTEMPT DETAIL MODAL --}}
        @if($viewingAttemptId && ($attempt = $this->getViewingAttempt()))
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-3xl w-full border border-gray-200 dark:border-gray-800 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/30">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Lembar Jawaban Siswa</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $attempt->score >= 75 ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                    Skor: {{ $attempt->score }} / 100
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Siswa: <strong class="text-gray-800 dark:text-gray-200">{{ $attempt->student?->name }}</strong> (NIS: {{ $attempt->student?->username }}) • Kuis: {{ $attempt->quiz?->title }}
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="closeAttemptModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <x-heroicon-m-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    {{-- Modal Body: Questions and Student Answers --}}
                    <div class="p-6 overflow-y-auto space-y-6 flex-1">
                        @php
                            $questions = $attempt->quiz?->questions ?? collect();
                            $answers = $attempt->answers ?? [];
                        @endphp

                        @forelse($questions as $idx => $question)
                            @php
                                // Detect selected answer index
                                $selectedOption = null;
                                if (is_array($answers)) {
                                    if (array_key_exists($question->id, $answers)) {
                                        $selectedOption = $answers[$question->id];
                                    } elseif (array_key_exists((string)$question->id, $answers)) {
                                        $selectedOption = $answers[(string)$question->id];
                                    } else {
                                        foreach ($answers as $ans) {
                                            if (is_array($ans) && isset($ans['question_id']) && $ans['question_id'] == $question->id) {
                                                $selectedOption = $ans['selected'] ?? $ans['selected_index'] ?? null;
                                                break;
                                            }
                                        }
                                    }
                                }
                                $isCorrect = ($selectedOption !== null && (int)$selectedOption === (int)$question->correct_index);
                                $letters = ['A', 'B', 'C', 'D'];
                            @endphp

                            <div class="p-4 rounded-xl border {{ $isCorrect ? 'border-green-200 bg-green-50/20 dark:border-green-900/40 dark:bg-green-950/10' : 'border-red-200 bg-red-50/20 dark:border-red-900/40 dark:bg-red-950/10' }}">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="font-semibold text-sm text-gray-900 dark:text-white">
                                        Soal #{{ $idx + 1 }}
                                    </div>
                                    <div>
                                        @if($selectedOption === null)
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Tidak Dijawab</span>
                                        @elseif($isCorrect)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                                <x-heroicon-m-check class="w-3.5 h-3.5" /> Benar
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                                <x-heroicon-m-x-mark class="w-3.5 h-3.5" /> Salah
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-2 text-sm text-gray-800 dark:text-gray-200 leading-relaxed font-medium">
                                    {{ $question->body }}
                                </div>

                                {{-- Options List --}}
                                <div class="mt-3 space-y-2">
                                    @foreach($question->options ?? [] as $optIdx => $optionText)
                                        @php
                                            $isStudentChoice = ($selectedOption !== null && (int)$selectedOption === (int)$optIdx);
                                            $isCorrectChoice = ((int)$optIdx === (int)$question->correct_index);
                                        @endphp
                                        <div class="flex items-center gap-2 p-2.5 rounded-lg text-xs font-medium border
                                            @if($isStudentChoice && $isCorrectChoice)
                                                bg-green-100 border-green-300 text-green-900 dark:bg-green-900/40 dark:border-green-700 dark:text-green-200
                                            @elseif($isStudentChoice && ! $isCorrectChoice)
                                                bg-red-100 border-red-300 text-red-900 dark:bg-red-900/40 dark:border-red-700 dark:text-red-200
                                            @elseif($isCorrectChoice)
                                                bg-green-50 border-green-200 text-green-800 dark:bg-green-950/20 dark:border-green-800 dark:text-green-300
                                            @else
                                                bg-white border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300
                                            @endif
                                        ">
                                            <span class="w-5 h-5 flex items-center justify-center rounded-full text-[10px] font-bold {{ $isStudentChoice ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                                {{ $letters[$optIdx] ?? ($optIdx + 1) }}
                                            </span>
                                            <span class="flex-1">{{ $optionText }}</span>
                                            @if($isStudentChoice)
                                                <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded bg-white/70 dark:bg-black/30">Pilihan Siswa</span>
                                            @endif
                                            @if($isCorrectChoice)
                                                <span class="text-[11px] font-semibold text-green-700 dark:text-green-400">★ Kunci Jawaban</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Explanation Note --}}
                                @if(!empty($question->explanation))
                                    <div class="mt-3 p-3 bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/50 rounded-lg text-xs">
                                        <div class="font-semibold text-blue-900 dark:text-blue-300 flex items-center gap-1.5 mb-0.5">
                                            <x-heroicon-m-light-bulb class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                            Pembahasan Guru:
                                        </div>
                                        <div class="text-blue-800 dark:text-blue-200 leading-relaxed">
                                            {{ $question->explanation }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada butir soal dalam kuis ini.</p>
                        @endforelse
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 flex justify-end">
                        <button
                            type="button"
                            wire:click="closeAttemptModal"
                            class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
