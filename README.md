# Hayes Auto Garage – Application de gestion de comptabilité

![Hayes Auto Garage Logo](public/assets/images/logo.png)

## Présentation

**Hayes Auto Garage** est une application web complète pour la gestion de la comptabilité d’un garage automobile. Elle permet de gérer les ventes de véhicules, les contrats de partenariat (LSPD, EMS, etc.), le stock du coffre, les utilisateurs, et d’obtenir des statistiques détaillées sur l’activité du garage.  
L’application propose une interface moderne, sécurisée, responsive et adaptée à un usage quotidien.

---

## Fonctionnalités principales

- **Gestion des ventes** : Ajout, modification, suppression de ventes (classiques ou sous contrat).
- **Contrats partenaires** : Gestion des ventes pour les partenaires (LSPD, EMS, etc.) avec tarifs spécifiques et logs dédiés.
- **Gestion du coffre** : Suivi du stock de pièces et retrait automatique lors des ventes/révisions.
- **Historique & logs** : Export CSV, suivi des actions (ajout, modification, suppression) par utilisateur et par type de vente.
- **Gestion des utilisateurs** : Création, modification, suppression, gestion des rôles (patron, employé).
- **Tableau de bord** : Statistiques, répartition des ventes, historique rapide.
- **Notifications modernes** : Système Toastify pour les retours utilisateur.
- **Sécurité** : Authentification, gestion des rôles, CSRF, sécurisation des accès et des fichiers sensibles.

---

## Installation

1. **Cloner le dépôt**

   ```bash
   git clone https://github.com/votre-utilisateur/HayesAuto.git
   ```

2. **Configurer la base de données**

   - Importez le schéma SQL fourni (`hayesauto.sql`) dans votre MySQL/MariaDB.
   - Renseignez vos identifiants dans `config/config.php`.

3. **Vérifier les droits d’accès**

   - Assurez-vous que le dossier `/logs` est accessible en écriture par le serveur web.

4. **Lancer le serveur**

   - Utilisez XAMPP, WAMP ou tout serveur Apache/PHP compatible.
   - Placez le dossier dans `htdocs` ou équivalent.

5. **Accéder à l’application**
   - Rendez-vous sur [http://localhost/HayesAuto/public/](http://localhost/HayesAuto/public/) dans votre navigateur.

---

## Structure du projet

```
HayesAuto/
│
├── public/
│   ├── index.php
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── src/
│   ├── Controller/
│   ├── Core/
│   ├── Model/
│
├── templates/
│   ├── base.html.twig
│   ├── dashboard.html.twig
│   ├── add_edit_vente.html.twig
│   ├── ventes.html.twig
│   ├── partenariats.html.twig
│   ├── coffre.html.twig
│   ├── manage_users.html.twig
│   └── 404.html.twig
│
├── config/
│   └── config.php
│
├── logs/
│   ├── ventes_log-<mois>-<année>.csv
│   ├── <partenaire>_log.csv
│   └── coffre_log.csv
│
├── public/assets/data/
│   └── vehicules.csv
│
└── README.md
```

---

## Technologies utilisées

- **PHP 8+**
- **MySQL/MariaDB**
- **Twig** (templates)
- **HTML5 / CSS3** (Flexbox, responsive)
- **JavaScript** (Toastify.js, Select2)
- **Apache** (avec .htaccess)

---

## Auteurs

- Nerow75

---

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus d’informations.

---

**Hayes Auto Garage – Gestion moderne et efficace de votre activité automobile 🚗🔧**
