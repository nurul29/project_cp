<?php

namespace Database\Seeders;

use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Enums\VideoProvider;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $teacherArif = User::where('username', 'pak.arif')->first();
        $teacherRina = User::where('username', 'bu.rina')->first();
        $classA = Classroom::where('name', 'VIII A')->first();

        $matematika = Subject::where('slug', 'matematika')->first();
        $ipa = Subject::where('slug', 'ipa')->first();
        $bindo = Subject::where('slug', 'bahasa-indonesia')->first();

        if ($matematika && $teacherArif) {
            Material::create([
                'subject_id' => $matematika->id,
                'classroom_id' => $classA?->id,
                'title' => 'Teorema Pythagoras & Segitiga Siku-siku',
                'type' => MaterialType::Module,
                'url' => 'https://example.com/modul-pythagoras.pdf',
                'status' => PublicationStatus::Published,
                'cover_text' => 'Modul Utama',
                'tone' => 'green',
                'description' => 'Pelajari konsep kuadrat sisi miring, pembuktian visual, dan selesaikan tantangan kuis interaktif hari ini.',
                'created_by' => $teacherArif->id,
            ]);

            Material::create([
                'subject_id' => $matematika->id,
                'classroom_id' => $classA?->id,
                'title' => 'Video Animasi Pembuktian Teorema Pythagoras',
                'type' => MaterialType::Video,
                'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'video_provider' => VideoProvider::YouTube,
                'status' => PublicationStatus::Published,
                'cover_text' => 'Video HD',
                'tone' => 'green',
                'description' => 'Visualisasi luas persegi pada setiap sisi segitiga siku-siku.',
                'created_by' => $teacherArif->id,
            ]);
        }

        if ($ipa && $teacherRina) {
            Material::create([
                'subject_id' => $ipa->id,
                'classroom_id' => $classA?->id,
                'title' => 'Buku Digital IPA: Sistem Organ Manusia',
                'type' => MaterialType::Book,
                'url' => 'https://example.com/buku-ipa.pdf',
                'status' => PublicationStatus::Published,
                'cover_text' => 'Buku Digital',
                'tone' => 'blue',
                'description' => 'Modul lengkap sistem pencernaan, pernapasan, dan peredaran darah.',
                'created_by' => $teacherRina->id,
            ]);
        }

        if ($bindo && $teacherRina) {
            Material::create([
                'subject_id' => $bindo->id,
                'classroom_id' => $classA?->id,
                'title' => 'Video Bedah Teks Eksplanasi Fenomena Alam',
                'type' => MaterialType::Video,
                'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'video_provider' => VideoProvider::YouTube,
                'status' => PublicationStatus::Published,
                'cover_text' => '12 Menit',
                'tone' => 'orange',
                'description' => 'Analisis struktur dan kaidah kebahasaan teks eksplanasi.',
                'created_by' => $teacherRina->id,
            ]);
        }
    }
}

