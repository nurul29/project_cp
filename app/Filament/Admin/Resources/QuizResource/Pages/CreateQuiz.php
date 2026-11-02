<?php

namespace App\Filament\Admin\Resources\QuizResource\Pages;

use App\Filament\Admin\Resources\QuizResource;
use App\Filament\Teacher\Resources\QuizResource\Pages\CreateQuiz as TeacherCreateQuiz;

class CreateQuiz extends TeacherCreateQuiz
{
    protected static string $resource = QuizResource::class;
}
