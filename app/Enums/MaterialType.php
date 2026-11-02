<?php

namespace App\Enums;

enum MaterialType: string
{
    case Book = 'book';
    case Reading = 'reading';
    case Module = 'module';
    case Video = 'video';
    case Link = 'link';
}
