<script setup>
import { Link } from '@inertiajs/vue3';
import StatusPill from '@/Components/StatusPill.vue';

defineProps({ item: { type: Object, required: true } });
const dueLabel = (value) => value ? new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(value)) : 'Tanpa batas pengumpulan';
</script>

<template>
    <Link :href="item.href" class="flex flex-wrap items-center gap-3 rounded-xl border border-[#e4eae7] bg-white p-4 transition hover:border-[#b8dcd0]">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#e9f7f1] text-lg">{{ item.icon }}</span>
        <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-bold">{{ item.title }}</span>
            <span class="mt-1 block text-xs text-[#667b79]">{{ item.subject }} · {{ dueLabel(item.dueAt) }}</span>
        </span>
        <StatusPill :status="item.status || 'active'" />
    </Link>
</template>
