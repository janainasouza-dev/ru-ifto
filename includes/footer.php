    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> RU IFTO - Restaurante Universitário do Instituto Federal do Tocantins</p>
        </div>
    </footer>
</div>
<div id="toast" class="toast"></div>
<?php if ($f = pegarFlash()): ?>
<script>
(function () {
    var t = document.getElementById('toast');
    t.textContent = <?= json_encode($f['msg'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    t.className = 'toast <?= e($f['tipo']) ?>';
    void t.offsetWidth; t.classList.add('show');
    setTimeout(function () { t.classList.remove('show'); }, 3500);
})();
</script>
<?php endif; ?>
</body>
</html>
