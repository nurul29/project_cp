<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { resetPasswordFormSchema, generateSchoolDefaultPassword } from '@/Utils/validation';

const props = defineProps({
    users: { type: Array, default: () => [] },
    classrooms: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ q: '', role: 'Semua', status: 'Semua' }) },
});

const search = ref(props.filters.q || '');
const roleFilter = ref(props.filters.role || 'Semua');
const statusFilter = ref(props.filters.status || 'Semua');

const applyFilter = () => {
    router.get(route('admin.users.index'), {
        q: search.value || undefined,
        role: roleFilter.value !== 'Semua' ? roleFilter.value : undefined,
        status: statusFilter.value !== 'Semua' ? statusFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
    search.value = '';
    roleFilter.value = 'Semua';
    statusFilter.value = 'Semua';
    router.get(route('admin.users.index'));
};

// Modal Form: Tambah / Edit Pengguna
const showUserModal = ref(false);
const editingUser = ref(null);
const clientErrors = ref({});

const userForm = useForm({
    name: '',
    username: '',
    email: '',
    role: 'student',
    password: '',
    password_confirmation: '',
    classroom_id: '',
    subject_id: '',
    is_active: true,
});

const openCreateModal = () => {
    editingUser.value = null;
    clientErrors.value = {};
    userForm.reset();
    userForm.is_active = true;
    showUserModal.value = true;
};

const openEditModal = (u) => {
    editingUser.value = u;
    clientErrors.value = {};
    userForm.name = u.name;
    userForm.username = u.username;
    userForm.email = u.email || '';
    userForm.role = u.role;
    userForm.password = '';
    userForm.password_confirmation = '';
    userForm.classroom_id = u.classroom_id || '';
    userForm.subject_id = u.teachings?.[0]?.subject_id || '';
    userForm.is_active = Boolean(u.is_active);
    showUserModal.value = true;
};

const applyDefaultPasswordForUser = () => {
    if (!userForm.username) {
        alert('Silakan isi NIS / Nama pengguna terlebih dahulu untuk menghasilkan kata sandi standar.');
        return;
    }
    const pwd = generateSchoolDefaultPassword(userForm.username);
    userForm.password = pwd;
    userForm.password_confirmation = pwd;
    clientErrors.value = {};
};

const submitUser = () => {
    clientErrors.value = {};

    if (!editingUser.value) {
        // Validasi kata sandi dengan Zod saat pembuatan user baru
        const parseResult = resetPasswordFormSchema.safeParse({
            password: userForm.password,
            password_confirmation: userForm.password_confirmation,
        });

        if (!parseResult.success) {
            const formatted = parseResult.error.format();
            if (formatted.password?._errors?.length) {
                clientErrors.value.password = formatted.password._errors[0];
            }
            if (formatted.password_confirmation?._errors?.length) {
                clientErrors.value.password_confirmation = formatted.password_confirmation._errors[0];
            }
            return;
        }

        userForm.post(route('admin.users.store'), {
            onSuccess: () => { showUserModal.value = false; userForm.reset(); },
        });
    } else {
        userForm.put(route('admin.users.update', editingUser.value.id), {
            onSuccess: () => { showUserModal.value = false; userForm.reset(); },
        });
    }
};

// Modal Ganti Password
const passwordUser = ref(null);
const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});
const passwordClientErrors = ref({});

const openPasswordModal = (u) => {
    passwordUser.value = u;
    passwordClientErrors.value = {};
    passwordForm.reset();
};

const applyDefaultPasswordForReset = () => {
    if (!passwordUser.value) return;
    const pwd = generateSchoolDefaultPassword(passwordUser.value.username);
    passwordForm.password = pwd;
    passwordForm.password_confirmation = pwd;
    passwordClientErrors.value = {};
};

const submitPassword = () => {
    if (!passwordUser.value) return;
    passwordClientErrors.value = {};

    const parseResult = resetPasswordFormSchema.safeParse({
        password: passwordForm.password,
        password_confirmation: passwordForm.password_confirmation,
    });

    if (!parseResult.success) {
        const formatted = parseResult.error.format();
        if (formatted.password?._errors?.length) {
            passwordClientErrors.value.password = formatted.password._errors[0];
        }
        if (formatted.password_confirmation?._errors?.length) {
            passwordClientErrors.value.password_confirmation = formatted.password_confirmation._errors[0];
        }
        return;
    }

    passwordForm.post(route('admin.users.reset-password', passwordUser.value.id), {
        onSuccess: () => { passwordUser.value = null; passwordForm.reset(); },
    });
};

const deleteUser = (u) => {
    if (confirm(`Yakin ingin menghapus pengguna "${u.name}"?`)) {
        showUserModal.value = false;
        router.delete(route('admin.users.destroy', u.id), { preserveScroll: true });
    }
};

const roleLabel = (r) => {
    const map = { student: 'Siswa', teacher: 'Guru', admin: 'Admin' };
    return map[r] || r;
};

const usernameLabel = computed(() => {
    if (userForm.role === 'student') return 'NIS (Nomor Induk Siswa) *';
    if (userForm.role === 'teacher') return 'NIP / Nama Pengguna *';
    return 'Nama Pengguna (Username) *';
});

const usernamePlaceholder = computed(() => {
    if (userForm.role === 'student') return 'Contoh: 202601001';
    if (userForm.role === 'teacher') return 'Contoh: 198203042006041002';
    return 'Contoh: admin.utama';
});

const usernameHelpText = computed(() => {
    if (userForm.role === 'student') return 'Nomor Induk Siswa unik yang digunakan murid saat login. Pastikan tidak ganda.';
    if (userForm.role === 'teacher') return 'NIP atau identitas unik guru yang digunakan saat login ke aplikasi.';
    return 'Username unik untuk login administrator sekolah.';
});
</script>

<template>
    <Head title="Pengguna Sekolah — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Pengelolaan sekolah</div>
                <h1>Pengguna sekolah</h1>
                <p>Kelola akun, peran, dan hak akses perpustakaan digital.</p>
            </div>
            <div class="page-actions">
                <button type="button" class="admin-btn solid" @click="openCreateModal">
                    + Tambah pengguna
                </button>
            </div>
        </div>

        <!-- Toolbar pencarian dan filter -->
        <form class="admin-toolbar" @submit.prevent="applyFilter">
            <div class="admin-search">
                <label for="admin-query">Cari pengguna</label>
                <input
                    id="admin-query"
                    v-model="search"
                    type="search"
                    placeholder="Nama, NIS, atau nama pengguna..."
                />
            </div>
            <div>
                <label for="admin-role-filter">Peran</label>
                <select id="admin-role-filter" v-model="roleFilter" @change="applyFilter">
                    <option value="Semua">Semua peran</option>
                    <option value="student">Siswa</option>
                    <option value="teacher">Guru</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <label for="admin-status-filter">Status</label>
                <select id="admin-status-filter" v-model="statusFilter" @change="applyFilter">
                    <option value="Semua">Semua status</option>
                    <option value="true">Aktif</option>
                    <option value="false">Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="primary" style="font-size: 12px; min-height: 40px;">
                Terapkan
            </button>
        </form>

        <div class="admin-section-caption">
            <span><b>{{ users.length }}</b> pengguna ditampilkan</span>
            <button type="button" class="admin-btn" @click="resetFilter">Reset filter</button>
        </div>

        <!-- Tabel Pengguna -->
        <div v-if="users.length" class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Peran</th>
                        <th>Kelas / Mata Pelajaran</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in users" :key="u.id">
                        <td>
                            <div class="user-cell">
                                <span class="user-monogram">
                                    {{ u.name.split(/\s+/).slice(0, 2).map(n => n[0]).join('').toUpperCase() }}
                                </span>
                                <div>
                                    <strong>{{ u.name }}</strong>
                                    <small>{{ u.username }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ roleLabel(u.role) }}</td>
                        <td>
                            {{ u.classroom?.name || '—' }}
                            <small v-if="u.teachings?.length" class="cell-meta">
                                {{ u.teachings.map(t => t.subject?.name).filter(Boolean).join(', ') }}
                            </small>
                        </td>
                        <td>
                            <span class="admin-badge" :class="u.is_active ? 'good' : 'neutral'">
                                {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button type="button" class="admin-btn" @click="openEditModal(u)">
                                    Edit
                                </button>
                                <button type="button" class="admin-btn" @click="openPasswordModal(u)">
                                    Ganti sandi
                                </button>
                                <button type="button" class="admin-btn danger" @click="deleteUser(u)">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="panel" style="text-align: center; padding: 40px 20px;">
            <p class="muted">Pengguna tidak ditemukan. Coba kata kunci lain atau reset filter.</p>
            <button type="button" class="admin-btn" @click="resetFilter">Reset filter</button>
        </div>

        <!-- Modal Tambah / Edit Pengguna -->
        <div
            v-if="showUserModal"
            class="modal-backdrop"
            @click.self="showUserModal = false"
        >
            <div class="reader-dialog" role="dialog" aria-modal="true">
                <div class="reader-top">
                    <div>
                        <h2>{{ editingUser ? 'Edit data pengguna' : 'Tambah pengguna baru' }}</h2>
                        <p class="dialog-lead" style="margin: 4px 0 0;">
                            {{ editingUser ? 'Perbarui identitas, peran, dan status akses pengguna.' : 'Lengkapi identitas, penempatan kelas, dan kata sandi awal pengguna.' }}
                        </p>
                    </div>
                    <button type="button" class="close-reader" aria-label="Tutup" @click="showUserModal = false">×</button>
                </div>

                <form @submit.prevent="submitUser" style="display: grid; gap: 14px;">
                    <div class="field" style="margin: 0;">
                        <label for="user-name">Nama lengkap *</label>
                        <input id="user-name" v-model="userForm.name" type="text" placeholder="Contoh: Nadia Aulia" required />
                        <small style="display: block; margin-top: 4px; font-size: 11px; color: #64748b;">
                            Nama asli pengguna sesuai identitas kependudukan atau buku induk sekolah.
                        </small>
                        <span v-if="userForm.errors.name" class="field-error">{{ userForm.errors.name }}</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px;">
                        <div class="field" style="margin: 0;">
                            <label for="user-role">Peran akun *</label>
                            <select id="user-role" v-model="userForm.role">
                                <option value="student">Siswa</option>
                                <option value="teacher">Guru</option>
                                <option value="admin">Admin</option>
                            </select>
                            <small style="display: block; margin-top: 4px; font-size: 11px; color: #64748b;">
                                Menentukan hak akses modul dan hak kelola di aplikasi.
                            </small>
                            <span v-if="userForm.errors.role" class="field-error">{{ userForm.errors.role }}</span>
                        </div>

                        <div class="field" style="margin: 0;">
                            <label for="user-username">{{ usernameLabel }}</label>
                            <input id="user-username" v-model="userForm.username" type="text" :placeholder="usernamePlaceholder" required />
                            <small style="display: block; margin-top: 4px; font-size: 11px; color: #64748b;">
                                {{ usernameHelpText }}
                            </small>
                            <span v-if="userForm.errors.username" class="field-error">{{ userForm.errors.username }}</span>
                        </div>
                    </div>

                    <div class="field" style="margin: 0;">
                        <label for="user-email">Email (Opsional)</label>
                        <input id="user-email" v-model="userForm.email" type="email" placeholder="Opsional (contoh: nadia@sekolah.sch.id)" />
                        <small style="display: block; margin-top: 4px; font-size: 11px; color: #64748b;">
                            Boleh dikosongkan jika belum ada. Pengguna dapat melengkapi atau memperbarui email mandiri lewat menu Profil.
                        </small>
                        <span v-if="userForm.errors.email" class="field-error">{{ userForm.errors.email }}</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px;">
                        <div v-if="userForm.role === 'student'" class="field" style="margin: 0;">
                            <label for="user-classroom">Penempatan Kelas</label>
                            <select id="user-classroom" v-model="userForm.classroom_id">
                                <option value="">Pilih kelas...</option>
                                <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <small style="display: block; margin-top: 4px; font-size: 11px; color: #64748b;">
                                Menentukan buku ajar dan kuis yang tampil untuk siswa.
                            </small>
                            <span v-if="userForm.errors.classroom_id" class="field-error">{{ userForm.errors.classroom_id }}</span>
                        </div>
                        <div v-if="userForm.role === 'teacher'" class="field" style="margin: 0;">
                            <label for="user-subject">Mata Pelajaran yang Diampu *</label>
                            <select id="user-subject" v-model="userForm.subject_id" required>
                                <option value="">Pilih mata pelajaran...</option>
                                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <small style="display: block; margin-top: 4px; font-size: 11px; color: #64748b;">
                                Guru akan mengelola materi dan kuis sesuai mata pelajaran ini.
                            </small>
                            <span v-if="userForm.errors.subject_id" class="field-error">{{ userForm.errors.subject_id }}</span>
                        </div>
                    </div>

                    <!-- Password awal & konfirmasi (hanya saat tambah user) -->
                    <div v-if="!editingUser" style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 12px; display: grid; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <span style="font-size: 12px; font-weight: 700; color: #1e293b;">Pengaturan Kata Sandi Akun</span>
                            <button
                                type="button"
                                class="admin-btn"
                                style="font-size: 11px; padding: 4px 8px; background: #e0f2fe; color: #0369a1; border-color: #bae6fd;"
                                @click="applyDefaultPasswordForUser"
                            >
                                ⚡ Terapkan Sandi Standar (7 Digit NIS/NIP + '@')
                            </button>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <div class="field" style="margin: 0;">
                                <label for="user-password">Kata sandi awal *</label>
                                <input
                                    id="user-password"
                                    v-model="userForm.password"
                                    type="password"
                                    placeholder="Minimal 8 karakter + simbol"
                                    required
                                />
                                <span v-if="clientErrors.password" class="field-error">{{ clientErrors.password }}</span>
                                <span v-else-if="userForm.errors.password" class="field-error">{{ userForm.errors.password }}</span>
                            </div>
                            <div class="field" style="margin: 0;">
                                <label for="user-password-conf">Konfirmasi kata sandi *</label>
                                <input
                                    id="user-password-conf"
                                    v-model="userForm.password_confirmation"
                                    type="password"
                                    placeholder="Ulangi kata sandi"
                                    required
                                />
                                <span v-if="clientErrors.password_confirmation" class="field-error">{{ clientErrors.password_confirmation }}</span>
                            </div>
                        </div>
                        <small style="font-size: 11px; color: #64748b; margin: 0;">
                            Wajib minimal 8 karakter dan memiliki minimal 1 simbol (@, #, $, dll). Gunakan tombol pintasan di atas untuk membuat kata sandi standar sekolah secara instan.
                        </small>
                    </div>

                    <label for="user-status-active" class="admin-check" style="display: flex; align-items: center; gap: 10px; margin: 4px 0 0; padding: 11px 14px; border: 1px solid #e1e9e4; border-radius: 11px; background: #f8faf7; cursor: pointer;">
                        <input id="user-status-active" v-model="userForm.is_active" type="checkbox" style="width: 16px; height: 16px; accent-color: #16766b; margin: 0;" />
                        <div>
                            <span style="font-size: 12.5px; font-weight: 650; color: #183d3b; display: block;">Akun aktif (dapat masuk ke sistem)</span>
                            <small style="font-size: 11px; color: #64748b;">Jika dinonaktifkan, akun diblokir sementara dari akses login ke ruang belajar.</small>
                        </div>
                    </label>

                    <div class="admin-dialog-actions" style="display: flex; justify-content: space-between; align-items: center;">
                        <button v-if="editingUser" type="button" class="admin-btn danger" style="padding: 9px 15px;" @click="deleteUser(editingUser)">
                            Hapus Akun
                        </button>
                        <div v-else></div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="secondary" @click="showUserModal = false">Batal</button>
                            <button type="submit" class="primary" :disabled="userForm.processing">
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Ganti Kata Sandi -->
        <div
            v-if="passwordUser"
            class="modal-backdrop"
            @click.self="passwordUser = null"
        >
            <div class="reader-dialog" role="dialog" aria-modal="true" style="max-width: 480px;">
                <div class="reader-top">
                    <div>
                        <h2>Ganti kata sandi</h2>
                        <p class="dialog-lead" style="margin: 4px 0 0;">
                            Perbarui kata sandi untuk akun <b>{{ passwordUser.name }}</b> ({{ passwordUser.username }}).
                        </p>
                    </div>
                    <button type="button" class="close-reader" aria-label="Tutup" @click="passwordUser = null">×</button>
                </div>

                <form @submit.prevent="submitPassword" style="display: grid; gap: 14px;">
                    <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 12px; display: grid; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px;">
                            <span style="font-size: 12px; font-weight: 600; color: #334155;">Formula Sandi Sekolah</span>
                            <button
                                type="button"
                                class="admin-btn"
                                style="font-size: 11px; padding: 4px 8px; background: #e0f2fe; color: #0369a1; border-color: #bae6fd;"
                                @click="applyDefaultPasswordForReset"
                            >
                                ⚡ Terapkan Sandi Standar (7 Digit + '@')
                            </button>
                        </div>
                        <small style="font-size: 11px; color: #64748b; margin: 0;">
                            Formula standar: 7 digit terakhir NIS/NIP/Username ditambah '@' (contoh: <b>{{ passwordUser.username }}</b> ➔ <code>{{ generateSchoolDefaultPassword(passwordUser.username) }}</code>).
                        </small>
                    </div>

                    <div class="field" style="margin: 0;">
                        <label for="reset-user-password">Kata sandi baru *</label>
                        <input
                            id="reset-user-password"
                            v-model="passwordForm.password"
                            type="password"
                            placeholder="Minimal 8 karakter + simbol"
                            required
                        />
                        <span v-if="passwordClientErrors.password" class="field-error">{{ passwordClientErrors.password }}</span>
                        <span v-else-if="passwordForm.errors.password" class="field-error">{{ passwordForm.errors.password }}</span>
                    </div>

                    <div class="field" style="margin: 0;">
                        <label for="reset-user-password-conf">Konfirmasi kata sandi baru *</label>
                        <input
                            id="reset-user-password-conf"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            placeholder="Ulangi kata sandi baru"
                            required
                        />
                        <span v-if="passwordClientErrors.password_confirmation" class="field-error">{{ passwordClientErrors.password_confirmation }}</span>
                    </div>

                    <small style="font-size: 11px; color: #64748b; margin: 0;">
                        Kata sandi wajib memenuhi standar keamanan minimal 8 karakter dan mengandung karakter simbol khusus.
                    </small>

                    <div class="admin-dialog-actions">
                        <button type="button" class="secondary" @click="passwordUser = null">Batal</button>
                        <button type="submit" class="primary" :disabled="passwordForm.processing">Simpan Sandi Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
