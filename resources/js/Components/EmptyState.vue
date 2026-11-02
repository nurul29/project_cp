<script setup>
defineProps({
    title: { type: String, default: 'Belum ada data' },
    description: { type: String, default: 'Konten baru akan muncul di sini.' },
    icon: { type: String, default: '✧' },
});
</script>

<template>
    <div class="rounded-2xl border border-dashed border-[#cbd9d3] bg-white px-6 py-12 text-center">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#e9f7f1] text-xl text-[#0c7774]">{{ icon }}</span>
        <h2 class="mt-4 font-bold">{{ title }}</h2>
        <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-[#667b79]">{{ description }}</p>
        <slot />
    </div>
</template>
