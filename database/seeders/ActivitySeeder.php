<?php

namespace Database\Seeders;

use App\Enums\AssignmentStatus;
use App\Enums\PublicationStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $classA = Classroom::where('name', 'VIII A')->first();
        $teacherArif = User::where('username', 'pak.arif')->first();
        $teacherRina = User::where('username', 'bu.rina')->first();

        $ipa = Subject::where('slug', 'ipa')->first();
        $matematika = Subject::where('slug', 'matematika')->first();

        // 1. IPA Assignment
        if ($ipa && $classA && $teacherRina) {
            Assignment::create([
                'subject_id' => $ipa->id,
                'classroom_id' => $classA->id,
                'title' => 'LKPD: Sistem Pencernaan Manusia',
                'instructions' => 'Jelaskan alur organ sistem pencernaan manusia dan fungsi masing-masing enzim pencernaan.',
                'due_at' => now()->addDays(2)->endOfDay(),
                'status' => AssignmentStatus::Active,
                'created_by' => $teacherRina->id,
            ]);
        }

        // 2. Matematika Quiz — linked to Pythagoras module material
        if ($matematika && $classA && $teacherArif) {
            $pythagorasMaterial = Material::where('title', 'Teorema Pythagoras & Segitiga Siku-siku')->first();

            $quiz = Quiz::create([
                'subject_id' => $matematika->id,
                'classroom_id' => $classA->id,
                'material_id' => $pythagorasMaterial?->id,
                'title' => 'Kuis Cepat: Teorema Pythagoras',
                'instructions' => 'Kerjakan soal-soal berikut berdasarkan materi Teorema Pythagoras. Pilih jawaban yang paling tepat.',
                'status' => PublicationStatus::Published,
                'created_by' => $teacherArif->id,
            ]);

            Question::create([
                'quiz_id' => $quiz->id,
                'body' => 'Jika sebuah segitiga siku-siku memiliki panjang sisi siku-siku 3 cm dan 4 cm, berapakah panjang sisi miringnya?',
                'options' => ['5 cm', '6 cm', '7 cm', '8 cm'],
                'correct_index' => 0,
                'explanation' => 'Menggunakan rumus Pythagoras: c² = a² + b² = 3² + 4² = 9 + 16 = 25, maka c = √25 = 5 cm.',
                'position' => 1,
            ]);

            Question::create([
                'quiz_id' => $quiz->id,
                'body' => 'Manakah di antara kelompok bilangan berikut yang merupakan Tripel Pythagoras?',
                'options' => ['3, 4, 6', '5, 12, 13', '6, 8, 11', '7, 9, 12'],
                'correct_index' => 1,
                'explanation' => 'Tripel Pythagoras memenuhi a² + b² = c². Periksa: 5² + 12² = 25 + 144 = 169 = 13². Benar!',
                'position' => 2,
            ]);

            Question::create([
                'quiz_id' => $quiz->id,
                'body' => 'Panjang hipotenusa suatu segitiga siku-siku adalah 10 cm. Jika salah satu sisi tegaknya 6 cm, berapakah panjang sisi tegak lainnya?',
                'options' => ['4 cm', '7 cm', '8 cm', '9 cm'],
                'correct_index' => 2,
                'explanation' => 'Gunakan rumus: b² = c² - a² = 10² - 6² = 100 - 36 = 64, maka b = √64 = 8 cm.',
                'position' => 3,
            ]);
        }

        // 3. IPA Quiz — standalone (no material link)
        if ($ipa && $classA && $teacherRina) {
            $standaloneQuiz = Quiz::create([
                'subject_id' => $ipa->id,
                'classroom_id' => $classA->id,
                'material_id' => null,
                'title' => 'Latihan Mandiri: Sistem Pencernaan',
                'instructions' => 'Kuis mandiri ini tidak terikat pada buku tertentu. Uji pemahamanmu tentang sistem pencernaan manusia.',
                'status' => PublicationStatus::Published,
                'created_by' => $teacherRina->id,
            ]);

            Question::create([
                'quiz_id' => $standaloneQuiz->id,
                'body' => 'Enzim ptialin yang terdapat dalam air liur berfungsi untuk mengubah...',
                'options' => ['Protein menjadi asam amino', 'Amilum menjadi maltosa', 'Lemak menjadi asam lemak', 'Vitamin menjadi mineral'],
                'correct_index' => 1,
                'explanation' => 'Enzim ptialin (amilase) dalam saliva mengubah amilum (zat tepung) menjadi maltosa melalui proses hidrolisis.',
                'position' => 1,
            ]);

            Question::create([
                'quiz_id' => $standaloneQuiz->id,
                'body' => 'Organ pencernaan yang berfungsi menyerap sari-sari makanan adalah...',
                'options' => ['Lambung', 'Usus besar', 'Usus halus', 'Kerongkongan'],
                'correct_index' => 2,
                'explanation' => 'Usus halus memiliki vili (jonjot usus) yang memperluas permukaan penyerapan nutrisi ke dalam pembuluh darah.',
                'position' => 2,
            ]);

            Question::create([
                'quiz_id' => $standaloneQuiz->id,
                'body' => 'Gerakan peristaltik pada kerongkongan berfungsi untuk...',
                'options' => ['Mengunyah makanan', 'Mendorong makanan menuju lambung', 'Menyerap air', 'Memecah protein'],
                'correct_index' => 1,
                'explanation' => 'Gerakan peristaltik adalah kontraksi otot-otot dinding kerongkongan secara bergelombang yang mendorong makanan ke lambung.',
                'position' => 3,
            ]);
        }
    }
}

