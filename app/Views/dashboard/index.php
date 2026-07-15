<?php
$title    = 'Tableau de bord';
$pageTitle = 'Tableau de bord';
$activeNav = 'dashboard';

$greeting = (int)date('H') < 12 ? 'Bonjour' : ((int)date('H') < 18 ? 'Bon après-midi' : 'Bonsoir');

// Formatage euro
function fmt(€ (float $n): string {
    return number_format($n, 0, ',', '\u00a0') . '\u00a0€';
}

$statusLabels = [
    'draft'     => ['label' => 'Brouillon',  'class' => 'badge-draft'],
    'sent'      => ['label' => 'Envoyé',    'class' => 'badge-sent'],
    'viewed'    => ['label' => 'Vu',          'class' => 'badge-sent'],
    'accepted'  => ['label' => 'Accepté',   'class' => 'badge-accepted'],
    'refused'   => ['label' => 'Refusé',    'class' => 'badge-refused'],
    'expired'   => ['label' => 'Expiré',    'class' => 'badge-expired'],
    'converted' => ['label' => 'Converti',   'class' => 'badge-converted'],
];

$circumference = 2 * pi() * 58; // r=58
$offset = $circumference - ($conversionRate / 100) * $circumference;

ob_start();
?>

<div class="dashboard-header">
  <div>
    <h2 class="dashboard-greeting"><?= $greeting ?>, <?= htmlspecialchars($user['first_name'] ?? 'vous') ?> 👋</h2>
    <p class="dashboard-subtitle">Voici votre activité pour <?= date('Y') ?></p>
  </div>
  <a href="/quotes/new" class="btn btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Nouveau devis
  </a>
</div>

<!-- KPIs -->
<div class="kpi-grid">
  <div class="kpi-card">
    <div class="kpi-icon blue">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div class="kpi-value" data-count="<?= (int)$stats['total_quotes'] ?>"><?= (int)$stats['total_quotes'] ?></div>
    <div class="kpi-label">Devis au total</div>
    <div class="kpi-trend up">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
      <?= (int)$stats['accepted'] ?> acceptés
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon green">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    </div>
    <div class="kpi-value"><?= fmt€((float)($invoiceStats['revenue_paid'] ?? 0)) ?></div>
    <div class="kpi-label">CA encaissé <?= date('Y') ?></div>
    <div class="kpi-trend up">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
      Facturé cette année
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon amber">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <div class="kpi-value"><?= fmt€((float)($invoiceStats['revenue_pending'] ?? 0)) ?></div>
    <div class="kpi-label">En attente de paiement</div>
    <?php if (($invoiceStats['overdue_count'] ?? 0) > 0): ?>
    <div class="kpi-trend down">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 9 12 15 6 9"/></svg>
      <?= (int)$invoiceStats['overdue_count'] ?> en retard
    </div>
    <?php endif; ?>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon purple">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    </div>
    <div class="kpi-value"><?= $conversionRate ?>%</div>
    <div class="kpi-label">Taux de conversion</div>
    <div class="kpi-trend <?= $conversionRate >= 50 ? 'up' : 'down' ?>">
      <?= $stats['refused'] ?? 0 ?> refusés
    </div>
  </div>
</div>

<!-- Grille principale -->
<div class="dashboard-grid">

  <!-- Derniers devis -->
  <div class="card" style="padding: 0; overflow: hidden;">
    <div class="section-header" style="padding: var(--space-5) var(--space-6);">
      <h3 class="section-title">Derniers devis</h3>
      <a href="/quotes" class="btn btn-ghost btn-sm">Voir tout</a>
    </div>
    <div class="table-container" style="border: none; border-radius: 0;">
      <table>
        <thead>
          <tr>
            <th>Numéro</th>
            <th>Client</th>
            <th>Montant TTC</th>
            <th>Statut</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentQuotes)): ?>
          <tr>
            <td colspan="5" style="text-align:center; padding: var(--space-12); color: var(--color-text-muted);">
              <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" style="margin:0 auto var(--space-3); display:block; opacity:.4;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              Aucun devis pour l'instant.<br>
              <a href="/quotes/new" class="btn btn-primary btn-sm" style="margin-top:var(--space-3)">Créer le premier</a>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($recentQuotes as $q): ?>
          <tr style="cursor:pointer" onclick="window.location='/quotes/<?= $q['id'] ?>'">
            <td><span class="font-mono text-sm"><?= htmlspecialchars($q['number']) ?></span></td>
            <td><?= htmlspecialchars($q['client_name'] ?? '—') ?></td>
            <td style="font-variant-numeric:tabular-nums;"><?= fmt€((float)$q['total_ttc']) ?></td>
            <td>
              <?php $s = $statusLabels[$q['status']] ?? ['label'=>$q['status'],'class'=>'badge-draft']; ?>
              <span class="badge <?= $s['class'] ?>"><?= $s['label'] ?></span>
            </td>
            <td class="text-muted text-sm"><?= date('d/m/Y', strtotime($q['issue_date'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Taux de conversion -->
  <div class="card">
    <div class="card-header">
      <h3 class="section-title">Conversion</h3>
    </div>
    <div class="conversion-ring">
      <div class="ring-wrapper">
        <svg class="ring-svg" width="140" height="140" viewBox="0 0 140 140">
          <circle class="ring-track" cx="70" cy="70" r="58"/>
          <circle class="ring-fill" cx="70" cy="70" r="58"
            stroke-dasharray="<?= $circumference ?>"
            stroke-dashoffset="<?= $offset ?>"
            id="ringFill"/>
        </svg>
        <div class="ring-label">
          <div class="ring-percent" id="ringPercent">0%</div>
          <div class="ring-sublabel">taux de<br>conversion</div>
        </div>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-3); width:100%; margin-top:var(--space-4);">
        <div style="text-align:center; padding:var(--space-3); background:var(--color-surface-2); border-radius:var(--radius-lg);">
          <div style="font-size:var(--text-xl); font-weight:800; color:var(--color-success);"><?= (int)$stats['accepted'] ?></div>
          <div style="font-size:var(--text-xs); color:var(--color-text-muted);">Acceptés</div>
        </div>
        <div style="text-align:center; padding:var(--space-3); background:var(--color-surface-2); border-radius:var(--radius-lg);">
          <div style="font-size:var(--text-xl); font-weight:800; color:var(--color-danger);"><?= (int)$stats['refused'] ?></div>
          <div style="font-size:var(--text-xs); color:var(--color-text-muted);">Refusés</div>
        </div>
      </div>
    </div>
  </div>

</div>

<?php
$content = ob_get_clean();

$extraScripts = '
<script>
// Anime le ring de conversion
document.addEventListener("DOMContentLoaded", function() {
  const target = ' . $conversionRate . ';
  const el = document.getElementById("ringPercent");
  let start = 0;
  const step = () => {
    start = Math.min(start + 1, target);
    el.textContent = start + "%";
    if (start < target) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);

  // Anime les KPIs
  document.querySelectorAll("[data-count]").forEach(el => {
    const target = parseInt(el.dataset.count);
    let c = 0;
    const inc = Math.ceil(target / 30);
    const t = setInterval(() => {
      c = Math.min(c + inc, target);
      el.textContent = c;
      if (c >= target) clearInterval(t);
    }, 30);
  });
});
</script>
';

require __DIR__ . '/../layouts/app.php';
