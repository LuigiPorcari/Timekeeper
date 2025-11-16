<?php
// config/races.php

return [

    /*
    |--------------------------------------------------------------------------
    | Tipi gara → apparecchiature di base
    |--------------------------------------------------------------------------
    | La UI mostrerà i label così come sono.
    | A salvataggio, il controller convertirà ogni label in uno "slug"
    | e lo "namespaccerà" con lo slug del tipo di gara (tipo__label),
    | così lo stesso nome su tipi diversi diventa un dato distinto.
    */

    'types' => [

        // NUOTO
        'NUOTO' => [
            'Piastre singole',
            'Piastre doppie',
            'Partenza singola',
            'Doppia partenza',
            'Tablet',
        ],

        // NUOTO - MANUALE
        'NUOTO - MANUALE' => [
            'Cronometro master',
            'Dorsale pulsanti',
            'Crono manuale Timy',
            'Tablet',
            'Cuffie-rolle cavi',
        ],

        // RALLY
        'RALLY START PS' => [
            'Semaforo partenza',
            'Kit cellule con S',
            'Crono manuale',
            'Tablet',
            'Cuffie-rolle cavi',
        ],
        'RALLY FINE PS' => [
            'Kit cellule con S',
            'Crono manuale',
            'PC + Tablet + Radio',
            'Transponder',
        ],

        // ENDURO
        'ENDURO START PS' => [
            'Kit cellule con S',
            'Crono manuale',
            'PC + Tablet + Radio',
            'Transponder',
        ],
        'ENDURO FINE PS' => [
            'Kit cellule con S',
            'Crono manuale',
            'PC + Tablet + Radio',
            'Tabelloni',
        ],

        // DOWHINILL / DOWNHILL
        'DOWHINILL' => [
            'Crono (master/REI)',
            'Crono manuale',
            'Orologio partenza',
            'Radio',
            'Tabelloni',
        ],

        // SCI
        'SCI ALPINO' => [
            'Crono (master/REI)',
            'Crono manuale',
            'Orologio partenza',
            'Radio',
            'Tabelloni',
        ],
        'SCI NORDICO (FONDO)' => [
            'Crono (master/REI)',
            'Cancellletto partenza',
            'Cuffie',
            'Orologio partenza',
            'Radio',
        ],

        // ATLETICA
        'ATLETICA - LYNX' => [
            'Lynx',
            'Cronometri (master/REI)',
            'Kit cellule',
            'Tabellone',
        ],
        'ATLETICA MANUALE' => [
            'Crono (master/REI)',
            'Radio',
            'Bandelle',
            'Tabellone',
        ],

        // CICLISMO
        'CICLISMO - LYNX' => [
            'Lynx',
            'Cronometri (master/REI)',
            'Bandelle',
            'Tabellone',
        ],
        'CICLISMO MANUALE' => [
            'Crono (master/REI)',
            'Radio',
            'Bandelle',
            'Tabellone',
        ],

        // ENDURO MTB
        'ENDURO MTB' => [
            'Semaforo partenza',
            'Kit cellule con S',
            'Crono manuale',
            'Tablet',
            'Cuffie-rolle cavi',
        ],

        // ALTRI (in base al foglio)
        'TROTTO' => [
            'Servizi standard senza apparecchiature specifiche',
            'Tabellone',
        ],
        'CONCORSO IPPICO' => [
            'Kit cellule con S',
            'Tabellone',
        ],
    ],

];
