<?php
$statusLabels = [
    'draft'     => ['label'=>'Brouillon',  'class'=>'badge-neutral'],
    'sent'      => ['label'=>'Envoyé',     'class'=>'badge-info'],
    'viewed'    => ['label'=>'Consulté',   'class'=>'badge-info'],
    'accepted'  => ['label'=>'Accepté',    'class'=>'badge-success'],
    'refused'   => ['label'=>'Refusé',     'class'=>'badge-danger'],
    'expired'   => ['label'=>'Expiré',     'class'=>'badge-warning'],
    'converted' => ['label'=>'Converti',   'class'=>'badge-primary'],
];
$currentStatus = $_GET['status'] ?? '';
$search        = htmlspecialchars($_GET['q'] ?? '');
?>
<div class="card" style="margin-bottom:var(--space-5);">
  <div class="card-header">
    <h2 style="font-size:var(--text-xl); font-weight:800;">Mes devis</h2>
    <a href="/quotes/new" class="btn btn-primary btn-sm">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Nouveau devis
    </a>
  </div>
</div>

<!-- Filtres -->
<div class="card" style="margin-bottom:var(--space-4);">
  <form method="GET" action="/quotes" style="display:flex; gap:var(--space-3); flex-wrap:wrap; align-items:center;">
    <input type="search" name="q" placeholder="Rechercher…" class="form-control" style="max-width:240px;" value="<?= $search ?>">
    <select name="status" class="form-control" style="max-width:180px;" onchange="this.form.submit()">
      <option value="">Tous les statuts</option>
      <?php foreach ($statusLabels as $val => $s): ?>
      <option value="<?= $val ?>" <?= $currentStatus === $val ? 'selected' : '' ?>><?= $s['label'] ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
    <?php if ($currentStatus || $search): ?>
    <a href="/quotes" class="btn btn-ghost btn-sm">Réinitialiser</a>
    <?php endif; ?>
  </form>
</div>

<!-- Tableau -->
<?php if (empty($quotes)): ?>
<div class="card">
  <div style="text-align:center; padding:var(--space-16) var(--space-8); color:var(--color-text-muted);">
    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin:0 auto var(--space-4); color:var(--color-text-faint);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
    <h3 style="color:var(--color-text); margin-bottom:var(--space-2);"><?= $currentStatus || $search ? 'Aucun résultat' : 'Aucun devis pour l\'instant' ?></h3>
    <p style="max-width:36ch; margin:0 auto var(--space-6);"><?= $currentStatus || $search ? 'Essayez d\'autres filtres.' : 'Créez votre premier devis et commencez à facturer.' ?></p>
    <?php if (!$currentStatus && !$search): ?>
    <a href="/quotes/new" class="btn btn-primary">Créer un devis</a>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<div class="card" style="padding:0; overflow:hidden;">
  <table style="width:100%; border-collapse:collapse; font-size:var(--text-sm);">
    <thead>
      <tr style="background:var(--color-surface-2); border-bottom:1px solid var(--color-border);">
        <th style="padding:var(--space-3) var(--space-4); text-align:left; font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted);">Numéro</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left; font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted);">Client</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left; font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted);">Objet</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left; font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted);">Date</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left; font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted);">Validité</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:right; font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted);">Montant TTC</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left; font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted);">Statut</th>
        <th style="padding:var(--space-3) var(--space-4);"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($quotes as $q):
        $s = $statusLabels[$q['status']] ?? ['label'=>$q['status'],'class'=>'badge-neutral'];
        $expired = $q['validity_date'] < date('Y-m-d') && !in_array($q['status'], ['accepted','converted','refused']);
      ?>
      <tr style="border-bottom:1px solid var(--color-border); transition:background var(--transition-interactive);" onmouseover="this.style.background='var(--color-surface-2)'" onmouseout="this.style.background=''">
        <td style="padding:var(--space-3) var(--space-4);">
          <a href="/quotes/<?= $q['id'] ?>" style="font-weight:700; color:var(--color-primary); text-decoration:none;"><?= htmlspecialchars($q['number']) ?></a>
        </td>
        <td style="padding:var(--space-3) var(--space-4); color:var(--color-text);"><?= htmlspecialchars($q['client_name'] ?? '—') ?></td>
        <td style="padding:var(--space-3) var(--space-4); color:var(--color-text-muted); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($q['title']) ?></td>
        <td style="padding:var(--space-3) var(--space-4); color:var(--color-text-muted); white-space:nowrap;"><?= date('d/m/Y', strtotime($q['issue_date'])) ?></td>
        <td style="padding:var(--space-3) var(--space-4); white-space:nowrap; <?= $expired ? 'color:#e53e3e;' : 'color:var(--color-text-muted);' ?>">
          <?= date('d/m/Y', strtotime($q['validity_date'])) ?>
          <?= $expired ? ' ⚠' : '' ?>
        </td>
        <td style="padding:var(--space-3) var(--space-4); text-align:right; font-weight:700; font-variant-numeric:tabular-nums;">
          <?= number_format($q['total_ttc'], 2, ',', '&nbsp;') ?> €
        </td>
        <td style="padding:var(--space-3) var(--space-4);">
          <span class="badge <?= $s['class'] ?>"><?= $s['label'] ?></span>
        </td>
        <td style="padding:var(--space-3) var(--space-4);">
          <a href="/quotes/<?= $q['id'] ?>" class="btn btn-ghost btn-sm" title="Voir">→</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
