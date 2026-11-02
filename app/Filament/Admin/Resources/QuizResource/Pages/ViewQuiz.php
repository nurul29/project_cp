<?php

namespace App\Filament\Admin\Resources\QuizResource\Pages;

use App\Filament\Admin\Resources\QuizResource;
use App\Filament\Teacher\Resources\QuizResource\Pages\ViewQuiz as TeacherViewQuiz;

class ViewQuiz extends TeacherViewQuiz
{
    protected static string $resource = QuizResource::class;
}
