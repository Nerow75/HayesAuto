<?php

return [
    // Tarifs des éléments de révision
    'revision_prices' => [
        "Huile Moteur" => 150,
        "Filtre à air" => 200,
        "Bougies d'allumage" => 300,
        "Pneu" => 500,
        "Embrayage" => 400,
        "Plaquettes de frein" => 450,
        "Suspensions" => 400
    ],

    // Prix des contrats
    'contract_prices' => [
        "LSPD" => [
            "garage"   => 250,
            "terrain"  => 350,
            "critique" => 400
        ],
        "EMS" => [
            "garage"   => 250,
            "terrain"  => 350,
            "critique" => 400
        ]
    ],

    // Fréquence radio
    'radio_frequency' => "57 Mhz",

    'partenariats' => ['LSPD', 'EMS'],
];
