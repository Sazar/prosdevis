<?php
$fmtDate = fn(?string $d) => $d ? date('d M Y', strtotime($d)) : '—';
?>
<style>
.blog-shell { max-width: 1180px; margin: 0 auto; padding: 2rem 1rem 4rem; }
.blog-hero { display:grid; grid-template-columns: 1.1fr .9fr; gap: 2rem; align-items:center; padding: 2rem 0 2.5rem; }
.blog-kicker { display:inline-flex; padding:.35rem .7rem; background:#d1fae5; color:#065f46; font-size:.78rem; font-weight:800; border-radius:999px; text-transform:uppercase; letter-spacing:.05em; }
.blog-title { font-size: clamp(2.3rem, 4vw, 4.5rem); line-height:1.02; letter-spacing:-.04em; margin:.9rem 0 1rem; color:#0f172a; font-weight:900; }
.blog-sub { color:#475569; font-size:1.05rem; max-width:62ch; line-height:1.75; }
.blog-visual { background:linear-gradient(135deg,#01696f,#0f766e); min-height:300px; border-radius:28px; padding:2rem; display:flex; flex-direction:column; justify-content:flex-end; color:#ecfeff; box-shadow:0 30px 80px rgba(1,105,111,.18); }
.blog-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1.25rem; margin-top:1.5rem; }
.blog-card { background:#fff; border:1px solid #e5e7eb; border-radius:22px; overflow:hidden; box-shadow:0 18px 40px rgba(15,23,42,.06); display:flex; flex-direction:column; }
.blog-cover { height:180px; background:#f1f5f9 center/cover no-repeat; }
.blog-body { padding:1.2rem 1.2rem 1.35rem; display:flex; flex-direction:column; gap:.8rem; }
.blog-meta { font-size:.76rem; text-transform:uppercase; letter-spacing:.05em; color:#0f766e; font-weight:800; }
.blog-card-title { font-size:1.15rem; font-weight:900; line-height:1.25; color:#0f172a; }
.blog-excerpt { color:#64748b; line-height:1.7; }
.blog-link { margin-top:auto; display:inline-flex; align-items:center; gap:.45rem; color:#01696f; font-weight:800; text-decoration:none; }
@media (max-width: 960px){ .blog-hero{grid-template-columns:1fr;} }
</style>

<div class="blog-shell">
  <section class="blog-hero">
    <div>
      <span class="blog-kicker">Blog & SEO</span>
      <h1 class="blog-title">Conseils concrets pour mieux vendre, facturer et relancer.</h1>
      <p class="blog-sub">Le blog ProsDevis rassemble des articles utiles pour les artisans, freelances et PME : devis plus clairs, factures conformes, TVA, relances impayés, signature électronique et automatisation administrative.</p>
    </div>
    <div class="blog-visual">
      <div style="font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;opacity:.8;">ProsDevis éditorial</div>
      <div style="font-size:1.7rem;font-weight:900;line-height:1.15;margin-top:.6rem;">Des contenus pensés pour convertir la vitrine en acquisition organique.</div>
      <div style="margin-top:1rem;opacity:.88;line-height:1.7;">Landing SEO, pages indexables, articles experts, maillage interne et sitemap XML.</div>
    </div>
  </section>

  <section class="blog-grid">
    <?php foreach ($posts as $post): ?>
      <article class="blog-card">
        <div class="blog-cover"<?= !empty($post['cover_image']) ? ' style="background-image:url(' . htmlspecialchars($post['cover_image']) . ')"' : '' ?>></div>
        <div class="blog-body">
          <div class="blog-meta"><?= $fmtDate($post['published_at']) ?><?php if (!empty($post['author_name'])): ?> &middot; <?= htmlspecialchars($post['author_name']) ?><?php endif; ?></div>
          <h2 class="blog-card-title"><?= htmlspecialchars($post['title']) ?></h2>
          <p class="blog-excerpt"><?= htmlspecialchars($post['excerpt'] ?: mb_strimwidth(strip_tags($post['content']), 0, 180, '…')) ?></p>
          <a class="blog-link" href="/blog/<?= htmlspecialchars($post['slug']) ?>">Lire l’article &rarr;</a>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</div>
