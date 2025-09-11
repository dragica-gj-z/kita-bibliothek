<?php
// config/books.php

use App\Enums\BookCondition;
use App\Enums\BookStatus;

return [
    /*
    |--------------------------------------------------------------------------
    | Zustand (BookCondition Enum)
    |--------------------------------------------------------------------------
    | Enum-Werte -> Labels für die Ausgabe im Frontend.
    */
    'conditions' => [
        BookCondition::NEW->value           => 'Neu',
        BookCondition::USED->value          => 'Gebraucht',
        BookCondition::DAMAGED->value       => 'Beschädigt',
        BookCondition::MISSING_PAGES->value => 'Seiten fehlen',
        BookCondition::REPAIRED->value      => 'Repariert',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status (BookStatus Enum)
    |--------------------------------------------------------------------------
    */
    'statuses' => [
        BookStatus::AVAILABLE->value => 'Verfügbar',
        BookStatus::BORROWED->value  => 'Ausgeliehen',
        BookStatus::RESERVED->value  => 'Reserviert',
        BookStatus::LOST->value      => 'Verloren',
    ],

];
