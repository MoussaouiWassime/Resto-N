<?php

namespace App\Enum;

enum ReservationStatus: string
{
    case PENDING = 'E';   // En attente
    case CONFIRMED = 'C'; // Confirmé
    case CANCELED = 'A';  // Annulé
    case TAKEN = 'T';     // Terminé/Pris (vu dans le contrôleur précédent)
}
