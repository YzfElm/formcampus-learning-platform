</main>

<footer>
    <div class="container" style="margin:0 auto;">
        <p style="margin:0;">© <?= date('Y') ?> Form'Campus — La nouvelle ère de l'apprentissage numérique.</p>
        <p style="margin:0.5rem 0 0;font-size:0.85rem;">
            <a href="formations.php" style="color:var(--accent-color);text-decoration:none;">Formations</a> ·
            <a href="inscription_publique.php" style="color:var(--accent-color);text-decoration:none;">S'inscrire</a> ·
            <?php if (is_admin()): ?>
                <a href="admin_dashboard.php" style="color:var(--accent-color);text-decoration:none;">Admin</a>
            <?php endif; ?>
        </p>
    </div>
</footer>

<script src="js/script.js"></script>
</body>
</html>
