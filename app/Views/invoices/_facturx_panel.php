<?php
/**
 * Panneau Factur-X à inclure dans la fiche facture (app/Views/invoices/show.php)
 * Usage : <?php include __DIR__ . '/_facturx_panel.php'; ?>
 * Variables requises : $invoice (array), $csrf (string)
 */
$hasXml = !empty($invoice['facturx_xml']);
$xmlPreview = $hasXml ? htmlspecialchars(substr($invoice['facturx_xml'], 0, 420)) . '…' : null;
?>
<style>
.fx-panel { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 18px; padding: 1.35rem 1.5rem; margin-top: 1.5rem; }
.fx-header { display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
.fx-title  { font-size:1rem; font-weight:900; color:#065f46; display:flex; align-items:center; gap:.55rem; }
.fx-badge  { display:inline-flex; align-items:center; gap:.35rem; padding:.3rem .7rem; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
.fx-badge-ok   { background:#dcfce7; color:#166534; }
.fx-badge-none { background:#fef9c3; color:#854d0e; }
.fx-actions { display:flex; gap:.65rem; flex-wrap:wrap; }
.fx-btn  { display:inline-flex; align-items:center; gap:.45rem; padding:.65rem 1rem; border-radius:12px; font-weight:700; font-size:.88rem; text-decoration:none; border:none; cursor:pointer; }
.fx-btn-dl  { background:#01696f; color:#fff; }
.fx-btn-regen { background:#e0f2fe; color:#0369a1; }
.fx-preview { background:#fff; border:1px solid #d1fae5; border-radius:12px; padding:1rem; margin-top:.75rem; font-family:monospace; font-size:.72rem; color:#166534; white-space:pre-wrap; word-break:break-all; max-height:120px; overflow:hidden; position:relative; }
.fx-preview::after { content:''; position:absolute; bottom:0; left:0; right:0; height:36px; background:linear-gradient(transparent,#f0fdf4); }
.fx-info { font-size:.82rem; color:#047857; line-height:1.6; }
</style>

<div class="fx-panel">
  <div class="fx-header">
    <div class="fx-title">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      Factur-X (EN 16931)
      <?php if ($hasXml): ?>
        <span class="fx-badge fx-badge-ok">✓ Généré</span>
      <?php else: ?>
        <span class="fx-badge fx-badge-none">⚠ Non généré</span>
      <?php endif; ?>
    </div>
    <div class="fx-actions">
      <a href="/invoices/<?= (int)$invoice['id'] ?>/facturx" class="fx-btn fx-btn-dl">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Télécharger XML
      </a>
      <form method="POST" action="/invoices/<?= (int)$invoice['id'] ?>/facturx/regenerate" style="margin:0;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <button type="submit" class="fx-btn fx-btn-regen">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
          Régénérer
        </button>
      </form>
    </div>
  </div>
  <p class="fx-info">Format XML structuré conforme à la norme <strong>EN 16931 / Factur-X 1.0</strong>, requis pour la facturation électronique B2B en France (obligation 2026). Ce fichier peut être importé directement dans votre logiciel comptable.</p>
  <?php if ($xmlPreview): ?>
  <div class="fx-preview"><?= $xmlPreview ?></div>
  <?php endif; ?>
</div>
