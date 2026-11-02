<?php

namespace App\Policies;

use App\Enums\PublicationStatus;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher() || $user->isStudent();
    }

    public function view(User $user, Material $material): bool
    {
        if ($user->isAdmin() || $user->isTeacher()) {
            return true;
        }

        if ($user->isStudent()) {
            $statusVal = $material->status instanceof PublicationStatus ? $material->status->value : (string) $material->status;
            if ($statusVal !== PublicationStatus::Published->value) {
                return false;
            }

            // Bacaan umum terbuka untuk seluruh siswa
            if ($material->collection_category === 'general' || $material->type?->value === 'reading') {
                return true;
            }

            // Materi pembelajaran untuk seluruh kelas
            if ($material->classroom_id === null && $material->target_grade === null && empty($material->target_classroom_ids)) {
                return true;
            }

            // Materi kelas spesifik
            if ($material->classroom_id && $material->classroom_id === $user->classroom_id) {
                return true;
            }

            // Materi jenjang tingkat kelas
            if ($material->target_grade && $user->classroom?->grade_level === $material->target_grade) {
                return true;
            }

            // Materi multi-kelas
            if (! empty($material->target_classroom_ids) && in_array($user->classroom_id, (array) $material->target_classroom_ids)) {
                return true;
            }

            return false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    public function update(User $user, Material $material): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            // Guru hanya boleh mengelola koleksi yang dibuatnya sendiri
            return $material->created_by === $user->id;
        }

        return false;
    }

    public function delete(User $user, Material $material): bool
    {
        return $this->update($user, $material);
    }
}
