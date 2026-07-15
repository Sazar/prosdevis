<?php
$statuses = [
    'draft'     => ['label' => 'Brouillon', 'class' => 'badge-neutral'],
    'sent'      => ['label' => 'Envoyée', 'class' => 'badge-info'],
    'partial'   => ['label' => 'Partielle', 'class' => 'badge-warning'],
    'paid'      => ['label' => 'Payée', 'class' => 'badge-success'],
    'cancelled' => ['label' => 'Annulée', 'class' => 'badge-danger'],
];
$currentStatus = $_GET['status'] ?? '';
$search = htmlspecialchars($_GET['q'] ?? '');
$overdue = !empty($_GET['overdue']);
?>
<div class="card" style="margin-bottom:var(--space-5);">
  <div class="card-header">
    <h2 style="font-size:var(--text-xl); font-weight:800;">Mes factures</h2>
  </div>
</div>

<div class="card" style="margin-bottom:var(--space-4);">
  <form method="GET" action="/invoices" style="display:flex; gap:var(--space-3); flex-wrap:wrap; align-items:center;">
    <input type="search" name="q" placeholder="Rechercher…" class="form-control" style="max-width:240px;" value="<?= $search ?>">
    <select name="status" class="form-control" style="max-width:180px;">
      <option value="">Tous les statuts</option>
      <?php foreach ($statuses as $key => $s): ?>
      <option value="<?= $key ?>" <?= $currentStatus === $key ? 'selected' : '' ?>><?= $s['label'] ?></option>
      <?php endforeach; ?>
    </select>
    <label style="display:flex; align-items:center; gap:8px; font-size:var(--text-sm); color:var(--color-text-muted);">
      <input type="checkbox" name="overdue" value="1" <?= $overdue ? 'checked' : '' ?>> Échéances dépassées
    </label>
    <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
    <?php if ($currentStatus || $search || $overdue): ?>
    <a href="/invoices" class="btn btn-ghost btn-sm">Réinitialiser</a>
    <?php endif; ?>
  </form>
</div>

<?php if (empty($invoices)): ?>
<div class="card">
  <div style="text-align:center; padding:var(--space-16) var(--space-8); color:var(--color-text-muted);">
    <h3 style="color:var(--color-text); margin-bottom:var(--space-2);">Aucune facture trouvée</h3>
    <p>Les factures créées à partir des devis acceptés apparaîtront ici.</p>
  </div>
</div>
<?php else: ?>
<div class="card" style="padding:0; overflow:hidden;">
  <table style="width:100%; border-collapse:collapse; font-size:var(--text-sm);">
    <thead>
      <tr style="background:var(--color-surface-2); border-bottom:1px solid var(--color-border);">
        <th style="padding:var(--space-3) var(--space-4); text-align:left;">Numéro</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left;">Client</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left;">Objet</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left;">Émission</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left;">Échéance</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:right;">TTC</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:right;">Solde dû</th>
        <th style="padding:var(--space-3) var(--space-4); text-align:left;">Statut</th>
        <th style="padding:var(--space-3) var(--space-4);"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($invoices as $inv):
        $status = $statuses[$inv['status']] ?? ['label' => $inv['status'], 'class' => 'badge-neutral'];
        $isOverdue = $inv['due_date'] < date('Y-m-d') && !in_array($inv['status'], ['paid','cancelled']);
      ?>
      <tr style="border-bottom:1px solid var(--color-border);">
        <td style="padding:var(--space-3) var(--space-4);"><a href="/invoices/<?= $inv['id'] ?>" style="font-weight:700; color:var(--color-primary); text-decoration:none;"><?= htmlspecialchars($inv['number']) ?></a></td>
        <td style="padding:var(--space-3) var(--space-4);"><?= htmlspecialchars($inv['client_name'] ?? '—') ?></td>
        <td style="padding:var(--space-3) var(--space-4); color:var(--color-text-muted);"><?= htmlspecialchars($inv['title']) ?></td>
        <td style="padding:var(--space-3) var(--space-4);"><?= date('d/m/Y', strtotime($inv['issue_date'])) ?></td>
        <td style="padding:var(--space-3) var(--space-4); <?= $isOverdue ? 'color:#dc2626;font-weight:700;' : '' ?>"><?= date('d/m/Y', strtotime($inv['due_date'])) ?></td>
        <td style="padding:var(--space-3) var(--space-4); text-align:right; font-weight:700;"><?= number_format($inv['total_ttc'], 2, ',', '&nbsp;') ?> €</td>
        <td style="padding:var(--space-3) var(--space-4); text-align:right; font-weight:700; <?= $inv['balance_due'] > 0 ? 'color:#dc2626;' : 'color:#16a34a;' ?>"><?= number_format($inv['balance_due'], 2, ',', '&nbsp;') ?> €</td>
        <td style="padding:var(--space-3) var(--space-4);"><span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
        <td style="padding:var(--space-3) var(--space-4);"><a href="/invoices/<?= $inv['id'] ?>" class="btn btn-ghost btn-sm">→</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
