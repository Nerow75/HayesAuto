<?php

namespace App\Core;

class Logger
{
    /**
     * Log une action dans un fichier CSV.
     * @param string $type Type de log : 'coffre', 'vente', 'contrat', etc.
     * @param array $data Les données à logger (ligne CSV)
     * @param string|null $contrat Nom du contrat si besoin (pour fichier dédié)
     */
    public static function logCsv($type, array $data, $contrat = null)
    {
        $logDir = dirname(__DIR__, 2) . '/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Détermine le nom du fichier
        if ($type === 'contrat' && $contrat) {
            $filename = $logDir . strtolower($contrat) . '_log.csv';
        } elseif ($type === 'vente') {
            // Ajoute mois et année au nom du fichier
            $filename = $logDir . 'ventes_log-' . date('m-Y') . '.csv';
        } elseif ($type === 'coffre') {
            $filename = $logDir . 'coffre_log.csv';
        } else {
            $filename = $logDir . 'general_log.csv';
        }

        $isNewFile = !file_exists($filename);

        $fp = fopen($filename, 'a');
        if ($fp) {
            // Ajoute l'en-tête si le fichier est nouveau
            if ($isNewFile) {
                fputcsv($fp, array_keys($data), ';');
            }
            fputcsv($fp, array_values($data), ';');
            fclose($fp);
        }
    }
}
