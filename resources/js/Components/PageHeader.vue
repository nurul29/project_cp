<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    eyebrow: { type: String, default: '' },
});
</script>

<template>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p v-if="eyebrow" class="mb-2 text-[11px] font-bold uppercase tracking-[.16em] text-[#0c7774]">{{ eyebrow }}</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-[#193b3a] sm:text-3xl">{{ title }}</h1>
            <p v-if="description" class="mt-2 max-w-2xl text-sm leading-6 text-[#667b79]">{{ description }}</p>
        </div>
        <slot name="action" />
    </div>
</template>
