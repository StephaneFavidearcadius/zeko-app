<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <?php if (isset($additionalCSS)): ?>
        <?php echo $additionalCSS; ?>
    <?php endif; ?>
</head>
<body>

<!-- MODALE DE CONFIRMATION -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Confirmer</h3>
            <button type="button" class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-body">
            <p id="modalMessage">Êtes-vous sûr de vouloir effectuer cette action ?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="modalCancel">Annuler</button>
            <button type="button" class="btn btn-danger" id="modalConfirm">Confirmer</button>
        </div>
    </div>
</div>