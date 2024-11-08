<?php

namespace App\Enum;

enum ReservationStatus: string
{
    case DISPONIBLE = 'Disponible';
    case RESERVE = 'Réservé';
    case CONFIRME = 'Réservé (Confirmé)';
    case ANNULE = 'Annulé';
}