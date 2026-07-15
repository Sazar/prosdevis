<?php
// Layout principal de l'application
// Variables attendues: $title, $user, $activeNav
$activeNav = $activeNav ?? '';

// Lecture du thème : priorité cookie > préférence user en BDD > 'auto'
// Évite le FOUC (Flash Of Unstyled Content) en appliquant le bon data-theme dès le HTML
$theme = 'auto';
if (!empty($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light', 'dark'])) {
    $theme = $_COOKIE['theme'];
} elseif (!empty($user['theme']) && in_array($user['theme'], ['light', 'dark', 'auto'])) {
    $theme = $user['theme'];
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?= htmlspecialchars($theme) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'ProsDevis') ?> — ProsDevis</title>
  <meta name="robots" content="noindex">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/main.css">
  <link rel="stylesheet" href="/assets/css/app.css">
  <?= $extraHead ?? '' ?>
  <script>
    // Applique le thème AVANT le rendu pour éviter tout flash
    (function(){
      var cookie = document.cookie.match(/(?:^|;\s*)theme=([^;]*)/);
      var saved  = cookie ? cookie[1] : null;
      var system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      var theme  = (saved === 'light' || saved === 'dark') ? saved : system;
      document.documentElement.setAttribute('data-theme', theme);
    })();
  </script>
</head>
<body>

<div class="app-layout" id="app">

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar" role="navigation" aria-label="Navigation principale">
    <div class="sidebar-logo">
      <a href="/dashboard" class="logo-link">
        <svg aria-label="ProsDevis" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" width="32" height="32">
          <rect width="36" height="36" rx="10" fill="var(--color-primary)"/>
          <path d="M10 10h10a6 6 0 0 1 0 12H10V10zm0 12h12" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M10 26h16" stroke="white" stroke-width="2.2" stroke-linecap="round" opacity="0.6"/>
        </svg>
        <span class="logo-text">ProsDevis</span>
      </a>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section">
        <span class="nav-section-label">Principal</span>
        <a href="/dashboard" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          Tableau de bord
        </a>
        <a href="/quotes" class="nav-item <?= $activeNav === 'quotes' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          Devis
          <?php if (($stats['sent'] ?? 0) > 0): ?>
          <span class="nav-badge"><?= $stats['sent'] ?></span>
          <?php endif; ?>
        </a>
        <a href="/invoices" class="nav-item <?= $activeNav === 'invoices' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          Factures
        </a>
        <a href="/clients" class="nav-item <?= $activeNav === 'clients' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Clients
        </a>
      </div>

      <div class="nav-section">
        <span class="nav-section-label">Catalogue</span>
        <a href="/products" class="nav-item <?= $activeNav === 'products' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          Produits & services
        </a>
      </div>

      <div class="nav-section">
        <span class="nav-section-label">Outils</span>
        <a href="/reminders" class="nav-item <?= $activeNav === 'reminders' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          Relances
        </a>
        <a href="/exports" class="nav-item <?= $activeNav === 'exports' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Exports
        </a>
        <a href="/blog" class="nav-item <?= $activeNav === 'blog' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          Blog
        </a>
      </div>

      <?php if (\App\Helpers\Auth::isAdmin()): ?>
      <div class="nav-section">
        <span class="nav-section-label">Administration</span>
        <a href="/settings" class="nav-item <?= $activeNav === 'settings' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          Paramètres
        </a>
        <a href="/team" class="nav-item <?= $activeNav === 'team' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Équipe
        </a>
      </div>
      <?php endif; ?>
    </nav>

    <!-- User card en bas -->
    <div class="sidebar-user">
      <div class="user-avatar"><?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1)) ?></div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></div>
        <div class="user-role"><?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?></div>
      </div>
      <a href="/logout" class="user-logout" title="Se déconnecter">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>
  </aside>

  <!-- Topbar -->
  <header class="topbar" role="banner">
    <div class="topbar-left">
      <button class="topbar-menu-btn" id="sidebarToggle" aria-label="Menu" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <h1 class="topbar-title"><?= htmlspecialchars($pageTitle ?? $title ?? '') ?></h1>
    </div>
    <div class="topbar-right">
      <!-- Bouton dark mode -->
      <button class="topbar-icon-btn" id="themeToggleBtn" data-theme-toggle aria-label="Basculer le thème">
        <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"/>
          <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
        </svg>
      </button>
      <!-- Notifications -->
      <button class="topbar-icon-btn" aria-label="Notifications">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      </button>
      <!-- Bouton nouveau devis -->
      <a href="/quotes/new" class="btn btn-primary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau devis
      </a>
    </div>
  </header>

  <!-- Contenu principal -->
  <main class="main-content" id="main-content" role="main">
    <?php if ($flash = \App\Helpers\Session::flash('success')): ?>
    <div class="alert alert-success" role="alert"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($flash = \App\Helpers\Session::flash('error')): ?>
    <div class="alert alert-error" role="alert"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?= $content ?>
  </main>

</div><!-- .app-layout -->

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
(function(){
  var html = document.documentElement;
  var btn  = document.getElementById('themeToggleBtn');
  var icon = document.getElementById('themeIcon');

  function getTheme() {
    var cookie = document.cookie.match(/(?:^|;\s*)theme=([^;]*)/);
    if (cookie && (cookie[1] === 'light' || cookie[1] === 'dark')) return cookie[1];
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function setIcon(t) {
    if (!icon) return;
    icon.innerHTML = t === 'dark'
      ? '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>'
      : '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
    btn && btn.setAttribute('aria-label', t === 'dark' ? 'Passer en mode clair' : 'Passer en mode sombre');
  }

  var current = getTheme();
  html.setAttribute('data-theme', current);
  setIcon(current);

  btn && btn.addEventListener('click', function(){
    current = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', current);
    document.cookie = 'theme=' + current + '; path=/; max-age=31536000; SameSite=Lax';
    setIcon(current);

    // Sync préférence en BDD en arrière-plan (fire & forget)
    fetch('/settings/theme', {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
      body: JSON.stringify({theme: current, _token: document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').content : ''})
    }).catch(function(){});
  });

  // Sidebar toggle mobile
  var toggleBtn = document.getElementById('sidebarToggle');
  var sidebar   = document.getElementById('sidebar');
  var overlay   = document.getElementById('sidebarOverlay');

  function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
    toggleBtn && toggleBtn.setAttribute('aria-expanded', 'false');
  }

  toggleBtn && toggleBtn.addEventListener('click', function(){
    var isOpen = sidebar.classList.toggle('open');
    overlay.classList.toggle('active', isOpen);
    toggleBtn.setAttribute('aria-expanded', String(isOpen));
  });
  overlay && overlay.addEventListener('click', closeSidebar);

  // Ferme sidebar au resize desktop
  window.matchMedia('(min-width: 1025px)').addEventListener('change', function(e){
    if (e.matches) closeSidebar();
  });
})();
</script>
<?= $extraScripts ?? '' ?>
</body>
</html>
