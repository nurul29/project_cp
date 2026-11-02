import { z } from 'zod';

/**
 * Skema validasi kata sandi:
 * - Minimal 8 karakter
 * - Wajib mengandung minimal 1 karakter simbol (!@#$%^&*()_+-=[]{}|;:,.<>?)
 */
export const passwordSchema = z
    .string()
    .min(8, 'Kata sandi minimal 8 karakter')
    .regex(/[^A-Za-z0-9]/, 'Kata sandi wajib mengandung minimal 1 karakter simbol (contoh: @, #, $, !, %, dll)');

/**
 * Skema validasi form kata sandi dan konfirmasi
 */
export const resetPasswordFormSchema = z
    .object({
        password: passwordSchema,
        password_confirmation: z.string().min(1, 'Konfirmasi kata sandi wajib diisi'),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'Konfirmasi kata sandi tidak cocok dengan kata sandi baru',
        path: ['password_confirmation'],
    });

/**
 * Helper formula standar sekolah:
 * Mengambil 7 karakter terakhir dari NIS/NIP/Username + '@'
 */
export const generateSchoolDefaultPassword = (identifier) => {
    if (!identifier || typeof identifier !== 'string') {
        return 'sundigital123@';
    }

    const clean = identifier.trim();
    if (clean.length >= 7) {
        return clean.slice(-7) + '@';
    }

    // Jika panjang kurang dari 7, tambahkan padding angka 0 di depan agar tepat 7 + '@' = 8 karakter
    return clean.padStart(7, '0') + '@';
};
