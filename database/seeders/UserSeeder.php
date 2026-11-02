<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teaching;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $classA = Classroom::where('name', 'VIII A')->first();
        $classB = Classroom::where('name', 'VIII B')->first();

        // 1. Students
        $students = [
            [
                'name' => 'Nadia Aulia',
                'username' => 'siswa.081',
                'email' => 'nadia@sundigital.sch.id',
                'password' => Hash::make('password'),
                'role' => UserRole::Student,
                'classroom_id' => $classA?->id,
                'is_active' => true,
            ],
            [
                'name' => 'Rafi Fadilah',
                'username' => 'siswa.082',
                'email' => 'rafi@sundigital.sch.id',
                'password' => Hash::make('password'),
                'role' => UserRole::Student,
                'classroom_id' => $classA?->id,
                'is_active' => true,
            ],
            [
                'name' => 'Salsa Putri',
                'username' => 'siswa.083',
                'email' => 'salsa@sundigital.sch.id',
                'password' => Hash::make('password'),
                'role' => UserRole::Student,
                'classroom_id' => $classA?->id,
                'is_active' => true,
            ],
        ];

        foreach ($students as $studentData) {
            User::updateOrCreate(['username' => $studentData['username']], $studentData);
        }

        // 2. Teachers
        $teacherArif = User::updateOrCreate(
            ['username' => 'pak.arif'],
            [
                'name' => 'Pak Arif, S.Pd.',
                'username' => 'pak.arif',
                'email' => 'arif@sundigital.sch.id',
                'password' => Hash::make('password'),
                'role' => UserRole::Teacher,
                'is_active' => true,
            ]
        );

        $teacherRina = User::updateOrCreate(
            ['username' => 'bu.rina'],
            [
                'name' => 'Bu Rina, M.Pd.',
                'username' => 'bu.rina',
                'email' => 'rina@sundigital.sch.id',
                'password' => Hash::make('password'),
                'role' => UserRole::Teacher,
                'is_active' => true,
            ]
        );

        // 3. Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Sekolah',
                'username' => 'admin',
                'email' => 'admin@sundigital.sch.id',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );

        // 4. Teachings
        $matematika = Subject::where('slug', 'matematika')->first();
        $bindo = Subject::where('slug', 'bahasa-indonesia')->first();
        $ipa = Subject::where('slug', 'ipa')->first();
        $targetClasses = Classroom::whereIn('name', ['VII A', 'VII B', 'VIII A', 'VIII B'])->get();

        if ($teacherArif) {
            foreach (array_filter([$matematika, $bindo]) as $subj) {
                foreach ($targetClasses as $cls) {
                    Teaching::firstOrCreate([
                        'teacher_id' => $teacherArif->id,
                        'subject_id' => $subj->id,
                        'classroom_id' => $cls->id,
                    ]);
                }
            }
        }

        if ($teacherRina) {
            foreach (array_filter([$ipa, $bindo]) as $subj) {
                foreach ($targetClasses as $cls) {
                    Teaching::firstOrCreate([
                        'teacher_id' => $teacherRina->id,
                        'subject_id' => $subj->id,
                        'classroom_id' => $cls->id,
                    ]);
                }
            }
        }
    }
}
