<script setup>
import { nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const dialog = ref(null);
const usernameInput = ref(null);
const submitted = ref(false);
const form = useForm({ username: '' });

const open = (username = '') => {
    form.reset();
    form.clearErrors();
    form.username = username.trim();
    submitted.value = false;

    if (!dialog.value?.open) dialog.value?.showModal();

    nextTick(() => usernameInput.value?.focus());
};

const close = () => {
    dialog.value?.close();
};

const submit = () => {
    form.post(route('account-help.store'), {
        preserveScroll: true,
        onSuccess: () => { submitted.value = true; },
    });
};

defineExpose({ open });
</script>

<template>
    <dialog
        ref="dialog"
        class="m-auto w-[min(92vw,30rem)] rounded-3xl border-0 bg-transparent p-0 text-[#193b3a] shadow-2xl backdrop:bg-[#102c2b]/65"
        aria-labelledby="account-help-title"
        @click.self="close"
    >
        <section class="relative rounded-3xl bg-white p-6 sm:p-8">
            <button type="button" class="absolute right-4 top-4 grid h-10 w-10 place-items-center rounded-full text-xl text-[#667b79] hover:bg-[#f1f6f3] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#0c7774]" aria-label="Tutup dialog bantuan" @click="close">×</button>
            <p class="text-[10px] font-extrabold uppercase tracking-[.16em] text-[#0c7774]">Bantuan akun</p>
            <template v-if="!submitted">
                <h2 id="account-help-title" class="mt-2 pr-8 text-2xl font-extrabold tracking-tight">Lupa kata sandi?</h2>
                <p class="mt-2 text-sm leading-6 text-[#667b79]">Ajukan bantuan pemulihan akun kepada admin sekolah.</p>
                <form class="mt-6 space-y-4" @submit.prevent="submit">
                    <div>
                        <label for="help-username" class="mb-1.5 block text-xs font-bold text-[#193b3a]">NIS (Siswa) / Nama Pengguna (Guru & Admin)</label>
                        <input
                            id="help-username"
                            ref="usernameInput"
                            v-model="form.username"
                            type="text"
                            autocomplete="username"
                            maxlength="255"
                            required
                            placeholder="Masukkan NIS untuk siswa, atau username untuk guru"
                            class="min-h-11 w-full rounded-xl border-[#dce6e1] text-sm placeholder:text-[#92a19c] focus:border-[#0c7774] focus:ring-[#0c7774]"
                            :aria-invalid="Boolean(form.errors.username)"
                            aria-describedby="help-username-error"
                        />
                        <p v-if="form.errors.username" id="help-username-error" class="mt-1 text-xs font-semibold text-red-700" role="alert">{{ form.errors.username }}</p>
                    </div>
                    <p class="rounded-xl bg-[#f4f8f5] p-3 text-xs leading-5 text-[#667b79]">
                        Admin sekolah akan memeriksa akun Anda dan menerapkan kata sandi standar sekolah. Hubungi admin sekolah setelah mengajukan bantuan.
                    </p>
                    <button type="submit" class="min-h-11 w-full rounded-xl bg-[#0c7774] px-5 text-sm font-bold text-white hover:bg-[#075f5d] disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
                        {{ form.processing ? 'Mengirim permintaan…' : 'Ajukan bantuan' }}
                    </button>
                </form>
            </template>
            <section v-else class="pt-3" role="status" aria-labelledby="account-help-success-title">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#e9f7f1] text-xl font-bold text-[#0c7774]" aria-hidden="true">✓</span>
                <h2 id="account-help-success-title" class="mt-4 text-xl font-extrabold">Permintaan bantuan dicatat</h2>
                <p class="mt-2 text-sm leading-6 text-[#667b79]">Admin sekolah akan membantu setelah memverifikasi identitasmu. Jika akun tidak ditemukan, hubungi admin sekolah secara langsung.</p>
                <button type="button" class="mt-5 min-h-11 w-full rounded-xl bg-[#0c7774] px-5 text-sm font-bold text-white hover:bg-[#075f5d]" @click="close">Kembali ke login</button>
            </section>
        </section>
    </dialog>
</template>
