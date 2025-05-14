<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hayes Auto</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Inclure Select2 depuis un CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
</head>

<body>
    <header>
        <h1>Hayes Auto - Comptabilité</h1>
        <?php if (isset($_SESSION['user'])): ?>
            <p>Bienvenue, <?= htmlspecialchars($_SESSION['user']['nom']) ?> | <a href="index.php?logout=1">Déconnexion</a></p>
        <?php endif; ?>
    </header>