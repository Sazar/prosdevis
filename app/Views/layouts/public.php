<?php
if (!isset($title)) { $title = 'ProsDevis'; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        :root {
            --bg:#f7f6f2; --surface:#ffffff; --border:#e5e7eb; --text:#111827; --muted:#6b7280; --primary:#01696f;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Inter,Arial,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;min-height:100vh}
        a{text-decoration:none;color:inherit}
        .public-header{padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:rgba(255,255,255,.85);backdrop-filter:blur(8px);position:sticky;top:0;z-index:5}
        .public-brand{font-weight:900;letter-spacing:-.03em;color:var(--primary);font-size:1.25rem}
        .public-footer{padding:2rem 1.25rem;color:var(--muted);font-size:.9rem;text-align:center}
    </style>
</head>
<body>
    <header class="public-header">
        <div class="public-brand">ProsDevis</div>
    </header>
    <main>
        <?= $content ?? '' ?>
    </main>
    <footer class="public-footer">
        Signature électronique sécurisée &mdash; ProsDevis
    </footer>
</body>
</html>
