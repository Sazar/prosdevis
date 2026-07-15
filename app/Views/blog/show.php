<?php
$fmtDate = fn(?string $d) => $d ? date('d M Y', strtotime($d)) : '—';
?>
<style>
.post-shell { max-width: 980px; margin: 0 auto; padding: 2rem 1rem 4rem; }
.post-cover { border-radius: 28px; min-height: 320px; background:#e2e8f0 center/cover no-repeat; box-shadow: 0 24px 60px rgba(15,23,42,.10); }
.post-meta { margin-top: 1.25rem; color:#0f766e; font-size:.82rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
.post-title { font-size: clamp(2rem, 4vw, 3.8rem); line-height:1.05; letter-spacing:-.04em; color:#0f172a; font-weight:900; margin:.75rem 0 1rem; }
.post-excerpt { color:#475569; font-size:1.06rem; line-height:1.8; max-width:70ch; }
.post-content { margin-top: 2rem; background:#fff; border:1px solid #e5e7eb; border-radius: 24px; padding: 2rem; color:#334155; line-height:1.85; box-shadow: 0 20px 45px rgba(15,23,42,.06); }
.post-content h2, .post-content h3 { color:#0f172a; margin:1.6rem 0 .9rem; line-height:1.2; }
.post-content p { margin-bottom:1rem; }
.post-content ul { margin: 0 0 1rem 1.3rem; }
.related { margin-top: 2rem; }
.related-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:1rem; margin-top:1rem; }
.related-card { background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:1rem; text-decoration:none; color:inherit; }
.related-card h3 { font-size:1rem; line-height:1.3; color:#0f172a; margin:.35rem 0 .5rem; }
.related-card p { color:#64748b; line-height:1.6; font-size:.92rem; }
</style>

<div class="post-shell">
  <a href="/blog" style="display:inline-flex;margin-bottom:1rem;color:#01696f;font-weight:800;text-decoration:none;">&larr; Retour au blog</a>
  <div class="post-cover"<?= !empty($post['cover_image']) ? ' style="background-image:url(' . htmlspecialchars($post['cover_image']) . ')"' : '' ?>></div>
  <div class="post-meta"><?= $fmtDate($post['published_at']) ?><?php if (!empty($post['author_name'])): ?> &middot; <?= htmlspecialchars($post['author_name']) ?><?php endif; ?></div>
  <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
  <?php if (!empty($post['excerpt'])): ?><p class="post-excerpt"><?= htmlspecialchars($post['excerpt']) ?></p><?php endif; ?>

  <article class="post-content">
    <?= $post['content'] ?>
  </article>

  <?php if (!empty($related)): ?>
  <section class="related">
    <h2 style="font-size:1.35rem;color:#0f172a;font-weight:900;">À lire aussi</h2>
    <div class="related-grid">
      <?php foreach ($related as $item): ?>
      <a class="related-card" href="/blog/<?= htmlspecialchars($item['slug']) ?>">
        <div style="font-size:.75rem;font-weight:800;color:#0f766e;text-transform:uppercase;letter-spacing:.05em;"><?= $fmtDate($item['published_at']) ?></div>
        <h3><?= htmlspecialchars($item['title']) ?></h3>
        <p><?= htmlspecialchars($item['excerpt'] ?: 'Article ProsDevis') ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</div>
