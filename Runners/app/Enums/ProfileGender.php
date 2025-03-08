<?php

namespace App\Enums;

enum ProfileGender: string
{
    case MALE = 'M';
    case FEMALE = 'F';
    case NONE_BINARY = 'N';
    case OTHER = 'O';
    case PREFER_NOT_TO_SAY = 'P';
}
