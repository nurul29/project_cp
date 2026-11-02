<script setup>
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import HelpDialog from '@/Components/HelpDialog.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: { type: String, default: null },
});

const showPassword = ref(false);
const capsLockActive = ref(false);
const helpDialog = ref(null);
const errorMessage = ref('');
const loginSuccess = ref(false);

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const updateCapsLockState = (event) => {
    capsLockActive.value = event.getModifierState('CapsLock');
};

const openHelpDialog = () => helpDialog.value?.open(form.username);

const submit = () => {
    errorMessage.value = '';
    form.post(route('login'), {
        onSuccess: () => {
            loginSuccess.value = true;
            errorMessage.value = '';
        },
        onError: (errors) => {
            const err = errors.username || errors.password || 'Gagal masuk. Periksa kembali nama pengguna dan kata sandi Anda.';
            errorMessage.value = err;
        },
        onFinish: () => form.reset('password'),
    });
};

const closeError = () => {
    errorMessage.value = '';
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk — SUNDIGITAL" />

        <main class="auth-layout">
            <section class="auth-intro" aria-labelledby="welcome-title">
                <p class="auth-overline">PERPUSTAKAAN DIGITAL · SMP NEGERI 16 DEPOK</p>
                <h1 id="welcome-title">Buka buku.<br />Temukan ide.<br /><span>Tumbuh bersama.</span></h1>
                <p class="auth-description">
                    Perpustakaan digital sekolah untuk menemukan, membaca, dan menyimpan koleksi pilihan dari berbagai mata pelajaran.
                </p>
                <div class="auth-tags" aria-label="Fitur SUNDIGITAL">
                    <span>▤ Perpustakaan</span>
                    <span>♡ Rak bacaan</span>
                    <span>▦ Koleksi lintas pelajaran</span>
                </div>
                <div class="auth-art" aria-label="Pengetahuan baru dimulai dari rasa ingin tahu">
                    <div class="auth-art-caption">BACA · PAHAMI · TERAPKAN</div>
                    <p>Pengetahuan baru dimulai dari rasa ingin tahu.</p>
                    <div class="auth-books" aria-hidden="true">
                        <span>IPA</span>
                        <span>MATEMATIKA</span>
                        <span>BAHASA<br />INDONESIA</span>
                    </div>
                </div>
            </section>

            <section class="auth-card" aria-labelledby="login-title">
                <p class="auth-card-overline">SELAMAT DATANG KEMBALI</p>
                <h2 id="login-title">Masuk ke SUNDIGITAL</h2>
                <p class="auth-card-description">Gunakan akun yang diberikan oleh sekolah.</p>

                <!-- Pop up / Notifikasi Gagal Masuk -->
                <div
                    v-if="errorMessage || form.errors.username"
                    class="auth-alert auth-alert-error"
                    role="alert"
                >
                    <div class="auth-alert-icon" aria-hidden="true">⚠️</div>
                    <div class="auth-alert-content">
                        <strong>Gagal Masuk</strong>
                        <p>{{ errorMessage || form.errors.username }}</p>
                    </div>
                    <button
                        type="button"
                        class="auth-alert-close"
                        aria-label="Tutup pesan kesalahan"
                        @click="closeError"
                    >
                        ×
                    </button>
                </div>

                <!-- Pop up / Notifikasi Berhasil Masuk -->
                <div
                    v-if="loginSuccess"
                    class="auth-alert auth-alert-success"
                    role="alert"
                >
                    <div class="auth-alert-icon" aria-hidden="true">✅</div>
                    <div class="auth-alert-content">
                        <strong>Berhasil Masuk!</strong>
                        <p>Selamat datang kembali! Sedang mengalihkan ke ruang belajar Anda...</p>
                    </div>
                </div>

                <div v-if="status" class="auth-status" role="status">{{ status }}</div>

                <form class="auth-form" @submit.prevent="submit">
                    <div class="auth-field">
                        <label for="username">NIS (Siswa) / Nama Pengguna (Guru & Admin)</label>
                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            placeholder="Masukkan NIS untuk siswa, atau nama pengguna untuk guru"
                            required
                            autofocus
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            :aria-invalid="Boolean(form.errors.username)"
                            aria-describedby="username-error username-help"
                        />
                        <small id="username-help" style="display: block; font-size: 11.5px; color: #5c7270; margin-top: 4px;">
                            Siswa masuk menggunakan Nomor Induk Siswa (NIS). Guru & Admin menggunakan NIP / Nama Pengguna.
                        </small>
                        <InputError id="username-error" :message="form.errors.username" />
                    </div>

                    <div class="auth-field">
                        <div class="auth-label-row">
                            <label for="password">Kata sandi</label>
                            <button type="button" class="text-left font-bold text-[#0c7774] hover:underline" @click="openHelpDialog">Lupa kata sandi?</button>
                        </div>
                        <div class="auth-password">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Masukkan kata sandi"
                                required
                                autocomplete="current-password"
                                @keydown="updateCapsLockState"
                                @keyup="updateCapsLockState"
                                @blur="capsLockActive = false"
                                :aria-invalid="Boolean(form.errors.password)"
                                aria-describedby="password-error caps-lock-message"
                            />
                            <button
                                type="button"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                :aria-pressed="showPassword"
                                @click="showPassword = !showPassword"
                            >
                                {{ showPassword ? 'Sembunyikan' : 'Lihat' }}
                            </button>
                        </div>
                        <p v-if="capsLockActive" id="caps-lock-message" class="auth-caps-lock" role="status">
                            Caps Lock aktif. Periksa huruf besar dan kecil.
                        </p>
                        <InputError id="password-error" :message="form.errors.password" />
                    </div>

                    <label class="auth-remember">
                        <Checkbox id="remember" name="remember" v-model:checked="form.remember" />
                        <span>Ingat perangkat ini</span>
                    </label>

                    <button class="auth-submit" type="submit" :disabled="form.processing">
                        <span v-if="form.processing" class="auth-spinner" aria-hidden="true"></span>
                        {{ form.processing ? 'Memproses masuk…' : 'Masuk ke ruang belajar →' }}
                    </button>
                </form>

                <div class="auth-security">
                    <span aria-hidden="true">▣</span>
                    <p>Siswa, guru, dan admin masuk melalui formulir yang sama. Setelah masuk, kamu diarahkan ke ruang belajar sesuai peran akun.</p>
                </div>
                <p class="auth-help">Mengalami kendala masuk? <button type="button" class="font-bold text-[#0c7774] hover:underline" @click="openHelpDialog">Hubungi admin sekolah.</button></p>
            </section>
        </main>
        <HelpDialog ref="helpDialog" />
    </GuestLayout>
</template>

<style scoped>
.auth-alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 13px 15px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 13px;
    line-height: 1.45;
    animation: fadeInSlide 0.25s ease-out;
}

.auth-alert-error {
    background: #fdf2f2;
    border: 1px solid #f8b4b4;
    color: #9b1c1c;
}

.auth-alert-success {
    background: #edfdf5;
    border: 1px solid #84e1bc;
    color: #03543f;
}

.auth-alert-icon {
    font-size: 18px;
    line-height: 1;
    flex-shrink: 0;
    margin-top: 1px;
}

.auth-alert-content {
    flex: 1;
    min-width: 0;
}

.auth-alert-content strong {
    display: block;
    font-weight: 700;
    margin-bottom: 2px;
    font-size: 13.5px;
}

.auth-alert-content p {
    margin: 0;
    font-size: 12.5px;
    opacity: 0.95;
}

.auth-alert-close {
    background: transparent;
    border: none;
    color: currentColor;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    padding: 0 4px;
    opacity: 0.7;
    transition: opacity 0.15s ease;
}

.auth-alert-close:hover {
    opacity: 1;
}

@keyframes fadeInSlide {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
