<?php
// Fichier : seller/products/edit.php
// Modification d'un produit

require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Modifier un produit - Zeko.app';

$userId = $_SESSION['user_id'];
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    setFlashMessage('error', 'Produit non trouvé.');
    redirect(APP_URL . 'seller/products/index.php');
}

$pdo = getPDO();

// Récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$productId, $userId]);
$product = $stmt->fetch();

if (!$product) {
    setFlashMessage('error', 'Produit non trouvé.');
    redirect(APP_URL . 'seller/products/index.php');
}

// Récupérer les catégories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$name = $product['name'];
$description = $product['description'];
$price = $product['price'];
$categoryId = $product['category_id'];
$status = $product['status'];
$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = str_replace(',', '.', $_POST['price'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'active');
    
    // === VALIDATION ===
    if (empty($name)) {
        $errors['name'] = 'Le nom du produit est requis.';
    } elseif (strlen($name) > 255) {
        $errors['name'] = 'Le nom ne doit pas dépasser 255 caractères.';
    }
    
    if (empty($description)) {
        $errors['description'] = 'La description est requise.';
    }
    
    if (empty($price)) {
        $errors['price'] = 'Le prix est requis.';
    } elseif (!is_numeric($price) || $price < 0) {
        $errors['price'] = 'Veuillez entrer un prix valide.';
    }
    
    if ($categoryId <= 0) {
        $errors['category_id'] = 'Veuillez sélectionner une catégorie.';
    }
    
    // Traiter le nouveau fichier (optionnel)
    $newFile = false;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileOriginal = $file['name'];
        
        if ($fileSize > MAX_FILE_SIZE) {
            $errors['file'] = 'Le fichier est trop volumineux. Taille max : 50 MB.';
        }
        
        $ext = strtolower(pathinfo($fileOriginal, PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_PRODUCT_EXTENSIONS)) {
            $errors['file'] = 'Type de fichier non autorisé.';
        }
        
        if (empty($errors)) {
            $newFile = true;
        }
    }
    
    // Traiter l'image de couverture (optionnel)
    $newCover = false;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $coverFile = $_FILES['cover_image'];
        $coverExt = strtolower(pathinfo($coverFile['name'], PATHINFO_EXTENSION));
        
        if (!in_array($coverExt, ALLOWED_IMAGE_EXTENSIONS)) {
            $errors['cover_image'] = 'L\'image doit être au format JPG, PNG ou GIF.';
        } elseif ($coverFile['size'] > MAX_AVATAR_SIZE) {
            $errors['cover_image'] = 'L\'image est trop volumineuse. Taille max : 5 MB.';
        }
        
        if (empty($errors)) {
            $newCover = true;
        }
    }
    
    // === SI PAS D'ERREURS ===
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $coverFileName = $product['cover_image'];
            
            // Si nouvelle image
            if ($newCover) {
                // Supprimer l'ancienne image
                if ($coverFileName && file_exists(PRODUCTS_PATH . $coverFileName)) {
                    unlink(PRODUCTS_PATH . $coverFileName);
                }
                
                $coverExt = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
                $coverUnique = uniqid() . '_' . time() . '.' . $coverExt;
                $coverPath = PRODUCTS_PATH . $coverUnique;
                
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverPath)) {
                    $coverFileName = $coverUnique;
                }
            }
            
            // Si nouveau fichier
            $filePath = $product['file_path'];
            $fileName = $product['file_name'];
            $fileSize = $product['file_size'];
            
            if ($newFile) {
                // Supprimer l'ancien fichier
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                $file = $_FILES['file'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $uniqueName = uniqid() . '_' . time() . '.' . $ext;
                $newFilePath = PRODUCTS_PATH . $uniqueName;
                
                if (move_uploaded_file($file['tmp_name'], $newFilePath)) {
                    $filePath = $newFilePath;
                    $fileName = sanitizeFilename($file['name']);
                    $fileSize = $file['size'];
                }
            }
            
            // Mise à jour en base
            $sql = "UPDATE products SET 
                    name = ?, description = ?, price = ?, category_id = ?, 
                    cover_image = ?, file_path = ?, file_name = ?, file_size = ?, status = ?
                    WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name, $description, $price, $categoryId,
                $coverFileName, $filePath, $fileName, $fileSize, $status,
                $productId, $userId
            ]);
            
            $pdo->commit();
            
            setFlashMessage('success', 'Produit modifié avec succès !');
            redirect(APP_URL . 'seller/products/index.php');
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = 'Erreur : ' . $e->getMessage();
        }
    }
}
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Modifier le produit</h1>
            <p class="dashboard-subtitle"><?php echo escape($product['name']); ?></p>
        </div>
        <div class="dashboard-actions">
            <a href="<?php echo APP_URL; ?>seller/products/index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    
    <div class="form-card">
        <?php if (isset($errors['general'])): ?>
            <div class="auth-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo escape($errors['general']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-main">
                    <div class="form-group">
                        <label for="name">Nom du produit *</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo escape($name); ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <span class="field-error"><?php echo escape($errors['name']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="6" required><?php echo escape($description); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="field-error"><?php echo escape($errors['description']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Prix (€) *</label>
                            <input type="text" id="price" name="price" 
                                   value="<?php echo escape($price); ?>" required>
                            <?php if (isset($errors['price'])): ?>
                                <span class="field-error"><?php echo escape($errors['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Catégorie *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <span class="field-error"><?php echo escape($errors['category_id']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Statut</label>
                        <select id="status" name="status">
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-sidebar">
                    <div class="form-group">
                        <label for="file">Fichier produit (laisser vide pour garder l'actuel)</label>
                        <div class="file-upload" id="fileUpload">
                            <input type="file" id="file" name="file">
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Changer le fichier</p>
                                <span class="file-hint">Actuel : <?php echo escape($product['file_name']); ?></span>
                            </div>
                            <div class="file-preview" style="display:none;">
                                <i class="fas fa-file"></i>
                                <span class="file-name"></span>
                                <button type="button" class="file-remove">&times;</button>
                            </div>
                        </div>
                        <?php if (isset($errors['file'])): ?>
                            <span class="field-error"><?php echo escape($errors['file']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="cover_image">Image de couverture</label>
                        <div class="file-upload" id="coverUpload">
                            <input type="file" id="cover_image" name="cover_image" accept="image/*">
                            <div class="file-upload-content">
                                <i class="fas fa-image"></i>
                                <p>Changer l'image</p>
                                <?php if ($product['cover_image']): ?>
                                    <span class="file-hint">Image actuelle présente</span>
                                <?php endif; ?>
                            </div>
                            <div class="file-preview" style="display:none;">
                                <img src="" alt="Aperçu">
                                <button type="button" class="file-remove">&times;</button>
                            </div>
                        </div>
                        <?php if (isset($errors['cover_image'])): ?>
                            <span class="field-error"><?php echo escape($errors['cover_image']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
                <a href="<?php echo APP_URL; ?>seller/products/index.php" class="btn btn-outline">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php
$additionalJS = <<<JS
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Upload de fichier
    const fileInput = document.getElementById('file');
    const fileUpload = document.getElementById('fileUpload');
    const fileContent = fileUpload.querySelector('.file-upload-content');
    const filePreview = fileUpload.querySelector('.file-preview');
    const fileName = filePreview.querySelector('.file-name');
    const fileRemove = filePreview.querySelector('.file-remove');
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            fileName.textContent = file.name;
            fileContent.style.display = 'none';
            filePreview.style.display = 'flex';
        }
    });
    
    fileRemove.addEventListener('click', function() {
        fileInput.value = '';
        fileContent.style.display = 'flex';
        filePreview.style.display = 'none';
    });
    
    // Upload d'image
    const coverInput = document.getElementById('cover_image');
    const coverUpload = document.getElementById('coverUpload');
    const coverContent = coverUpload.querySelector('.file-upload-content');
    const coverPreview = coverUpload.querySelector('.file-preview');
    const coverImg = coverPreview.querySelector('img');
    const coverRemove = coverPreview.querySelector('.file-remove');
    
    coverInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                coverImg.src = e.target.result;
                coverContent.style.display = 'none';
                coverPreview.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        }
    });
    
    coverRemove.addEventListener('click', function() {
        coverInput.value = '';
        coverContent.style.display = 'flex';
        coverPreview.style.display = 'none';
        coverImg.src = '';
    });
});
</script>
JS;

$content = ob_get_clean();
include __DIR__ . '/../../includes/header-private.php';
?>
<div class="app-layout">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>
<?php
include __DIR__ . '/../../includes/footer-private.php';
?>