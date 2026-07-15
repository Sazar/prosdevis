<?php
$levelLabel = [1 => 'Niveau 1 — Rappel doux', 2 => 'Niveau 2 — Relance ferme', 3 => 'Niveau 3 — Mise en demeure'];
$levelColor = [1 => '#d97706', 2 => '#ea580c', 3 => '#b91c1c'];
?>
<style>
.rh-shell { max-width: 960px; margin: 0 auto; padding: 2rem 1rem 4rem; }
.rh-title  { font-size: 1.5rem; font-weight: 900; color: var(--color-text, #0f172a); margin-bottom: 1.5rem; }
.rh-empty  { background: var(--color-surface, #f9f8f5); border: 1px dashed #cbd5e1; border-radius: 16px; padding: 3rem; text-align: center; color: #64748b; }
.rh-table  { width: 100%; border-collapse: collapse; background: var(--color-surface, #fff); border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; }
.rh-table th { background: #01696f; color: #fff; text-align: left; padding: .85rem 1rem; font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; }
.rh-table td { padding: .9rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: .9rem; vertical-align: middle; }
.rh-table tr:last-child td { border-bottom: none; }
.badge { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
.badge-ok   { background: #ecfdf5; color: #047857; }
.badge-fail { background: #fef2f2; color: #b91c1c; }
.level-dot  { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: .4rem; }
.btn-back { display: inline-flex; align-items: center; gap: .5rem; background: #e2e8f0; color: #0f172a; padding: .7rem 1.1rem; border-radius: 12px; font-weight: 700; font-size: .9rem; text-decoration: none; margin-bottom: 1.25rem; }
</style>

<div class="rh-shell">
  <a href="javascript:history.back()" class="btn-back">&larr; Retour</a>
  <div class="rh-title">Historique des relances &mdash; Facture #<?= htmlspecialchars($invoiceId ?? '') ?></div>

  <?php if (empty($history)): ?>
  <div class="rh-empty">
    <div style="font-size:2rem;margin-bottom:.75rem;">📭</div>
    <div style="font-weight:700;margin-bottom:.4rem;">Aucune relance envoyée</div>
    <p>Cette facture n'a encore fait l'objet d'aucune relance.</p>
  </div>
  <?php else: ?>
  <table class="rh-table">
    <thead>
      <tr>
        <th>Niveau</th>
        <th>Envoyé à</th>
        <th>Date</th>
        <th>Déclencheur</th>
        <th>Statut</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($history as $r): ?>
      <tr>
        <td>
          <span class="level-dot" style="background:<?= $levelColor[$r['level']] ?? '#64748b' ?>;"></span>
          <?= htmlspecialchars($levelLabel[$r['level']] ?? 'Niveau ' . $r['level']) ?>
        </td>
        <td><?= htmlspecialchars($r['sent_to']) ?></td>
        <td><?= date('d/m/Y à H:i', strtotime($r['sent_at'])) ?></td>
        <td><?= $r['triggered_by'] ? htmlspecialchars(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) : '<em style="color:#94a3b8">Automatique (cron)</em>' ?></td>
        <td>
          <?php if ($r['sent']): ?>
            <span class="badge badge-ok">✓ Envoyé</span>
          <?php else: ?>
            <span class="badge badge-fail">✗ Échec mail</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
