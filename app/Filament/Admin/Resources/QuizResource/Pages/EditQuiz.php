<?php

namespace App\Filament\Admin\Resources\QuizResource\Pages;

use App\Filament\Admin\Resources\QuizResource;
use App\Filament\Teacher\Resources\QuizResource\Pages\EditQuiz as TeacherEditQuiz;

class EditQuiz extends TeacherEditQuiz
{
    protected static string $resource = QuizResource::class;
}
