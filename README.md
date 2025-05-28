# Hayes Auto Garage - Gestion de Comptabilité

![Hayes Auto Garage Logo](public/assets/images/logo.png)

## Présentation

**Hayes Auto Garage** est une application web de gestion de la comptabilité pour un garage automobile. Elle permet de gérer les ventes de véhicules, les contrats de partenariat (LSPD, EMS, etc.), les utilisateurs, les historiques d’actions, et d’obtenir des statistiques sur l’activité du garage.

## Fonctionnalités principales

- **Gestion des ventes** : Ajout, modification, suppression de ventes de véhicules.
- **Contrats partenaires** : Gestion des ventes sous contrat pour les partenaires (LSPD, EMS, etc.) avec tarifs spécifiques.
- **Historique & logs** : Suivi des actions importantes et export CSV.
- **Gestion des utilisateurs** : Création, modification, suppression d’utilisateurs avec rôles (patron, employé).
- **Notifications modernes** : Système de notifications toast pour les retours utilisateur.
- **Tableau de bord** : Statistiques, répartition des ventes, tarifs, historique rapide.
- **Sécurité** : Protection des accès, gestion des rôles, sécurisation des fichiers sensibles.

## Installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/votre-utilisateur/HayesAuto.git
   ```
2. **Configurer la base de données**

   - Importez le schéma SQL fourni (non inclus ici).
   - Renseignez vos identifiants dans `includes/db.php`.

3. **Configurer les droits d’accès**

   - Vérifiez les droits d’écriture sur le dossier `/logs`.

4. **Lancer le serveur**

   - Utilisez XAMPP, WAMP ou tout serveur Apache/PHP compatible.
   - Placez le dossier dans `htdocs` ou équivalent.

5. **Accéder à l’application**
   - Ouvrez [http://localhost/HayesAuto/public/index.php](http://localhost/HayesAuto/public/index.php) dans votre navigateur.

## Structure du projet

```
HayesAuto/
│
├── public/
│   ├── index.php
│   ├── dashboard.php
│   ├── add_vente.php
│   ├── add_vente_contrat.php
│   ├── edit_vente.php
│   ├── edit_vente_contrat.php
│   ├── delete_vente.php
│   ├── delete_vente_contrat.php
│   ├── ventes.php
│   ├── ventes_contrat.php
│   ├── manage_users.php
│   ├── partenariats.php
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── includes/
│   ├── db.php
│   ├── header.php
│   └── footer.php
│
├── config/
│   └── config.php
│
├── logs/
│   ├── log-general.csv
│   ├── lspd-log.csv
│   ├── ems-log.csv
│   └── other-log.txt
│
└── README.md
```

## Technologies utilisées

- PHP 8+
- MySQL/MariaDB
- HTML5 / CSS3 (Flexbox, responsive)
- JavaScript (Toastify.js, Select2)
- Apache (avec .htaccess)

## Auteurs

- Nerow75

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus d’informations.

Hayes Auto Garage – Gestion moderne et efficace de votre activité automobile 🚗🔧
