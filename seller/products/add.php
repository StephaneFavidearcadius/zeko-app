<?php
// Fichier : seller/products/add.php
// Ajout d'un nouveau produit

require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Ajouter un produit - Zeko.app';

$userId = $_SESSION['user_id'];
$pdo = getPDO();

// Récupérer les catégories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$name = '';
$description = '';
$price = '';
$categoryId = '';
$status = 'active';
$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = str_replace(',', '.', $_POST['price'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'active');
    
    // === VALIDATION ===
    
    // 1. Nom
    if (empty($name)) {
        $errors['name'] = 'Le nom du produit est requis.';
    } elseif (strlen($name) > 255) {
        $errors['name'] = 'Le nom ne doit pas dépasser 255 caractères.';
    }
    
    // 2. Description
    if (empty($description)) {
        $errors['description'] = 'La description est requise.';
    }
    
    // 3. Prix
    if (empty($price)) {
        $errors['price'] = 'Le prix est requis.';
    } elseif (!is_numeric($price) || $price < 0) {
        $errors['price'] = 'Veuillez entrer un prix valide (ex: 9.99).';
    }
    
    // 4. Catégorie
    if ($categoryId <= 0) {
        $errors['category_id'] = 'Veuillez sélectionner une catégorie.';
    }
    
    // 5. Fichier
    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['file'] = 'Veuillez sélectionner un fichier.';
    } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errors['file'] = 'Erreur lors de l\'upload du fichier.';
    } else {
        $file = $_FILES['file'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileOriginal = $file['name'];
        
        // Vérifier la taille
        if ($fileSize > MAX_FILE_SIZE) {
            $errors['file'] = 'Le fichier est trop volumineux. Taille max : 50 MB.';
        }
        
        // Vérifier l'extension
        $ext = strtolower(pathinfo($fileOriginal, PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_PRODUCT_EXTENSIONS)) {
            $errors['file'] = 'Type de fichier non autorisé. Extensions acceptées : ' . implode(', ', ALLOWED_PRODUCT_EXTENSIONS);
        }
    }
    
    // 6. Image de couverture (optionnelle)
    $coverImage = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $coverFile = $_FILES['cover_image'];
        $coverExt = strtolower(pathinfo($coverFile['name'], PATHINFO_EXTENSION));
        
        if (!in_array($coverExt, ALLOWED_IMAGE_EXTENSIONS)) {
            $errors['cover_image'] = 'L\'image doit être au format JPG, PNG ou GIF.';
        } elseif ($coverFile['size'] > MAX_AVATAR_SIZE) {
            $errors['cover_image'] = 'L\'image est trop volumineuse. Taille max : 5 MB.';
        }
    }
    
    // === SI PAS D'ERREURS ===
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Générer un nom unique pour le fichier
            $fileName = sanitizeFilename($fileOriginal);
            $uniqueName = uniqid() . '_' . time() . '.' . $ext;
            $filePath = PRODUCTS_PATH . $uniqueName;
            
            // Déplacer le fichier
            if (!move_uploaded_file($fileTmp, $filePath)) {
                throw new Exception('Erreur lors du déplacement du fichier.');
            }
            
            // Traiter l'image de couverture
            $coverFileName = '';
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $coverExt = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
                $coverUnique = uniqid() . '_' . time() . '.' . $coverExt;
                $coverPath = PRODUCTS_PATH . $coverUnique;
                
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverPath)) {
                    $coverFileName = $coverUnique;
                }
            }
            
            // Insérer en base
            $sql = "INSERT INTO products (user_id, category_id, name, description, price, 
                    cover_image, file_path, file_name, file_size, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $userId,
                $categoryId,
                $name,
                $description,
                $price,
                $coverFileName,
                $filePath,
                $fileName,
                $fileSize,
                $status
            ]);
            
            $pdo->commit();
            
            setFlashMessage('success', 'Produit ajouté avec succès !');
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
            <h1>Ajouter un produit</h1>
            <p class="dashboard-subtitle">Ajoutez un nouveau produit numérique à votre boutique</p>
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
                <!-- Colonne principale -->
                <div class="form-main">
                    <div class="form-group">
                        <label for="name">Nom du produit *</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo escape($name); ?>" 
                               placeholder="Mon super produit" required>
                        <?php if (isset($errors['name'])): ?>
                            <span class="field-error"><?php echo escape($errors['name']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="6" 
                                  placeholder="Décrivez votre produit en détail..." required><?php echo escape($description); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="field-error"><?php echo escape($errors['description']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Prix (€) *</label>
                            <input type="text" id="price" name="price" 
                                   value="<?php echo escape($price); ?>" 
                                   placeholder="9.99" required>
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
                
                <!-- Colonne droite (fichiers) -->
                <div class="form-sidebar">
                    <div class="form-group">
                        <label for="file">Fichier produit *</label>
                        <div class="file-upload" id="fileUpload">
                            <input type="file" id="file" name="file" required>
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Glissez votre fichier ici ou cliquez</p>
                                <span class="file-hint">PDF, ZIP, DOC, TXT (max 50 MB)</span>
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
                                <p>Image de couverture (optionnelle)</p>
                                <span class="file-hint">JPG, PNG, GIF (max 5 MB)</span>
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
                    <i class="fas fa-save"></i> Ajouter le produit
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
include __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>
<?php
include __DIR__ . '/../../includes/footer.php';
?>