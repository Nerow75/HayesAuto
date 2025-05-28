<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$config = require dirname(__DIR__) . '/config/config.php';
include '../includes/header.php';
?>
<h2 class="form-header">Fiches Partenariats</h2>
<ul class="partenariats-list">
    <?php foreach ($config['partenariats'] as $part): ?>
        <li><a href="ventes_contrat.php?partenariat=<?= urlencode($part) ?>"><?= htmlspecialchars($part) ?></a></li>
    <?php endforeach; ?>
</ul>
<?php include '../includes/footer.php'; ?>