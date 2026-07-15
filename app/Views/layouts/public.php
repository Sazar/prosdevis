<?php
if (!isset($title)) { $title = 'ProsDevis'; }
$metaDescription = $metaDescription ?? 'ProsDevis, devis, factures et automatisation administrative pour indépendants et PME.';
$canonical = $canonical ?? '/';
$canonicalUrl = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $canonical;
$ogImage = $ogImage ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
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
    <style>
        :root {
            --bg:#f7f6f2; --surface:#ffffff; --border:#e5e7eb; --text:#111827; --muted:#6b7280; --primary:#01696f;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Inter,Arial,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;min-height:100vh}
        a{text-decoration:none;color:inherit}
        .public-header{padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:rgba(255,255,255,.88);backdrop-filter:blur(8px);position:sticky;top:0;z-index:5}
        .public-nav{max-width:1180px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;gap:1rem}
        .public-brand{font-weight:900;letter-spacing:-.03em;color:var(--primary);font-size:1.25rem}
        .public-menu{display:flex;gap:1rem;align-items:center;flex-wrap:wrap}
        .public-link{font-weight:700;color:#334155}
        .public-cta{display:inline-flex;align-items:center;padding:.7rem 1rem;border-radius:12px;background:var(--primary);color:#fff;font-weight:800}
        .public-footer{padding:2rem 1.25rem;color:var(--muted);font-size:.9rem;text-align:center}
        @media (max-width: 720px){ .public-nav{flex-direction:column;align-items:flex-start;} }
    </style>
</head>
<body>
    <header class="public-header">
        <div class="public-nav">
            <a href="/" class="public-brand">ProsDevis</a>
            <nav class="public-menu">
                <a class="public-link" href="/">Accueil</a>
                <a class="public-link" href="/blog">Blog</a>
                <a class="public-link" href="/pricing">Tarifs</a>
                <a class="public-cta" href="/login">Se connecter</a>
            </nav>
        </div>
    </header>
    <main>
        <?= $content ?? '' ?>
    </main>
    <footer class="public-footer">
        ProsDevis &mdash; Devis, factures, signature électronique, relances et conformité 2026.
    </footer>
</body>
</html>
