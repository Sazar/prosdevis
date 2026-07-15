<?php
$csrf = App\Helpers\Session::csrf();
$isEdit = !empty($post['id']);
$action = $isEdit ? '/admin/blog/' . (int)$post['id'] : '/admin/blog';
?>
<style>
.bf-shell { max-width: 960px; margin: 0 auto; padding: 2rem 1rem 4rem; }
.bf-card { background:#fff; border:1px solid #e5e7eb; border-radius:24px; padding:1.5rem; box-shadow:0 20px 50px rgba(15,23,42,.06); }
.bf-title { font-size:1.55rem; font-weight:900; color:#0f172a; margin-bottom:1rem; }
.bf-grid { display:grid; gap:1rem; }
.bf-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.bf-group label { display:block; font-size:.84rem; font-weight:800; color:#334155; margin-bottom:.4rem; }
.bf-input, .bf-textarea, .bf-select { width:100%; border:1px solid #d6dbe3; border-radius:14px; padding:.85rem .95rem; font:inherit; }
.bf-textarea { min-height: 170px; }
.bf-submit { display:inline-flex; align-items:center; gap:.45rem; border:none; background:#01696f; color:#fff; padding:.9rem 1.2rem; border-radius:14px; font-weight:800; cursor:pointer; }
@media (max-width: 800px){ .bf-grid-2{grid-template-columns:1fr;} }
</style>
<div class="bf-shell">
  <div class="bf-card">
    <div class="bf-title"><?= $isEdit ? 'Modifier l’article' : 'Nouvel article' ?></div>
    <form method="POST" action="<?= htmlspecialchars($action) ?>" class="bf-grid">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <div class="bf-grid-2">
        <div class="bf-group">
          <label for="title">Titre</label>
          <input id="title" name="title" class="bf-input" required value="<?= htmlspecialchars($post['title'] ?? '') ?>">
        </div>
        <div class="bf-group">
          <label for="slug">Slug</label>
          <input id="slug" name="slug" class="bf-input" required value="<?= htmlspecialchars($post['slug'] ?? '') ?>">
        </div>
      </div>

      <div class="bf-group">
        <label for="excerpt">Extrait</label>
        <textarea id="excerpt" name="excerpt" class="bf-textarea" style="min-height:110px;"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
      </div>

      <div class="bf-group">
        <label for="content">Contenu HTML</label>
        <textarea id="content" name="content" class="bf-textarea" required><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
      </div>

      <div class="bf-grid-2">
        <div class="bf-group">
          <label for="cover_image">Image de couverture (URL)</label>
          <input id="cover_image" name="cover_image" class="bf-input" value="<?= htmlspecialchars($post['cover_image'] ?? '') ?>">
        </div>
        <div class="bf-group">
          <label for="og_image">Image Open Graph (URL)</label>
          <input id="og_image" name="og_image" class="bf-input" value="<?= htmlspecialchars($post['og_image'] ?? '') ?>">
        </div>
      </div>

      <div class="bf-grid-2">
        <div class="bf-group">
          <label for="meta_title">Meta title</label>
          <input id="meta_title" name="meta_title" class="bf-input" value="<?= htmlspecialchars($post['meta_title'] ?? '') ?>">
        </div>
        <div class="bf-group">
          <label for="status">Statut</label>
          <select id="status" name="status" class="bf-select">
            <option value="draft"<?= (($post['status'] ?? 'draft') === 'draft') ? ' selected' : '' ?>>Brouillon</option>
            <option value="published"<?= (($post['status'] ?? '') === 'published') ? ' selected' : '' ?>>Publié</option>
          </select>
        </div>
      </div>

      <div class="bf-group">
        <label for="meta_desc">Meta description</label>
        <textarea id="meta_desc" name="meta_desc" class="bf-textarea" style="min-height:110px;"><?= htmlspecialchars($post['meta_desc'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="bf-submit">Enregistrer l’article</button>
    </form>
  </div>
</div>
