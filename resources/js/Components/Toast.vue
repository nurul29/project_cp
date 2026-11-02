<script setup>
import { useToast } from '@/Composables/useToast';

const { messages, dismissToast } = useToast();
</script>

<template>
    <div class="pointer-events-none fixed right-4 top-4 z-50 flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-2" aria-live="polite" aria-atomic="false">
        <TransitionGroup name="toast">
            <div v-for="item in messages" :key="item.id" class="pointer-events-auto flex items-start gap-3 rounded-2xl border bg-white p-4 shadow-lg" :class="item.type === 'error' ? 'border-[#f3c8b8]' : 'border-[#cde9dc]'">
                <span class="text-sm font-bold" :class="item.type === 'error' ? 'text-[#9b4229]' : 'text-[#075f5d]'">{{ item.type === 'error' ? '!' : '✓' }}</span>
                <p class="flex-1 text-sm text-[#193b3a]">{{ item.message }}</p>
                <button type="button" class="rounded px-1 text-[#667b79] hover:text-[#193b3a]" aria-label="Tutup notifikasi" @click="dismissToast(item.id)">×</button>
            </div>
        </TransitionGroup>
    </div>
</template>
