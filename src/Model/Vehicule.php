<?php

namespace App\Model;

use App\Core\Database;
use PDO;

class Vehicule
{
    public static function getVehiculesFromCSV(string $csvPath): array
    {
        $vehicules = [];

        if (!file_exists($csvPath)) {
            return $vehicules;
        }

        if (($handle = fopen($csvPath, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ","); // saute l'entête
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $vehicules[] = [
                    'brand' => $row[0],
                    'model' => $row[1],
                    'category' => $row[2],
                    'price' => isset($row[3]) ? floatval($row[3]) : 0,
                    'price_sell' => isset($row[4]) ? floatval($row[4]) : 0
                ];
            }
            fclose($handle);
        }

        return $vehicules;
    }

    public static function getPrixVehicule(string $nom, array $vehicules): float
    {
        $nom = trim(mb_strtolower($nom));
        foreach ($vehicules as $vehicule) {
            if (trim(mb_strtolower($vehicule['model'])) === $nom) {
                return $vehicule['price_sell'];
            }
        }
        return 0;
    }
}
