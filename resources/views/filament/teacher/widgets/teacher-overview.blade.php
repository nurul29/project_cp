<x-filament-widgets::widget>
    <div class="teacher-dashboard">
        <!-- Context Pills -->
        <div class="teacher-context">
            @if (isset($subjectNames) && $subjectNames->isNotEmpty())
                @foreach ($subjectNames as $subj)
                    <span class="soft-pill">{{ $subj }}</span>
                @endforeach
            @else
                <span class="soft-pill">{{ $subjectName }}</span>
            @endif
            <span>
                @if ($classrooms->isNotEmpty())
                    {{ $classrooms->pluck('name')->join(' • ') }}
                @else
                    Belum ada kelas ditugaskan
                @endif
            </span>
            <span>Semester {{ $settings->semester }} {{ $settings->school_year }} • Koleksi perpustakaan sekolah</span>
        </div>

        <!-- Hero Section according to Mockup -->
        <section class="student-hero library-teacher-hero">
            <div class="copy">
                <div class="mini">BERBAGI BACAAN, MEMBUKA WAWASAN</div>
                <h2>Bangun koleksi yang<br />ingin dibaca siswa.</h2>
                <p>Tambahkan buku, materi, dan modul PDF. Lengkapi bacaan dengan tautan video yang diputar di aplikasi.</p>
                <a href="{{ $materialsUrl }}" class="lightbtn">Kelola koleksi saya →</a>
            </div>
            <div class="hero-illustration" aria-hidden="true">
                <svg width="145" height="160" viewBox="0 0 145 160" fill="none">
                    <rect x="9" y="33" width="42" height="111" rx="6" fill="#E8AB67"/>
                    <path d="M18 47H40M18 53H36M18 128H39" stroke="#81501F" stroke-width="3"/>
                    <rect x="57" y="9" width="43" height="135" rx="6" fill="#D7ECDC"/>
                    <path d="M66 28H91M66 35H85M66 128H90" stroke="#195B55" stroke-width="3"/>
                    <rect x="106" y="49" width="31" height="95" rx="6" fill="#F8D6C8"/>
                    <path d="M114 64H129M114 70H125M114 128H129" stroke="#844D4D" stroke-width="3"/>
                </svg>
            </div>
        </section>

        <!-- Teacher 4 Stat Cards matching Mockup -->
        <div class="teacher-stats">
            <a href="{{ $materialsUrl }}" class="teacher-stat">
                <div class="teacher-stat-top">
                    <span>Buku &amp; bacaan</span>
                    <b aria-hidden="true">▤</b>
                </div>
                <strong>{{ $readingsCount }}</strong>
                <div class="teacher-stat-bottom">
                    <span>PDF, modul, dan koleksi contoh</span>
                    <span>Koleksi</span>
                </div>
            </a>

            <a href="{{ $materialsUrl }}" class="teacher-stat">
                <div class="teacher-stat-top">
                    <span>Koleksi terbit</span>
                    <b aria-hidden="true">✓</b>
                </div>
                <strong>{{ $publishedMaterialsCount }}</strong>
                <div class="teacher-stat-bottom">
                    <span>Tersedia di kelas tujuan</span>
                    <span>Koleksi</span>
                </div>
            </a>

            <a href="{{ $materialsUrl }}" class="teacher-stat">
                <div class="teacher-stat-top">
                    <span>Draf koleksi</span>
                    <b aria-hidden="true">✎</b>
                </div>
                <strong>{{ $draftMaterialsCount }}</strong>
                <div class="teacher-stat-bottom">
                    <span>Siap dilengkapi dan ditinjau</span>
                    <span>Koleksi</span>
                </div>
            </a>

            <a href="{{ $materialsUrl }}" class="teacher-stat">
                <div class="teacher-stat-top">
                    <span>Materi pendukung</span>
                    <b aria-hidden="true">▶</b>
                </div>
                <strong>{{ $supportMaterialsCount }}</strong>
                <div class="teacher-stat-bottom">
                    <span>Tautan video pembelajaran</span>
                    <span>Koleksi</span>
                </div>
            </a>
        </div>

        <!-- Two Columns Layout (Koleksi saya vs Koleksi siap dibaca / Draf & Peran guru) -->
        <div class="teacher-grid">
            <!-- Left Column: Koleksi saya -->
            <section class="panel teacher-panel">
                <div class="panel-head">
                    <div>
                        <h2>Koleksi saya</h2>
                        <p>Buku dan sumber bacaan yang kamu kelola.</p>
                    </div>
                    <a href="{{ $materialsUrl }}" class="text-link">Lihat semua →</a>
                </div>

                @forelse ($materials as $material)
                    <div class="teacher-material-row">
                        <span class="teacher-doc" aria-hidden="true">
                            {{ $material->type?->value === 'video' ? '▷' : '▤' }}
                        </span>
                        <div>
                            <strong>{{ $material->title }}</strong>
                            <small>{{ $material->subject?->name ?? $subjectName }} • {{ $material->classroom?->name ?? 'Semua kelas' }}</small>
                        </div>
                        <a href="{{ \App\Filament\Teacher\Resources\MaterialResource::getUrl('edit', ['record' => $material]) }}" class="admin-btn">
                            Edit
                        </a>
                    </div>
                @empty
                    <div class="teacher-quiet">
                        <span aria-hidden="true">📖</span>
                        <h3>Mulai koleksi pertamamu</h3>
                        <p>Tambahkan buku PDF atau bahan bacaan untuk siswa.</p>
                    </div>
                @endforelse

                <a href="{{ $createMaterialUrl }}" class="teacher-add-material">
                    + Tambahkan buku atau bacaan
                </a>
            </section>

            <!-- Right Column -->
            <div class="teacher-column">
                <section class="panel teacher-panel">
                    <div class="panel-head">
                        <h2>{{ $draftMaterialsCount > 0 ? 'Draf yang perlu dilengkapi' : 'Koleksi siap dibaca' }}</h2>
                    </div>
                    <p class="teacher-ready-copy">
                        @if ($draftMaterialsCount > 0)
                            {{ $draftMaterialsCount }} koleksi belum diterbitkan. Periksa judul, kategori, kelas tujuan, dan file sebelum menerbitkan.
                        @else
                            Belum ada draf tertunda. Kamu bisa menambahkan koleksi baru atau meninjau bacaan yang sudah terbit.
                        @endif
                    </p>
                    <a href="{{ $materialsUrl }}" class="admin-btn">
                        Tinjau koleksi →
                    </a>
                </section>

                <section class="panel teacher-panel upload-guide">
                    <h2>Peran guru di perpustakaan</h2>
                    <p>Memilih bacaan, mengunggah koleksi, dan melengkapi informasi agar siswa mudah menemukan sumber yang sesuai.</p>
                    <p><strong>Mata pelajaran</strong> menjadi kategori koleksi. <strong>Tautan video</strong> menjadi sumber pendukung bacaan.</p>
                </section>

                @if ($classrooms->isNotEmpty())
                    <section class="panel teacher-panel teacher-class-panel">
                        <span class="teacher-overline">KELAS YANG DIAMPU</span>
                        @foreach ($classesSummary as $cls)
                            <div class="teacher-class-summary">
                                <div>
                                    <h2>{{ $cls['name'] }}</h2>
                                    <p>{{ $cls['subject'] }}</p>
                                </div>
                                <span><b>{{ $cls['studentCount'] }}</b> siswa aktif</span>
                            </div>
                        @endforeach
                        <div class="teacher-class-footer">
                            <span>Nilai &amp; umpan balik</span>
                            <a href="{{ $gradeReportUrl }}" class="text-link">Buka rekap →</a>
                        </div>
                    </section>
                @endif
            </div>
        </div>

        <!-- Assessment Support Banner -->
        <section class="library-support qz-home-support">
            <div>
                <h2>Lengkapi bacaan dengan kuis</h2>
                <p>Buat soal pilihan ganda dan pantau hasil belajar siswa.</p>
            </div>
            <a href="{{ $quizzesUrl }}" class="secondary">
                Kelola kuis →
            </a>
        </section>
    </div>
</x-filament-widgets::widget>
