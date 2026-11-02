<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ grades: { type: Array, default: () => [] } });

const formatDate = (value) => {
    if (!value) return '—';
    try {
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(value));
    } catch {
        return '—';
    }
};
</script>

<template>
    <div class="overflow-x-auto rounded-2xl border border-[#e4eae7] bg-white shadow-sm">
        <table class="w-full min-w-[680px] border-collapse text-left text-sm">
            <thead class="bg-[#eef4ee] text-[11px] uppercase tracking-wide text-[#4e655f]">
                <tr>
                    <th class="px-5 py-4 font-bold">Mata pelajaran</th>
                    <th class="px-5 py-4 font-bold">Judul kuis</th>
                    <th class="px-5 py-4 font-bold">Ringkasan jawaban</th>
                    <th class="px-5 py-4 font-bold">Waktu selesai</th>
                    <th class="px-5 py-4 font-bold">Nilai akhir</th>
                    <th class="px-5 py-4 text-right font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e4eae7]">
                <tr v-for="grade in grades" :key="grade.id" class="hover:bg-[#f9fbfa]">
                    <td class="px-5 py-4 font-bold text-[#193b3a]">{{ grade.subject }}</td>
                    <td class="px-5 py-4 font-semibold text-[#193b3a]">{{ grade.activity }}</td>
                    <td class="px-5 py-4 text-xs text-[#526764]">{{ grade.feedback || '—' }}</td>
                    <td class="px-5 py-4 text-xs text-[#667b79]">{{ formatDate(grade.gradedAt) }}</td>
                    <td class="px-5 py-4">
                        <span
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-extrabold"
                            :class="(grade.score ?? 0) >= 75 ? 'bg-[#e6f3ed] text-[#17694e]' : 'bg-[#fff3db] text-[#9a6223]'"
                        >
                            {{ grade.score ?? '—' }} / 100
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <Link
                            v-if="grade.href"
                            :href="grade.href"
                            class="inline-flex items-center rounded-lg border border-[#d7e9df] bg-[#f7fbf8] px-3 py-1.5 text-xs font-bold text-[#0c7774] hover:bg-[#e9f6ef]"
                        >
                            Lihat kuis →
                        </Link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
