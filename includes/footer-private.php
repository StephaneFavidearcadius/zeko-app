<script src="<?php echo APP_URL; ?>assets/js/app.js"></script>

<?php if (isset($additionalJS)): ?>
    <?php echo $additionalJS; ?>
<?php endif; ?>

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