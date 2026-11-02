<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, shallowRef, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import * as pdfjsLib from 'pdfjs-dist';
import workerSrc from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = workerSrc;

const CSS_UNITS = 96 / 72;

const props = defineProps({
    source: { type: String, required: true },
    title: { type: String, required: true },
    description: { type: String, default: '' },
    quizzes: { type: Array, default: () => [] },
});

const readerSection = ref(null);
const canvasStage = ref(null);
const canvas = ref(null);
const pdfDocument = shallowRef(null);
const loading = ref(true);
const error = ref('');
const pageNumber = ref(1);
const pageCount = ref(0);
const zoom = ref(100);
const renderedZoom = ref(100);
const fitMode = ref('custom'); // 'custom' (100% default) | 'width' | 'page'
const focusMode = ref(false);
const useBrowserViewer = ref(false);
const viewerReloadKey = ref(1);
const loadTask = shallowRef(null);
const focusError = ref('');

let renderTask = null;
let renderVersion = 0;
let resizeObserver = null;
let lastStageWidth = 0;

const zoomPresets = [50, 75, 100, 125, 150, 175, 200, 250, 300];

const displayZoom = computed(() => (fitMode.value === 'custom' ? zoom.value : renderedZoom.value));

const iframeSource = computed(() => {
    if (!props.source) return '';
    const baseUrl = props.source.split('#')[0];
    const separator = baseUrl.includes('?') ? '&' : '?';
    const cacheBuster = `${separator}v=${viewerReloadKey.value}`;
    if (fitMode.value === 'width') {
        return `${baseUrl}${cacheBuster}#view=FitH`;
    }
    if (fitMode.value === 'page') {
        return `${baseUrl}${cacheBuster}#view=Fit`;
    }
    return `${baseUrl}${cacheBuster}#zoom=${zoom.value}`;
});

const cancelActiveRender = async () => {
    if (!renderTask) return;
    const active = renderTask;
    renderTask = null;
    try {
        active.cancel();
        await active.promise;
    } catch {
        // Expected when cancelling an in-flight PDF.js render task
    }
};

const renderPage = async () => {
    if (useBrowserViewer.value) return;
    const currentDocument = pdfDocument.value;
    const targetCanvas = canvas.value;
    if (!currentDocument || !targetCanvas) return;

    const version = ++renderVersion;
    await cancelActiveRender();
    if (version !== renderVersion || !canvas.value) return;

    try {
        const page = await currentDocument.getPage(pageNumber.value);
        if (version !== renderVersion || !canvas.value) return;

        const baseViewport = page.getViewport({ scale: CSS_UNITS });
        const stageEl = canvasStage.value;
        const availableWidth = Math.max(280, (stageEl?.clientWidth || baseViewport.width) - 32);
        const availableHeight = Math.max(360, (stageEl?.clientHeight || baseViewport.height) - 32);

        let zoomRatio;
        if (fitMode.value === 'width') {
            zoomRatio = Math.min(3.0, Math.max(0.5, availableWidth / baseViewport.width));
        } else if (fitMode.value === 'page') {
            zoomRatio = Math.min(
                3.0,
                Math.max(0.4, Math.min(availableWidth / baseViewport.width, availableHeight / baseViewport.height))
            );
        } else {
            zoomRatio = zoom.value / 100;
        }

        renderedZoom.value = Math.round(zoomRatio * 100);
        const cssScale = zoomRatio * CSS_UNITS;
        const viewport = page.getViewport({ scale: cssScale });
        const pixelRatio = window.devicePixelRatio || 1;
        const context = targetCanvas.getContext('2d');
        if (!context) {
            error.value = 'Browser tidak dapat menyiapkan kanvas pembaca PDF.';
            return;
        }

        targetCanvas.width = Math.floor(viewport.width * pixelRatio);
        targetCanvas.height = Math.floor(viewport.height * pixelRatio);
        targetCanvas.style.width = `${Math.floor(viewport.width)}px`;
        targetCanvas.style.height = `${Math.floor(viewport.height)}px`;

        const currentRenderTask = page.render({
            canvasContext: context,
            viewport: page.getViewport({ scale: cssScale * pixelRatio }),
        });
        renderTask = currentRenderTask;
        await currentRenderTask.promise;
        if (renderTask === currentRenderTask) {
            renderTask = null;
        }
    } catch (cause) {
        if (
            version !== renderVersion ||
            cause?.name === 'RenderingCancelledException' ||
            String(cause?.message || '').toLowerCase().includes('cancel')
        ) {
            return;
        }
        error.value = 'Halaman PDF gagal ditampilkan di kanvas. Menampilkan penampil PDF bawaan browser.';
        useBrowserViewer.value = true;
    }
};

watch(() => props.source, async (source) => {
    await cancelActiveRender();
    loadTask.value?.destroy();
    pdfDocument.value?.destroy();
    pdfDocument.value = null;
    loading.value = true;
    error.value = '';
    pageNumber.value = 1;
    pageCount.value = 0;
    if (!source) {
        loading.value = false;
        error.value = 'File PDF tidak tersedia.';
        return;
    }

    const task = pdfjsLib.getDocument(source);
    loadTask.value = task;

    try {
        const timeoutPromise = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('PDF worker timeout')), 8000)
        );
        const document = await Promise.race([task.promise, timeoutPromise]);
        if (loadTask.value !== task) return;
        pdfDocument.value = document;
        pageCount.value = document.numPages;
        loading.value = false;
        await nextTick();
        lastStageWidth = canvasStage.value?.clientWidth || 0;
        await renderPage();
    } catch {
        if (loadTask.value === task) {
            loading.value = false;
            useBrowserViewer.value = true;
        }
    }
}, { immediate: true });

watch([pageNumber, zoom, fitMode, useBrowserViewer], async () => {
    viewerReloadKey.value++;
    await nextTick();
    await renderPage();
});

const setPage = (value) => {
    const page = Number(value);
    if (Number.isInteger(page) && page >= 1 && page <= pageCount.value) {
        pageNumber.value = page;
    }
};

const adjustZoom = (amount) => {
    const currentZoom = fitMode.value === 'custom' ? zoom.value : renderedZoom.value;
    fitMode.value = 'custom';
    zoom.value = Math.min(300, Math.max(50, Math.round((currentZoom + amount) / 5) * 5));
};

const onSelectZoom = (event) => {
    const value = event.target.value;
    if (value === 'width' || value === 'page') {
        fitMode.value = value;
    } else {
        fitMode.value = 'custom';
        zoom.value = Number(value) || 100;
    }
};

const changePage = (event) => {
    setPage(event.target.value);
    event.target.value = pageNumber.value;
};

const applyDefault100Zoom = () => {
    fitMode.value = 'custom';
    zoom.value = 100;
    renderedZoom.value = 100;
    viewerReloadKey.value++;
};

const toggleFocus = async () => {
    focusError.value = '';
    try {
        if (document.fullscreenElement === readerSection.value) {
            await document.exitFullscreen();
        } else if (readerSection.value?.requestFullscreen) {
            applyDefault100Zoom();
            await readerSection.value.requestFullscreen();
        } else {
            focusMode.value = !focusMode.value;
            if (focusMode.value) {
                applyDefault100Zoom();
            }
        }
    } catch {
        focusMode.value = !focusMode.value;
        if (focusMode.value) {
            applyDefault100Zoom();
        }
    }
};

const updateFocusMode = async () => {
    const isFullscreen = document.fullscreenElement === readerSection.value;
    focusMode.value = isFullscreen;
    if (isFullscreen) {
        applyDefault100Zoom();
    }
    await nextTick();
    setTimeout(async () => {
        if (isFullscreen) {
            applyDefault100Zoom();
        }
        lastStageWidth = canvasStage.value?.clientWidth || 0;
        await renderPage();
    }, 150);
};

onMounted(() => {
    document.addEventListener('fullscreenchange', updateFocusMode);
    resizeObserver = new ResizeObserver(() => {
        const currentWidth = canvasStage.value?.clientWidth || 0;
        if (currentWidth > 0 && Math.abs(currentWidth - lastStageWidth) > 4) {
            lastStageWidth = currentWidth;
            if (fitMode.value !== 'custom') {
                renderPage();
            }
        }
    });
    if (canvasStage.value) {
        lastStageWidth = canvasStage.value.clientWidth || 0;
        resizeObserver.observe(canvasStage.value);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('fullscreenchange', updateFocusMode);
    resizeObserver?.disconnect();
    cancelActiveRender();
    loadTask.value?.destroy();
    pdfDocument.value?.destroy();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Detail Koleksi di Bagian Atas -->
        <aside v-if="!focusMode" class="rounded-2xl border border-[#e4eae7] bg-white p-5">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-extrabold uppercase tracking-[.15em] text-[#0c7774]">Detail koleksi</p>
                    <h2 class="mt-1.5 text-base font-extrabold text-[#193b3a]">{{ title }}</h2>
                    <p class="mt-1 text-sm leading-6 text-[#667b79]">
                        {{ description || 'Materi belajar yang dibagikan untuk kelasmu.' }}
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a
                        :href="source"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-9 items-center rounded-xl border border-[#dce6e1] bg-[#f4f8f5] px-3.5 text-xs font-bold text-[#0c7774] hover:bg-[#e9f7f1]"
                    >
                        Buka PDF di tab baru ↗
                    </a>
                </div>
            </div>

            <div class="mt-4 border-t border-[#e4eae7] pt-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#193b3a]">Kuis terkait</h3>
                    <p v-if="!quizzes.length" class="text-xs text-[#667b79]">
                        Belum ada kuis yang ditautkan ke materi ini.
                    </p>
                </div>
                <ul v-if="quizzes.length" class="mt-2.5 flex flex-wrap gap-2">
                    <li v-for="quiz in quizzes" :key="quiz.id">
                        <Link
                            :href="quiz.href"
                            class="inline-flex items-center rounded-lg bg-[#f4f8f5] px-3 py-2 text-xs font-bold text-[#075f5d] hover:bg-[#e9f7f1]"
                        >
                            {{ quiz.title }} →
                        </Link>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Kotak Pembaca PDF Lebar Penuh -->
        <section
            ref="readerSection"
            class="flex min-w-0 w-full flex-col overflow-hidden rounded-2xl border border-[#e4eae7] bg-white"
            :class="focusMode ? 'fixed inset-0 z-50 h-screen w-screen rounded-none border-0' : ''"
        >
            <!-- Toolbar Pembaca PDF -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e4eae7] bg-white px-3 py-2.5">
                <div class="flex flex-wrap items-center gap-2">
                    <template v-if="!useBrowserViewer">
                        <button
                            type="button"
                            class="min-h-9 rounded-lg border border-[#dce6e1] px-3 text-xs font-bold text-[#193b3a] hover:bg-[#f4f8f5] disabled:opacity-40"
                            :disabled="pageNumber <= 1 || loading"
                            aria-label="Halaman sebelumnya"
                            @click="setPage(pageNumber - 1)"
                        >
                            ←
                        </button>
                        <label class="flex items-center gap-2 text-xs font-semibold text-[#667b79]">
                            Halaman
                            <input
                                :value="pageNumber"
                                type="number"
                                min="1"
                                :max="pageCount"
                                class="h-9 w-16 rounded-lg border border-[#dce6e1] px-2 text-center text-xs font-bold text-[#193b3a]"
                                :disabled="loading"
                                aria-label="Nomor halaman"
                                @change="changePage"
                            />
                            dari {{ pageCount || '—' }}
                        </label>
                        <button
                            type="button"
                            class="min-h-9 rounded-lg border border-[#dce6e1] px-3 text-xs font-bold text-[#193b3a] hover:bg-[#f4f8f5] disabled:opacity-40"
                            :disabled="pageNumber >= pageCount || loading"
                            aria-label="Halaman berikutnya"
                            @click="setPage(pageNumber + 1)"
                        >
                            →
                        </button>
                    </template>
                    <span v-else class="text-xs font-bold text-[#193b3a]">
                        📄 {{ title }}
                    </span>
                </div>

                <!-- Kontrol Ukuran & Tampilan -->
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="min-h-9 rounded-lg border px-3 text-xs font-bold transition"
                        :class="fitMode === 'width' ? 'border-[#0c7774] bg-[#e9f7f1] text-[#075f5d]' : 'border-[#dce6e1] text-[#193b3a] hover:bg-[#f4f8f5]'"
                        :aria-pressed="fitMode === 'width'"
                        @click="fitMode = 'width'"
                    >
                        Pas lebar
                    </button>
                    <button
                        type="button"
                        class="min-h-9 rounded-lg border px-3 text-xs font-bold transition"
                        :class="fitMode === 'page' ? 'border-[#0c7774] bg-[#e9f7f1] text-[#075f5d]' : 'border-[#dce6e1] text-[#193b3a] hover:bg-[#f4f8f5]'"
                        :aria-pressed="fitMode === 'page'"
                        @click="fitMode = 'page'"
                    >
                        Pas halaman
                    </button>

                    <div class="flex items-center rounded-lg border border-[#dce6e1] bg-[#f9fbfa] p-0.5">
                        <button
                            type="button"
                            class="h-8 w-8 rounded-md text-sm font-extrabold text-[#193b3a] hover:bg-white disabled:opacity-40"
                            :disabled="displayZoom <= 50"
                            aria-label="Perkecil"
                            title="Perkecil tampilan (−25%)"
                            @click="adjustZoom(-25)"
                        >
                            −
                        </button>
                        <select
                            :value="fitMode === 'custom' ? String(zoom) : fitMode"
                            aria-label="Pilih ukuran perbesaran PDF"
                            class="h-8 border-0 bg-transparent px-2 text-center text-xs font-bold text-[#193b3a] focus:ring-0"
                            @change="onSelectZoom"
                        >
                            <option value="width">Pas Lebar ({{ renderedZoom }}%)</option>
                            <option value="page">Pas Halaman ({{ renderedZoom }}%)</option>
                            <option v-for="preset in zoomPresets" :key="preset" :value="String(preset)">
                                {{ preset }}%
                            </option>
                        </select>
                        <button
                            type="button"
                            class="h-8 w-8 rounded-md text-sm font-extrabold text-[#193b3a] hover:bg-white disabled:opacity-40"
                            :disabled="displayZoom >= 300"
                            aria-label="Perbesar"
                            title="Perbesar tampilan (+25%)"
                            @click="adjustZoom(25)"
                        >
                            +
                        </button>
                    </div>

                    <button
                        type="button"
                        class="min-h-9 rounded-lg border border-[#dce6e1] px-3 text-xs font-bold text-[#193b3a] hover:bg-[#f4f8f5]"
                        @click="useBrowserViewer = !useBrowserViewer"
                    >
                        {{ useBrowserViewer ? 'Mode Kanvas' : 'Mode PDF Browser' }}
                    </button>

                    <button
                        type="button"
                        class="min-h-9 rounded-lg bg-[#0c7774] px-3 text-xs font-bold text-white hover:bg-[#075f5d]"
                        :aria-pressed="focusMode"
                        @click="toggleFocus"
                    >
                        {{ focusMode ? '✕ Keluar layar penuh' : '⛶ Mode fokus (100%)' }}
                    </button>
                </div>
            </div>

            <p v-if="focusError" class="px-3 pt-2 text-xs font-semibold text-red-700" role="alert">{{ focusError }}</p>

            <!-- Area Baca PDF -->
            <div
                ref="canvasStage"
                class="flex flex-1 w-full justify-center overflow-auto bg-[#525659]"
                :class="[
                    focusMode ? 'h-[calc(100vh-58px)] p-4' : 'min-h-[78vh] max-h-[86vh] p-4 sm:p-6',
                    useBrowserViewer ? '!p-0' : '',
                ]"
                aria-live="polite"
            >
                <p v-if="loading" class="my-auto text-sm font-semibold text-white">Memuat PDF…</p>

                <iframe
                    v-else-if="useBrowserViewer && source"
                    :key="iframeSource"
                    :src="iframeSource"
                    :title="title"
                    class="w-full border-0 bg-white"
                    :class="focusMode ? 'h-[calc(100vh-58px)] w-screen' : 'min-h-[78vh]'"
                ></iframe>

                <p v-else-if="error && !source" class="my-auto max-w-md text-center text-sm font-semibold text-red-200" role="alert">
                    {{ error }}
                </p>

                <canvas
                    v-else
                    ref="canvas"
                    class="mx-auto my-auto self-start bg-white shadow-2xl"
                    :aria-label="`${title}, halaman ${pageNumber} dari ${pageCount}`"
                ></canvas>
            </div>
        </section>
    </div>
</template>
