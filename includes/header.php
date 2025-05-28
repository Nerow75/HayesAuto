<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hayes Auto</title>
    <link rel="stylesheet" href="../public/assets/css/styles.css">
    <!-- Inclure la police Inter depuis Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Inclure jQuery depuis un CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Inclure Select2 depuis un CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>

<body>
    <header>
        <img src="../public/assets/images/logo.png" alt="Hayes Auto Logo" class="logo">
        <h1>Hayes Auto - Comptabilité</h1>
        <?php if (isset($_SESSION['user'])): ?>
            <p>Bienvenue, <?= htmlspecialchars($_SESSION['user']['nom']) ?> | <a href="index.php?logout=1">Déconnexion</a></p>
        <?php endif; ?>
    </header>

    <script>
        <?php if (!empty($_SESSION['toast_success'])): ?>
            Toastify({
                text: "✅ <?= addslashes($_SESSION['toast_success']) ?>",
                duration: 3500,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(90deg, #43e97b 0%, #38f9d7 100%)",
                    color: "#fff",
                    fontWeight: "bold",
                    boxShadow: "0 4px 16px rgba(67, 233, 123, 0.15)",
                    borderRadius: "8px",
                    fontSize: "1.1em",
                    padding: "14px 24px"
                },
                stopOnFocus: true,
                close: true
            }).showToast();
            <?php unset($_SESSION['toast_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['toast_error'])): ?>
            Toastify({
                text: "❌ <?= addslashes($_SESSION['toast_error']) ?>",
                duration: 3500,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(90deg, #ff5858 0%, #f09819 100%)",
                    color: "#fff",
                    fontWeight: "bold",
                    boxShadow: "0 4px 16px rgba(255, 88, 88, 0.15)",
                    borderRadius: "8px",
                    fontSize: "1.1em",
                    padding: "14px 24px"
                },
                stopOnFocus: true,
                close: true
            }).showToast();
            <?php unset($_SESSION['toast_error']); ?>
        <?php endif; ?>
    </script>

    <div class="main-container">