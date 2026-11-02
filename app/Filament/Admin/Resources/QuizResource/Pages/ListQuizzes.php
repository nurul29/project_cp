<?php

namespace App\Filament\Admin\Resources\QuizResource\Pages;

use App\Filament\Admin\Resources\QuizResource;
use App\Filament\Teacher\Resources\QuizResource\Pages\ListQuizzes as TeacherListQuizzes;

class ListQuizzes extends TeacherListQuizzes
{
    protected static string $resource = QuizResource::class;
}
