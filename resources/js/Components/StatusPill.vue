<script setup>
import { computed } from 'vue';

const props = defineProps({ status: { type: String, default: '' } });
const labels = {
    active: 'Aktif',
    closed: 'Ditutup',
    pending: 'Menunggu guru',
    graded: 'Dinilai',
    saved: 'Tersimpan',
};
const label = computed(() => labels[props.status] ?? props.status ?? 'Belum dikerjakan');
const classes = computed(() => ({
    graded: 'bg-[#e9f7f1] text-[#075f5d]',
    pending: 'bg-[#fff4df] text-[#8a5a12]',
    active: 'bg-[#e9f7f1] text-[#075f5d]',
    closed: 'bg-[#f1f3f2] text-[#667b79]',
    saved: 'bg-[#edf2ff] text-[#425d9b]',
}[props.status] ?? 'bg-[#f1f3f2] text-[#667b79]'));
</script>

<template>
    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold" :class="classes">{{ label }}</span>
</template>
