<?php

namespace App\Enum;

enum StatutReservation: string
{
    case EN_COURS = 'en_cours';
    case ANNULEE = 'annulee';
    case TERMINEE = 'terminee';
}