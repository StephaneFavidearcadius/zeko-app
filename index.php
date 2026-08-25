<?php
// Fichier : index.php (Landing page)
// Page d'accueil publique de Zeko.app

require_once __DIR__ . '/includes/config.php';

$pageTitle = 'Vendez vos produits numériques simplement';

// Démarrer le buffering pour le HTML
ob_start();
?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <span class="hero-badge">🚀 Plateforme SaaS</span>
            <h1 class="hero-title">
                Vendez vos <span class="highlight">produits numériques</span> simplement
            </h1>
            <p class="hero-subtitle">
                Créez votre boutique en ligne en quelques minutes. Gérez vos ventes, 
                téléchargements et clients depuis un tableau de bord moderne.
            </p>
            <div class="hero-actions">
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i> Commencer gratuitement
                </a>
                <a href="#features" class="btn btn-outline btn-lg">
                    <i class="fas fa-play-circle"></i> Découvrir
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">10K+</span>
                    <span class="stat-label">Vendeurs actifs</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">50K+</span>
                    <span class="stat-label">Produits vendus</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">4.9★</span>
                    <span class="stat-label">Avis utilisateurs</span>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <div class="dashboard-preview">
                <div class="preview-header">
                    <div class="preview-dots">
                        <span></span><span></span><span></span>
                    </div>
                    <span>Tableau de bord</span>
                </div>
                <div class="preview-content">
                    <div class="preview-stat">
                        <span class="stat-value">€12,450</span>
                        <span class="stat-label">Chiffre d'affaires</span>
                    </div>
                    <div class="preview-stat">
                        <span class="stat-value">342</span>
                        <span class="stat-label">Ventes</span>
                    </div>
                    <div class="preview-stat">
                        <span class="stat-value">28</span>
                        <span class="stat-label">Produits</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES SECTION -->
<section id="features" class="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Fonctionnalités</span>
            <h2>Tout pour réussir votre vente</h2>
            <p>Une plateforme complète pour gérer votre activité de vente de produits numériques</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3>Gestion des produits</h3>
                <p>Ajoutez, modifiez et organisez vos produits numériques en quelques clics.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Statistiques en temps réel</h3>
                <p>Suivez vos ventes, revenus et performances avec des graphiques clairs.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-download"></i>
                </div>
                <h3>Téléchargements sécurisés</h3>
                <p>Gérez l'accès à vos fichiers avec un système de téléchargement protégé.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Gestion des clients</h3>
                <p>Consultez l'historique de vos clients et leurs achats.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3>Paiements simplifiés</h3>
                <p>Acceptez les paiements et gérez vos commandes facilement.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Sécurité garantie</h3>
                <p>Vos données et fichiers sont protégés par les meilleures pratiques.</p>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-it-works">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Comment ça marche</span>
            <h2>Commencez en 4 étapes simples</h2>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                <h3>Créez votre compte</h3>
                <p>Inscrivez-vous gratuitement et créez votre profil de vendeur</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-icon"><i class="fas fa-upload"></i></div>
                <h3>Ajoutez vos produits</h3>
                <p>Téléchargez vos fichiers et définissez les informations de vente</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-icon"><i class="fas fa-share-alt"></i></div>
                <h3>Partagez vos liens</h3>
                <p>Diffusez vos produits sur vos réseaux et votre site web</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-icon"><i class="fas fa-money-bill-wave"></i></div>
                <h3>Recevez vos ventes</h3>
                <p>Gérez vos commandes et téléchargements depuis votre dashboard</p>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="testimonials">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Témoignages</span>
            <h2>Ils nous font confiance</h2>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-stars">★★★★★</div>
                <p>"Zeko.app a révolutionné la façon dont je vends mes e-books. Interface simple et efficace."</p>
                <div class="testimonial-author">
                    <strong>Sophie Martin</strong>
                    <span>Auteur de e-books</span>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">★★★★★</div>
                <p>"La meilleure plateforme pour vendre des formations en ligne. Mes ventes ont doublé !"</p>
                <div class="testimonial-author">
                    <strong>Thomas Dubois</strong>
                    <span>Formateur en ligne</span>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">★★★★★</div>
                <p>"Simple, rapide et professionnel. Je recommande à tous les créateurs de contenu."</p>
                <div class="testimonial-author">
                    <strong>Julie Petit</strong>
                    <span>Créatrice de templates</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA FINAL -->
<section class="cta-final">
    <div class="container">
        <div class="cta-content">
            <h2>Prêt à vendre vos produits numériques ?</h2>
            <p>Rejoignez des milliers de vendeurs et commencez à gagner de l'argent dès aujourd'hui.</p>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-right"></i> Créer mon compte
            </a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

// Inclure le header
include __DIR__ . '/includes/header.php';

// Afficher le contenu
echo $content;

// Inclure le footer
include __DIR__ . '/includes/footer.php';
?>