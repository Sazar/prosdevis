<?php
$title = 'Connexion - ProsDevis';
$bodyClass = 'auth-page';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="auto">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/main.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body class="auth-body">

<div class="auth-container">
  <div class="auth-card">

    <div class="auth-header">
      <div class="auth-logo">
        <span class="logo-icon">🧾</span>
        <span class="logo-text">ProsDevis</span>
      </div>
      <h1 class="auth-title">Bon retour !</h1>
      <p class="auth-subtitle">Connectez-vous à votre espace professionnel</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
      </svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/login" class="auth-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

      <div class="form-group">
        <label for="email">Adresse email</label>
        <div class="input-icon-wrapper">
          <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741Z"/>
          </svg>
          <input type="email" id="email" name="email"
                 class="form-control input-with-icon"
                 placeholder="vous@entreprise.fr"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 autocomplete="email" required autofocus>
        </div>
      </div>

      <div class="form-group">
        <label for="password">
          Mot de passe
          <a href="/forgot-password" class="label-link">Oublié ?</a>
        </label>
        <div class="input-icon-wrapper">
          <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
          </svg>
          <input type="password" id="password" name="password"
                 class="form-control input-with-icon"
                 placeholder="Votre mot de passe"
                 autocomplete="current-password" required>
          <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
              <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="form-check">
        <input type="checkbox" id="remember" name="remember" value="1">
        <label for="remember">Se souvenir de moi</label>
      </div>

      <button type="submit" class="btn btn-primary w-full btn-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z"/>
          <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
        </svg>
        Se connecter
      </button>
    </form>

    <div class="auth-footer">
      <p>Pas encore de compte ? <a href="/pricing">Découvrir ProsDevis</a></p>
    </div>

  </div>

  <!-- Visuel décoratif -->
  <div class="auth-visual" aria-hidden="true">
    <div class="auth-visual-content">
      <div class="visual-badge">Gratuit pour commencer</div>
      <h2>La solution devis la plus complète pour les pros</h2>
      <ul class="visual-features">
        <li>✅ PDF conformes avec mentions légales</li>
        <li>✅ Numérotation automatique</li>
        <li>✅ Signature électronique intégrée</li>
        <li>✅ Transformation Devis → Facture</li>
        <li>✅ Multi-utilisateurs & rôles</li>
        <li>✅ Conforme RGPD & Factur-X 2026</li>
      </ul>
    </div>
  </div>
</div>

<script>
  // Toggle password visibility
  document.querySelector('.toggle-password')?.addEventListener('click', function() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
  });

  // Theme management
  const savedTheme = localStorage.getItem('theme') || 'auto';
  document.documentElement.setAttribute('data-theme', savedTheme);
</script>
</body>
</html>
