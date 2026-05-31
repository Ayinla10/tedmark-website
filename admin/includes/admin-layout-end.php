  </div><!-- /.tm-content -->
</div><!-- /.tm-main -->

<script>
// ── Light/Dark mode toggle ─────────────────────────────────
(function(){
  const body = document.body;
  const icon = document.getElementById('modeIcon');
  const saved = localStorage.getItem('tm_admin_mode');

  function applyMode(mode) {
    if (mode === 'light') {
      body.classList.add('light');
      if (icon) { icon.className = 'fa-solid fa-sun'; }
    } else {
      body.classList.remove('light');
      if (icon) { icon.className = 'fa-solid fa-moon'; }
    }
  }

  // Apply on load (before paint to avoid flash)
  applyMode(saved || 'dark');

  window.toggleMode = function() {
    const isLight = body.classList.contains('light');
    const next = isLight ? 'dark' : 'light';
    localStorage.setItem('tm_admin_mode', next);
    applyMode(next);
  };
})();
</script>

</body>
</html>
