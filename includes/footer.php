</main>
<!-- ============================================ -->
<!-- FIN DU CONTENU PRINCIPAL                     -->
<!-- ============================================ -->

<!-- ============================================ -->
<!-- FOOTER                                       -->
<!-- ============================================ -->
<footer>
    <div class="container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <div class="logo">Zeko<span>.app</span></div>
                <p>La plateforme SaaS qui permet aux créateurs de vendre leurs produits numériques simplement.</p>
            </div>
            
            <!-- Liens rapides -->
            <div class="footer-links">
                <h4>Produit</h4>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>#features">Fonctionnalités</a></li>
                    <li><a href="<?php echo APP_URL; ?>#how-it-works">Comment ça marche</a></li>
                    <li><a href="<?php echo APP_URL; ?>register.php">S'inscrire</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div class="footer-links">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Aide</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
            
            <!-- Légal -->
            <div class="footer-links">
                <h4>Légal</h4>
                <ul>
                    <li><a href="#">Conditions générales</a></li>
                    <li><a href="#">Politique de confidentialité</a></li>
                    <li><a href="#">Mentions légales</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <span>&copy; <?php echo date('Y'); ?> Zeko.app. Tous droits réservés.</span>
            <div class="footer-bottom-links">
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                <a href="#"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript Global -->
<script src="<?php echo APP_URL; ?>assets/js/app.js?v=<?php echo time(); ?>"></script>

<?php if (isset($additionalJS)): ?>
    <?php echo $additionalJS; ?>
<?php endif; ?>

<!-- Script pour fermer les messages flash -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.flash-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.flash-message').remove();
        });
    });
});
</script>

</body>
</html>