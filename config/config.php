<?php

return [
    // Configuration de la base de données
    'db_host' => 'localhost',
    'db_name' => 'hayesauto',
    'db_user' => 'root',
    'db_pass' => '',

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

    'partenariats' => ['LSPD', 'EMS'],
];
