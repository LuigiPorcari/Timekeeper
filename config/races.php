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

        'NUOTO' => [
            'Piastre singole',
            'Piastre doppie',
            'Partenza singola',
            'Doppia partenza',
        ],

        'NUOTO - MANUALE' => [
            'Cronometro master',
            'Dorsale pulsanti',
        ],

        'RALLY START PS' => [
            'Crono (master/REI)',
            'Semaforo partenza',
            'Kit cellule con Supporti',
            'Crono manuale Timy',
            'Tablet',
        ],

        'RALLY FINE PS' => [
            'Crono (master/REI)',
            'Kit cellule con Supporti',
            'Crono manuale',
            'Tablet',
            'Cuffie-rolle cavi',
        ],

        'ENDURO START PS' => [
            'Crono (master/REI)',
            'Kit cellule con Supporti',
            'Crono manuale',
            'PC + Tablet + Radio',
            'Transponder',
        ],

        'ENDURO FINE PS' => [
            'Crono (master/REI)',
            'Kit cellule con Supporti',
            'Crono manuale',
            'PC + Tablet + Radio',
            'Transponder',
        ],

        'DOWHINILL' => [
            'Crono (master/REI)',
            'Kit cellule con Supporti',
            'Crono manuale',
            'PC + Tablet + Radio',
            'Tabelloni',
            'Orologio partenza',
            'Radio',
        ],

        'SCI ALPINO' => [
            'Crono (master/REI)',
            'Kit cellule con Supporti',
            'Crono manuale',
            'Orologio partenza',
            'Tabelloni',
            'PC',
            'Cancelletto partenza',
            'Cuffie',
            'Radio',
        ],

        'SCI NORDICO (FONDO)' => [
            'Crono (master/REI)',
            'Kit cellule con Supporti',
            'Crono manuale',
            'Orologio partenza',
            'Radio',
            'PC',
            'Cancellletto partenza',
            'Cuffie',
        ],

        'ATLETICA - LYNX' => [
            'Lynx',
            'Radio',
            'Cronometri (master/REI)',
            'Kit cellule',
            'Tabellone',
        ],

        'ATLETICA MANUALE' => [
            'Crono (master/REI)',
            'Kit cellule con Supporti',
            'Tabellone',
        ],

        'CICLISMO - LYNX' => [
            'Lynx',
            'Radio',
            'Cronometri (master/REI)',
            'Bandelle',
            'Tabellone',
        ],

        'CICLISMO MANUALE' => [
            'Crono (master/REI)',
            'Radio',
            'Tabellone',
            'Bandelle',
        ],

        'ENDURO MTB' => [
            'Crono (master/REI)',
            'Semaforo partenza',
            'Kit cellule con Supporti',
            'Crono manuale Timy',
            'Tablet',
            'Crono manuale',
            'Cuffie-rolle cavi',
        ],

        'CONCORSO IPPICO' => [
            'Crono (master/REI)',
            'Kit cellule con Supporti',
            'Tabellone',
        ],

        'TROTTO' => [
            'Servizi standard senza apparecchiature specifiche',
        ],

        'HOCHEY' => [
            'Servizi standard senza apparecchiature specifiche',
        ],
    ],

];
