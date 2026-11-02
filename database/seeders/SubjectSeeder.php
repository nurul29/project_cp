<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Matematika',
                'slug' => 'matematika',
                'icon' => '∑',
                'description' => 'Aljabar, Geometri & Statistik',
                'tone' => 'green',
                'sort_order' => 1,
            ],
            [
                'name' => 'IPA',
                'slug' => 'ipa',
                'icon' => '🔬',
                'description' => 'Biologi, Fisika & Praktikum',
                'tone' => 'blue',
                'sort_order' => 2,
            ],
            [
                'name' => 'Bahasa Indonesia',
                'slug' => 'bahasa-indonesia',
                'icon' => '✎',
                'description' => 'Literasi & Teks Fiksi/Nonfiksi',
                'tone' => 'orange',
                'sort_order' => 3,
            ],
            [
                'name' => 'IPS',
                'slug' => 'ips',
                'icon' => '🌍',
                'description' => 'Sejarah, Geografi & Ekonomi',
                'tone' => 'pink',
                'sort_order' => 4,
            ],
            [
                'name' => 'Bahasa Inggris',
                'slug' => 'bahasa-inggris',
                'icon' => 'Aa',
                'description' => 'Speaking, Listening & Vocab',
                'tone' => 'lilac',
                'sort_order' => 5,
            ],
            [
                'name' => 'Pendidikan Pancasila',
                'slug' => 'pendidikan-pancasila',
                'icon' => '🇮🇩',
                'description' => 'Kewarganegaraan & Nilai Pancasila',
                'tone' => 'yellow',
                'sort_order' => 6,
            ],
            [
                'name' => 'Bahasa Sunda',
                'slug' => 'bahasa-sunda',
                'icon' => '🗣️',
                'description' => 'Basa, Sastra & Budaya Sunda',
                'tone' => 'green',
                'sort_order' => 7,
            ],
            [
                'name' => 'Seni Budaya',
                'slug' => 'seni-budaya',
                'icon' => '🎨',
                'description' => 'Seni Rupa, Musik & Tari',
                'tone' => 'pink',
                'sort_order' => 8,
            ],
            [
                'name' => 'PJOK',
                'slug' => 'pjok',
                'icon' => '⚽',
                'description' => 'Olahraga & Kesehatan',
                'tone' => 'blue',
                'sort_order' => 9,
            ],
            [
                'name' => 'Pendidikan Agama',
                'slug' => 'pendidikan-agama',
                'icon' => '📖',
                'description' => 'Budi Pekerti & Keagamaan',
                'tone' => 'yellow',
                'sort_order' => 10,
            ],
            [
                'name' => 'Informatika',
                'slug' => 'informatika',
                'icon' => '💻',
                'description' => 'Berpikir Komputasional & Coding',
                'tone' => 'lilac',
                'sort_order' => 11,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(['slug' => $subject['slug']], $subject);
        }
    }
}
