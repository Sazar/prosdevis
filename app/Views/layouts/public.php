<?php
if (!isset($title)) { $title = 'ProsDevis'; }
$metaDescription = $metaDescription ?? 'ProsDevis, devis, factures et automatisation administrative pour indépendants et PME.';
$canonical = $canonical ?? '/';
$canonicalUrl = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $canonical;
$ogImage = $ogImage ?? null;

// Lecture thème depuis cookie (anti-FOUC)
$pubTheme = 'auto';
if (!empty($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light', 'dark'])) {
    $pubTheme = $_COOKIE['theme'];
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?= htmlspecialchars($pubTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <?php if ($ogImage): ?><meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="/assets/css/public.css">
    <!-- Anti-FOUC : applique le thème avant le premier rendu -->
    <script>
    (function(){
        var c = document.cookie.match(/(?:^|;\s*)theme=([^;]*)/);
        var s = c ? c[1] : null;
        var t = (s === 'light' || s === 'dark') ? s
              : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', t);
    })();
    </script>
</head>
<body>
    <header class="public-header">
        <div class="public-nav">
            <a href="/" class="public-brand">
                <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" width="28" height="28" aria-hidden="true">
                    <rect width="36" height="36" rx="10" fill="var(--pub-primary)"/>
                    <path d="M10 10h10a6 6 0 0 1 0 12H10V10zm0 12h12" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 26h16" stroke="white" stroke-width="2.2" stroke-linecap="round" opacity="0.6"/>
                </svg>
                ProsDevis
            </a>
            <nav class="public-menu" aria-label="Navigation principale">
                <a class="public-link" href="/">Accueil</a>
                <a class="public-link" href="/blog">Blog</a>
                <a class="public-link" href="/pricing">Tarifs</a>
                <!-- Toggle dark mode -->
                <button id="pubThemeToggle" class="pub-theme-btn" aria-label="Basculer le thème">
                    <svg id="pubThemeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                </button>
                <a class="public-cta" href="/login">Se connecter</a>
            </nav>
        </div>
    </header>
    <main>
        <?= $content ?? '' ?>
    </main>
    <footer class="public-footer">
        <div class="public-footer-inner">
            <span class="public-footer-brand">ProsDevis</span>
            <span class="public-footer-sep">&mdash;</span>
            <span>Devis, factures, signature électronique, relances et conformité 2026.</span>
            <div class="public-footer-links">
                <a href="/mentions-legales">Mentions légales</a>
                <a href="/confidentialite">Confidentialité</a>
                <a href="/blog">Blog</a>
            </div>
        </div>
    </footer>

    <script>
    (function(){
        var html = document.documentElement;
        var btn  = document.getElementById('pubThemeToggle');
        var icon = document.getElementById('pubThemeIcon');

        function getCookie() {
            var c = document.cookie.match(/(?:^|;\s*)theme=([^;]*)/);
            return c ? c[1] : null;
        }

        function getTheme() {
            var s = getCookie();
            if (s === 'light' || s === 'dark') return s;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        function setIcon(t) {
            if (!icon) return;
            if (t === 'dark') {
                icon.innerHTML = '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>';
                btn && (btn.setAttribute('aria-label', 'Passer en mode clair'));
            } else {
                icon.innerHTML = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
                btn && (btn.setAttribute('aria-label', 'Passer en mode sombre'));
            }
        }

        var current = getTheme();
        html.setAttribute('data-theme', current);
        setIcon(current);

        btn && btn.addEventListener('click', function(){
            current = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', current);
            document.cookie = 'theme=' + current + '; path=/; max-age=31536000; SameSite=Lax';
            setIcon(current);
        });

        // Sync auto si l'OS change et qu'aucun cookie n'est défini
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e){
            if (!getCookie()) {
                current = e.matches ? 'dark' : 'light';
                html.setAttribute('data-theme', current);
                setIcon(current);
            }
        });
    })();
    </script>
    <?= $extraScripts ?? '' ?>
</body>
</html>
