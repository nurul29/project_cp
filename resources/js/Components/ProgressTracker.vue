<script setup>
defineProps({
    completed: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
    percentage: { type: Number, default: 0 },
});
</script>

<template>
    <section class="rounded-2xl border border-[#e4eae7] bg-white p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-bold">Progres belajar</p>
                <p class="mt-1 text-xs text-[#667b79]">{{ completed }} dari {{ total }} aktivitas selesai</p>
            </div>
            <span class="text-lg font-extrabold text-[#0c7774]">{{ percentage }}%</span>
        </div>
        <div class="mt-4 h-2 overflow-hidden rounded-full bg-[#e9f7f1]" role="progressbar" :aria-valuenow="percentage" aria-valuemin="0" aria-valuemax="100" :aria-label="`${percentage}% progres belajar`">
            <div class="h-full rounded-full bg-[#0c7774] transition-[width] duration-500" :style="{ width: `${percentage}%` }"></div>
        </div>
    </section>
</template>
