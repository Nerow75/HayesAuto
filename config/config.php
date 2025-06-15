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

    'coffre_revision_map' => [
        'Kit de réparation'      => ['objet' => 'kit_de_reparation',      'quantite' => 1],
        'Huile de moteur'        => ['objet' => 'huile_de_moteur',        'quantite' => 1],
        'Pneu'                   => ['objet' => 'pneu',                   'quantite' => 4],
        'Embrayage'              => ['objet' => 'embrayage',              'quantite' => 1],
        'Filtre à air'           => ['objet' => 'filtre_a_air',           'quantite' => 1],
        'Bougie'                 => ['objet' => 'bougie',                 'quantite' => 4],
        'Plaquettes de frein'    => ['objet' => 'plaquettes_de_frein',    'quantite' => 4],
        'Suspensions'            => ['objet' => 'suspensions',            'quantite' => 1],
        'Moteur EX'              => ['objet' => 'moteur_ev',              'quantite' => 1],
        'Batterie EV'            => ['objet' => 'batterie_ev',            'quantite' => 1],
        'Liquide Refroidissement' => ['objet' => 'liquide_refroidissement', 'quantite' => 1],
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
