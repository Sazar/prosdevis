<?php $csrf = App\Helpers\Session::csrf(); ?>
<style>
.ba-shell { max-width: 1180px; margin: 0 auto; padding: 2rem 1rem 4rem; }
.ba-head { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1.25rem; }
.ba-title { font-size:1.7rem; font-weight:900; color:#0f172a; }
.ba-btn { display:inline-flex; align-items:center; gap:.45rem; background:#01696f; color:#fff; text-decoration:none; padding:.8rem 1rem; border-radius:12px; font-weight:800; }
.ba-table { width:100%; border-collapse:collapse; background:#fff; border:1px solid #e5e7eb; border-radius:18px; overflow:hidden; }
.ba-table th { background:#0f172a; color:#fff; text-align:left; padding:.85rem 1rem; font-size:.78rem; text-transform:uppercase; letter-spacing:.05em; }
.ba-table td { padding: .95rem 1rem; border-bottom:1px solid #f1f5f9; vertical-align:top; }
.ba-status { display:inline-flex; padding:.35rem .65rem; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
.ba-draft { background:#fef3c7; color:#92400e; }
.ba-published { background:#dcfce7; color:#166534; }
</style>
<div class="ba-shell">
  <div class="ba-head">
    <div class="ba-title">Administration du blog</div>
    <a href="/admin/blog/create" class="ba-btn">+ Nouvel article</a>
  </div>

  <table class="ba-table">
    <thead>
      <tr>
        <th>Titre</th>
        <th>Slug</th>
        <th>Statut</th>
        <th>Auteur</th>
        <th>Publié le</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($posts as $post): ?>
      <tr>
        <td><strong><?= htmlspecialchars($post['title']) ?></strong><br><span style="color:#64748b;"><?= htmlspecialchars($post['excerpt'] ?: '—') ?></span></td>
        <td><code><?= htmlspecialchars($post['slug']) ?></code></td>
        <td><span class="ba-status <?= $post['status'] === 'published' ? 'ba-published' : 'ba-draft' ?>"><?= htmlspecialchars($post['status']) ?></span></td>
        <td><?= htmlspecialchars($post['author_name'] ?: '—') ?></td>
        <td><?= !empty($post['published_at']) ? date('d/m/Y H:i', strtotime($post['published_at'])) : '—' ?></td>
        <td><a href="/admin/blog/<?= (int)$post['id'] ?>/edit" style="color:#01696f;font-weight:800;text-decoration:none;">Modifier</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
