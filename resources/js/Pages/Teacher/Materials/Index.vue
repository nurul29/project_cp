<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    pageMode: { type: String, default: 'materials' },
    materials: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    teachingSubjects: { type: Array, default: () => [] },
    allGrades: { type: Array, default: () => [] },
    teachingGrades: { type: Array, default: () => [] },
    classrooms: { type: Array, default: () => [] },
    teachingClassrooms: { type: Array, default: () => [] },
    genres: { type: Array, default: () => [
        'Fiksi & Cerita Pendek',
        'Novel & Sastra Remaja',
        'Cerita Rakyat & Dongeng',
        'Sains & Teknologi Populer',
        'Sejarah, Budaya & Biografi',
        'Ensiklopedia & Pengetahuan Umum',
        'Pengembangan Diri & Motivasi',
        'Puisi, Pantun & Drama',
        'Komik Edukasi & Cerita Bergambar',
        'Artikel & Literasi Umum',
    ] },
    filters: { type: Object, default: () => ({ search: '', subject_id: '', target_grade: '', classroom_id: '' }) },
});

const search = ref(props.filters.search || '');
const selectedSubject = ref(props.filters.subject_id || '');
const selectedGrade = ref(props.filters.target_grade || '');
const fileInput = ref(null);
const isDragging = ref(false);
const uploadPreviewUrl = ref(null);
const previewModalItem = ref(null);

const applyFilters = () => {
    const routeName = props.pageMode === 'library'
        ? 'guru.library.index'
        : (props.pageMode === 'videos' ? 'guru.videos.index' : 'guru.materials.index');

    router.get(route(routeName), {
        search: search.value || undefined,
        subject_id: selectedSubject.value || undefined,
        target_grade: selectedGrade.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const pageTitle = computed(() => 'Koleksi Digital');

const pageSubtitle = computed(() => 'Kelola seluruh koleksi pembelajaran sekolah: materi pelajaran (PDF & video) dan buku bacaan umum.');

const form = useForm({
    collection_category: 'learning',
    content_format: 'pdf',
    title: '',
    subject_id: props.teachingSubjects[0]?.id || props.subjects[0]?.id || '',
    genre: props.genres[0] || 'Fiksi & Cerita Pendek',
    author: '',
    target_grade: '',
    description: '',
    file: null,
    video_url: '',
    is_published: true,
});

const onCategoryChange = () => {
    if (form.collection_category === 'general') {
        form.subject_id = '';
        form.target_grade = '';
        form.video_url = '';
        form.content_format = 'pdf';
        form.genre = props.genres[0] || 'Fiksi & Cerita Pendek';
    } else {
        form.genre = '';
        form.author = '';
        form.subject_id = props.teachingSubjects[0]?.id || props.subjects[0]?.id || '';
    }
};

const onFormatChange = () => {
    if (form.content_format === 'video') {
        removeFile();
    } else {
        form.video_url = '';
    }
};

const resetForm = () => {
    removeFile();
    form.reset();
    form.collection_category = 'learning';
    form.content_format = 'pdf';
    form.subject_id = props.teachingSubjects[0]?.id || props.subjects[0]?.id || '';
};

const myMaterials = computed(() => props.materials.filter((m) => m.is_mine));
const otherMaterials = computed(() => props.materials.filter((m) => !m.is_mine));

const getTypeLabel = (type, category, format) => {
    if (category === 'general' || type === 'reading') return '📄 Bacaan Umum';
    if (format === 'video' || type === 'video') return '🎬 Video Pembelajaran';
    return '📘 Modul PDF';
};

const formatFileSize = (bytes) => {
    if (!bytes) return '';
    return bytes < 1048576
        ? (bytes / 1024).toFixed(1) + ' KB'
        : (bytes / 1048576).toFixed(1) + ' MB';
};

const setUploadFile = (file) => {
    if (uploadPreviewUrl.value) {
        URL.revokeObjectURL(uploadPreviewUrl.value);
        uploadPreviewUrl.value = null;
    }
    form.file = file || null;
    if (file && (file.type === 'application/pdf' || file.name?.toLowerCase().endsWith('.pdf'))) {
        uploadPreviewUrl.value = URL.createObjectURL(file);
    }
};

const handleFileChange = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        setUploadFile(file);
    }
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (file) {
        setUploadFile(file);
    }
};

const removeFile = () => {
    setUploadFile(null);
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const getYoutubeId = (url) => {
    if (!url) return null;
    const match = url.match(/(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/|v\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i);
    return match ? match[1] : null;
};

const getYoutubeEmbedUrl = (url) => {
    const ytId = getYoutubeId(url);
    return ytId ? `https://www.youtube.com/embed/${ytId}?rel=0&modestbranding=1&playsinline=1` : null;
};

const submit = () => {
    form.post(route('guru.materials.store'), {
        forceFormData: true,
        onSuccess: () => {
            resetForm();
        },
    });
};

const deleteMaterial = (id) => {
    if (confirm('Apakah kamu yakin ingin menghapus materi ini?')) {
        router.delete(route('guru.materials.destroy', id), {
            preserveScroll: true,
        });
    }
};

onBeforeUnmount(() => {
    if (uploadPreviewUrl.value) {
        URL.revokeObjectURL(uploadPreviewUrl.value);
    }
});
</script>

<template>
    <Head :title="`${pageTitle} — SUNDIGITAL`" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Ruang guru</div>
                <h1>{{ pageTitle }}</h1>
                <p>{{ pageSubtitle }}</p>
            </div>
        </div>

        <div class="split-layout">
            <!-- Left: Materials List -->
            <section class="panel">
                <div class="panel-head">
                    <h2>Semua koleksi ({{ materials.length }})</h2>
                </div>

                <!-- Filters -->
                <div class="filter-row" style="margin-bottom: 16px;">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari judul..."
                        style="border: 1px solid #dce6e2; border-radius: 9px; padding: 7px 12px; font-size: 12px; flex: 1; min-width: 130px;"
                        @input="applyFilters"
                    />
                    <select
                        v-model="selectedSubject"
                        style="border: 1px solid #dce6e2; border-radius: 9px; padding: 7px 10px; font-size: 12px;"
                        @change="applyFilters"
                    >
                        <option value="">Semua Mapel</option>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <select
                        v-model="selectedGrade"
                        style="border: 1px solid #dce6e2; border-radius: 9px; padding: 7px 10px; font-size: 12px;"
                        @change="applyFilters"
                    >
                        <option value="">Semua Tingkat</option>
                        <option value="all">Semua Kelas (Umum)</option>
                        <option v-for="g in allGrades" :key="g" :value="g">Kelas {{ g }}</option>
                    </select>
                </div>

                <!-- Bagian 1: Koleksi Unggahan Saya -->
                <div style="margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #e4ece9; padding-bottom: 8px;">
                        <strong style="font-size: 13.5px; color: #075f5d;">👤 Koleksi Unggahan Saya ({{ myMaterials.length }})</strong>
                    </div>

                    <div v-if="myMaterials.length" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 12px;">
                        <article
                            v-for="m in myMaterials"
                            :key="m.id"
                            style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid #bce3d8; border-radius: 14px; background: #f9fdfb; padding: 14px; gap: 10px;"
                        >
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
                                    <span style="font-size: 11px; font-weight: 700; background: #e2f5ee; color: #075f5d; padding: 3px 8px; border-radius: 6px;">
                                        {{ getTypeLabel(m.type) }}
                                    </span>
                                    <span style="font-size: 10.5px; color: #667b79;">{{ m.created_at_formatted }}</span>
                                </div>
                                <strong style="display: block; font-size: 13.5px; color: #193b3a; margin-bottom: 4px;">{{ m.title }}</strong>
                                <p v-if="m.description" style="margin: 0 0 8px; font-size: 11.5px; color: #5b706d; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ m.description }}
                                </p>
                                <small style="display: block; font-size: 11px; color: #4b635e;">
                                    📖 {{ m.category_display || m.subject?.name || 'Umum' }} • 🏫 {{ m.target_class_display || m.classroom?.name || 'Semua Kelas' }}
                                    <template v-if="m.size_bytes"> • {{ formatFileSize(m.size_bytes) }}</template>
                                </small>
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; border-top: 1px solid #e1efe9; padding-top: 10px;">
                                <button
                                    v-if="m.file_url || m.url"
                                    type="button"
                                    class="admin-btn"
                                    style="flex: 1; padding: 6px 10px; font-size: 11px; margin-left: 0; background: #075f5d; color: #fff; border-color: #075f5d;"
                                    @click="previewModalItem = m"
                                >
                                    👁️ Pratinjau
                                </button>
                                <a
                                    v-if="m.file_url || m.url"
                                    :href="m.file_url || m.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="admin-btn"
                                    style="padding: 6px 9px; font-size: 11px; margin-left: 0;"
                                >
                                    ↗
                                </a>
                                <button
                                    v-if="m.can_delete"
                                    type="button"
                                    class="admin-btn danger"
                                    style="padding: 6px 9px; font-size: 11px; margin-left: 0;"
                                    @click="deleteMaterial(m.id)"
                                >
                                    🗑️
                                </button>
                            </div>
                        </article>
                    </div>
                    <p v-else style="font-size: 12px; color: #667b79; background: #f8fbfa; border: 1px dashed #d5e2dd; border-radius: 10px; padding: 14px; text-align: center;">
                        Anda belum mengunggah koleksi pada filter ini.
                    </p>
                </div>

                <!-- Bagian 2: Koleksi dari Guru Lain -->
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #e4ece9; padding-bottom: 8px;">
                        <strong style="font-size: 13.5px; color: #312e81;">🏛️ Koleksi dari Guru Lain ({{ otherMaterials.length }})</strong>
                    </div>

                    <div v-if="otherMaterials.length" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 12px;">
                        <article
                            v-for="m in otherMaterials"
                            :key="m.id"
                            style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid #dce6e2; border-radius: 14px; background: #fff; padding: 14px; gap: 10px;"
                        >
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
                                    <span style="font-size: 11px; font-weight: 700; background: #eef2ff; color: #3730a3; padding: 3px 8px; border-radius: 6px;">
                                        {{ getTypeLabel(m.type) }}
                                    </span>
                                    <span style="font-size: 10.5px; color: #667b79;">{{ m.created_at_formatted }}</span>
                                </div>
                                <strong style="display: block; font-size: 13.5px; color: #193b3a; margin-bottom: 4px;">{{ m.title }}</strong>
                                <p v-if="m.description" style="margin: 0 0 8px; font-size: 11.5px; color: #5b706d; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ m.description }}
                                </p>
                                <small style="display: block; font-size: 11px; color: #4b635e;">
                                    📖 {{ m.category_display || m.subject?.name || 'Umum' }} • 🏫 {{ m.target_class_display || m.classroom?.name || 'Semua Kelas' }}
                                </small>
                                <small style="display: block; margin-top: 4px; font-size: 11px; font-weight: 600; color: #3730a3;">
                                    👩‍🏫 Oleh: {{ m.creator_name || 'Guru' }}
                                </small>
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; border-top: 1px solid #eef3f1; padding-top: 10px;">
                                <button
                                    v-if="m.file_url || m.url"
                                    type="button"
                                    class="admin-btn"
                                    style="flex: 1; padding: 6px 10px; font-size: 11px; margin-left: 0; background: #e9f7f1; color: #075f5d; border-color: #bce3d8;"
                                    @click="previewModalItem = m"
                                >
                                    👁️ Pratinjau
                                </button>
                                <a
                                    v-if="m.file_url || m.url"
                                    :href="m.file_url || m.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="admin-btn"
                                    style="padding: 6px 9px; font-size: 11px; margin-left: 0;"
                                >
                                    ↗
                                </a>
                                <button
                                    v-if="m.can_delete"
                                    type="button"
                                    class="admin-btn danger"
                                    style="padding: 6px 9px; font-size: 11px; margin-left: 0;"
                                    @click="deleteMaterial(m.id)"
                                >
                                    🗑️
                                </button>
                            </div>
                        </article>
                    </div>
                    <p v-else style="font-size: 12px; color: #667b79; background: #f8fbfa; border: 1px dashed #d5e2dd; border-radius: 10px; padding: 14px; text-align: center;">
                        Belum ada koleksi dari guru lain yang sesuai filter.
                    </p>
                </div>
            </section>

            <!-- Right: Add Material Form -->
            <section class="panel">
                <div class="panel-head">
                    <h2>Tambah koleksi baru</h2>
                </div>

                <form @submit.prevent="submit" style="display: grid; gap: 14px; margin-top: 10px;">
                    <!-- A. Pilihan Kategori Koleksi -->
                    <div class="field" style="margin: 0;">
                        <label for="material-category">Kategori Koleksi *</label>
                        <select id="material-category" v-model="form.collection_category" @change="onCategoryChange" required>
                            <option value="learning">Materi Pembelajaran (Buku pelajaran, modul, dan video)</option>
                            <option value="general">Bacaan Umum (Novel, cerpen, dongeng, biografi, dsb.)</option>
                        </select>
                    </div>

                    <!-- B. Formulir Materi Pembelajaran -->
                    <template v-if="form.collection_category === 'learning'">
                        <div class="field" style="margin: 0;">
                            <label for="material-title">Judul Materi Pembelajaran *</label>
                            <input
                                id="material-title"
                                v-model="form.title"
                                type="text"
                                placeholder="Contoh: Teorema Pythagoras & Segitiga Siku-siku"
                                required
                            />
                            <small v-if="form.errors.title" class="field-error">{{ form.errors.title }}</small>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="field" style="margin: 0;">
                                <label for="material-subject">Mata Pelajaran *</label>
                                <select id="material-subject" v-model="form.subject_id" required>
                                    <option v-for="s in teachingSubjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                                <small v-if="form.errors.subject_id" class="field-error">{{ form.errors.subject_id }}</small>
                            </div>

                            <div class="field" style="margin: 0;">
                                <label for="material-class">Kelas Tujuan</label>
                                <select id="material-class" v-model="form.target_grade">
                                    <option value="">Semua Kelas yang Diampu</option>
                                    <option v-for="g in teachingGrades" :key="g" :value="g">Kelas {{ g }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="field" style="margin: 0;">
                            <label for="content-format">Format Konten *</label>
                            <select id="content-format" v-model="form.content_format" @change="onFormatChange" required>
                                <option value="pdf">Dokumen PDF (Buku digital / Modul materi)</option>
                                <option value="video">Video Pembelajaran (YouTube)</option>
                            </select>
                        </div>
                    </template>

                    <!-- C. Formulir Bacaan Umum -->
                    <template v-else>
                        <div class="field" style="margin: 0;">
                            <label for="material-title-reading">Judul Buku / Bahan Bacaan *</label>
                            <input
                                id="material-title-reading"
                                v-model="form.title"
                                type="text"
                                placeholder="Contoh: Antologi Cerpen Remaja / Laskar Pelangi"
                                required
                            />
                            <small v-if="form.errors.title" class="field-error">{{ form.errors.title }}</small>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="field" style="margin: 0;">
                                <label for="material-genre">Genre / Kategori Bacaan *</label>
                                <select id="material-genre" v-model="form.genre" required>
                                    <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
                                </select>
                                <small v-if="form.errors.genre" class="field-error">{{ form.errors.genre }}</small>
                            </div>

                            <div class="field" style="margin: 0;">
                                <label for="material-author">Penulis (Opsional)</label>
                                <input
                                    id="material-author"
                                    v-model="form.author"
                                    type="text"
                                    placeholder="Contoh: Andrea Hirata"
                                />
                            </div>
                        </div>
                        <p style="margin: -4px 0 0; font-size: 11.5px; color: #5e746f;">
                            ℹ️ Bacaan umum dapat diakses oleh seluruh siswa tanpa dikaitkan ke mata pelajaran atau kelas tertentu.
                        </p>
                    </template>

                    <!-- Berkas PDF (bila format PDF atau Bacaan Umum) -->
                    <div v-if="form.collection_category === 'general' || form.content_format === 'pdf'" class="field" style="margin: 0;">
                        <label for="material-file">Unggah Berkas PDF (Maks. 50 MB) *</label>
                        <div
                            class="upload-dropzone"
                            :class="{ dragging: isDragging }"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop"
                        >
                            <span class="upload-symbol" aria-hidden="true">↑</span>
                            <strong>Pilih file PDF atau seret ke sini</strong>
                            <p>Format PDF • Maksimal 50 MB</p>
                            <input
                                id="material-file"
                                ref="fileInput"
                                type="file"
                                accept=".pdf,application/pdf"
                                @change="handleFileChange"
                            />
                        </div>

                        <div v-if="form.file" class="chosen-file">
                            <span class="file-type-mark">PDF</span>
                            <div>
                                <strong>{{ form.file.name }}</strong>
                                <small>{{ formatFileSize(form.file.size) }} • Siap diunggah</small>
                            </div>
                            <button type="button" class="text-link" @click="removeFile">Hapus</button>
                        </div>

                        <!-- Live Preview Sebelum Upload -->
                        <div
                            v-if="uploadPreviewUrl"
                            style="margin-top: 12px; border: 1px solid #bce3d8; border-radius: 12px; background: #f3faf7; padding: 10px;"
                        >
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: #075f5d;">
                                <span>👁️ Pratinjau Dokumen (In-App PDF Reader)</span>
                                <a :href="uploadPreviewUrl" target="_blank" rel="noopener" style="text-decoration: underline;">Buka penuh ↗</a>
                            </div>
                            <iframe
                                :src="`${uploadPreviewUrl}#zoom=100`"
                                title="Pratinjau Upload PDF"
                                style="width: 100%; height: 380px; border: 1px solid #dce6e1; border-radius: 8px; background: #fff;"
                            ></iframe>
                        </div>

                        <small v-if="form.errors.file" class="field-error">{{ form.errors.file }}</small>
                    </div>

                    <!-- Tautan Video (bila Video Pembelajaran) -->
                    <div v-if="form.collection_category === 'learning' && form.content_format === 'video'" class="field" style="margin: 0;">
                        <label for="material-video">Tautan Video Pembelajaran (YouTube) *</label>
                        <input
                            id="material-video"
                            v-model="form.video_url"
                            type="url"
                            placeholder="https://www.youtube.com/watch?v=..."
                            required
                        />
                        <small v-if="form.errors.video_url" class="field-error">{{ form.errors.video_url }}</small>

                        <div
                            v-if="getYoutubeEmbedUrl(form.video_url)"
                            style="margin-top: 12px; border: 1px solid #bce3d8; border-radius: 12px; background: #f3faf7; padding: 10px;"
                        >
                            <div style="margin-bottom: 8px; font-size: 12px; font-weight: 700; color: #075f5d;">
                                🎬 Pemutar Video Dalam Aplikasi (In-App Player)
                            </div>
                            <iframe
                                :src="getYoutubeEmbedUrl(form.video_url)"
                                title="Pratinjau Video"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                style="width: 100%; aspect-ratio: 16 / 9; border: 0; border-radius: 8px; background: #000;"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </div>

                    <div class="field" style="margin: 0;">
                        <label for="material-desc">Keterangan Singkat</label>
                        <textarea
                            id="material-desc"
                            v-model="form.description"
                            placeholder="Apa yang akan dipelajari siswa dari materi ini?"
                            style="min-height: 80px;"
                        ></textarea>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 6px;">
                        <button
                            type="button"
                            class="secondary"
                            style="flex: 1;"
                            @click="resetForm"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="primary"
                            :disabled="form.processing"
                            style="flex: 2;"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <!-- Modal Pratinjau Koleksi -->
        <div
            v-if="previewModalItem"
            style="position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; background: rgba(15, 35, 34, 0.65); padding: 16px;"
            @click.self="previewModalItem = null"
        >
            <div style="width: 100%; max-width: 1040px; max-height: 92vh; display: flex; flex-direction: column; overflow: hidden; border-radius: 18px; background: #fff; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid #e4eae7; padding: 14px 20px;">
                    <div>
                        <strong style="font-size: 15px; color: #193b3a;">{{ previewModalItem.title }}</strong>
                        <p style="margin: 2px 0 0; font-size: 12px; color: #667b79;">
                            {{ previewModalItem.subject?.name }} • {{ previewModalItem.target_class_display || previewModalItem.classroom?.name || 'Semua Kelas' }} • Oleh: {{ previewModalItem.creator_name || 'Guru' }}
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <a
                            :href="previewModalItem.file_url || previewModalItem.url"
                            target="_blank"
                            rel="noopener"
                            class="admin-btn"
                            style="padding: 6px 12px; font-size: 12px;"
                        >
                            Buka di Tab Baru ↗
                        </a>
                        <button
                            type="button"
                            class="admin-btn danger"
                            style="padding: 6px 12px; font-size: 12px;"
                            @click="previewModalItem = null"
                        >
                            ✕ Tutup
                        </button>
                    </div>
                </div>
                <div style="flex: 1; background: #0f2322; min-height: 75vh; display: flex; align-items: center; justify-content: center;">
                    <iframe
                        v-if="getYoutubeEmbedUrl(previewModalItem.url)"
                        :src="getYoutubeEmbedUrl(previewModalItem.url)"
                        :title="previewModalItem.title"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        style="width: 100%; aspect-ratio: 16 / 9; max-height: 78vh; border: 0; background: #000;"
                        allowfullscreen
                    ></iframe>
                    <iframe
                        v-else-if="previewModalItem.file_url"
                        :src="`${previewModalItem.file_url}#zoom=100`"
                        :title="previewModalItem.title"
                        style="width: 100%; height: 78vh; border: 0; background: #fff;"
                    ></iframe>
                    <div v-else style="padding: 40px; text-align: center; color: #fff;">
                        <p>Tautan eksternal: {{ previewModalItem.url }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
