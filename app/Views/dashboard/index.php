<?php
$fmt     = fn(float $n) => number_format($n, 2, ',', '\u00a0') . '\u00a0\u20ac';
$fmtK    = function(float $n): string {
    if ($n >= 1000) return number_format($n / 1000, 1, ',', '') . 'k\u00a0\u20ac';
    return number_format($n, 0, ',', '\u00a0') . '\u00a0\u20ac';
};

// Labels mois pour graphique
$moisLabels  = array_map(fn($r) => date('M Y', strtotime($r['mois'] . '-01')), $caMensuel);
$caFacture   = array_map(fn($r) => round((float)$r['ca_facture'], 2), $caMensuel);
$caEncaisse  = array_map(fn($r) => round((float)$r['ca_encaisse'], 2), $caMensuel);

// Donut statuts devis
$donutLabels = [];
$donutData   = [];
$donutColors = ['draft'=>'#bab9b4','sent'=>'#006494','viewed'=>'#5591c7','accepted'=>'#437a22','refused'=>'#a12c7b','expired'=>'#d19900','converted'=>'#01696f'];
foreach ($statutsDevis as $s) {
    $donutLabels[] = match($s['status']) {
        'draft'=>'Brouillon','sent'=>'Envoy\u00e9','viewed'=>'Consult\u00e9','accepted'=>'Accept\u00e9',
        'refused'=>'Refus\u00e9','expired'=>'Expir\u00e9','converted'=>'Converti', default => $s['status']
    };
    $donutData[]   = (int) $s['total'];
}

// Labels activité
$actionLabels = [
    'quote.created'      => 'Devis cr\u00e9\u00e9',
    'quote.updated'      => 'Devis modifi\u00e9',
    'quote.sent'         => 'Devis envoy\u00e9',
    'quote.converted'    => 'Devis converti en facture',
    'invoice.paid'       => 'Paiement enregistr\u00e9',
    'invoice.reminder_sent' => 'Relance envoy\u00e9e',
    'user.login'         => 'Connexion',
];
?>

<style>
.dash-grid-kpi {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(200px,100%),1fr));
    gap: var(--space-4);
    margin-bottom: var(--space-6);
}
.kpi-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-5);
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    transition: box-shadow var(--transition-interactive);
}
.kpi-card:hover { box-shadow: var(--shadow-md); }
.kpi-label {
    font-size: var(--text-xs);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--color-text-muted);
}
.kpi-value {
    font-size: var(--text-2xl);
    font-weight: 900;
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
    color: var(--color-text);
}
.kpi-value.danger { color: #dc2626; }
.kpi-value.success { color: #16a34a; }
.kpi-sub {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
    margin-top: var(--space-1);
}
.dash-grid-charts {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
}
.dash-grid-bottom {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}
.chart-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-5);
}
.chart-card h3 {
    font-size: var(--text-sm);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--color-text-muted);
    margin-bottom: var(--space-4);
}
.activity-list { list-style: none; display: flex; flex-direction: column; gap: var(--space-3); }
.activity-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
    font-size: var(--text-sm);
}
.activity-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--color-primary);
    margin-top: 5px;
    flex-shrink: 0;
}
.activity-meta { color: var(--color-text-muted); font-size: var(--text-xs); }
.client-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-2) 0;
    border-bottom: 1px solid var(--color-border);
    font-size: var(--text-sm);
}
.client-row:last-child { border-bottom: none; }
.client-bar-wrap { flex: 1; margin: 0 var(--space-3); height: 6px; background: var(--color-surface-offset); border-radius: 999px; overflow: hidden; }
.client-bar-fill { height: 100%; background: var(--color-primary); border-radius: 999px; }
@media (max-width: 1024px) {
    .dash-grid-charts, .dash-grid-bottom { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .kpi-value { font-size: var(--text-xl); }
}
</style>

<!-- KPIs -->
<div class="dash-grid-kpi">
    <div class="kpi-card">
        <span class="kpi-label">CA factur\u00e9 ce mois</span>
        <span class="kpi-value"><?= $fmtK((float)$kpis['caMois']) ?></span>
        <span class="kpi-sub">Factures \u00e9mises en <?= date('F Y') ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label">CA encaiss\u00e9 ce mois</span>
        <span class="kpi-value success"><?= $fmtK((float)$kpis['caEncaisse']) ?></span>
        <span class="kpi-sub">Paiements re\u00e7us ce mois</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label">Solde total d\u00fb</span>
        <span class="kpi-value <?= $kpis['soldeDu'] > 0 ? 'danger' : 'success' ?>"><?= $fmtK((float)$kpis['soldeDu']) ?></span>
        <span class="kpi-sub">Toutes factures ouvertes</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label">Devis en attente</span>
        <span class="kpi-value"><?= $kpis['devisEnAttente'] ?></span>
        <span class="kpi-sub">Brouillons, envoy\u00e9s, consult\u00e9s</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label">Factures en retard</span>
        <span class="kpi-value <?= $kpis['facturesEnRetard'] > 0 ? 'danger' : 'success' ?>"><?= $kpis['facturesEnRetard'] ?></span>
        <span class="kpi-sub">\u00c9ch\u00e9ance d\u00e9pass\u00e9e</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label">Taux de conversion</span>
        <span class="kpi-value"><?= $kpis['tauxConversion'] ?>%</span>
        <span class="kpi-sub"><?= $kpis['convertis30'] ?>/<?= $kpis['totalDevis30'] ?> devis sur 30 jours</span>
    </div>
</div>

<!-- Graphiques -->
<div class="dash-grid-charts">
    <div class="chart-card">
        <h3>CA mensuel (6 mois)</h3>
        <canvas id="chartCA" height="120"></canvas>
    </div>
    <div class="chart-card">
        <h3>Statuts des devis</h3>
        <canvas id="chartDevis" height="200"></canvas>
    </div>
</div>

<!-- Bas de page -->
<div class="dash-grid-bottom">
    <div class="chart-card">
        <h3>Activit\u00e9 r\u00e9cente</h3>
        <?php if (empty($activite)): ?>
        <p style="color:var(--color-text-muted); font-size:var(--text-sm);">Aucune activit\u00e9 pour l'instant.</p>
        <?php else: ?>
        <ul class="activity-list">
            <?php foreach ($activite as $act): ?>
            <li class="activity-item">
                <span class="activity-dot"></span>
                <div>
                    <div><?= htmlspecialchars($actionLabels[$act['action']] ?? $act['action']) ?>
                        <?php if ($act['entity_type'] === 'invoice'): ?><a href="/invoices/<?= $act['entity_id'] ?>" style="color:var(--color-primary);"> #<?= $act['entity_id'] ?></a>
                        <?php elseif ($act['entity_type'] === 'quote'): ?><a href="/quotes/<?= $act['entity_id'] ?>" style="color:var(--color-primary);"> #<?= $act['entity_id'] ?></a><?php endif; ?>
                    </div>
                    <div class="activity-meta">
                        <?= htmlspecialchars($act['user_name'] ?? 'Syst\u00e8me') ?>
                        &mdash; <?= date('d/m/Y H:i', strtotime($act['created_at'])) ?>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <div class="chart-card">
        <h3>Top clients</h3>
        <?php if (empty($topClients)): ?>
        <p style="color:var(--color-text-muted); font-size:var(--text-sm);">Aucune donn\u00e9e client disponible.</p>
        <?php else:
            $maxCa = max(array_column($topClients, 'ca_total')) ?: 1;
        ?>
        <?php foreach ($topClients as $client): ?>
        <div class="client-row">
            <span style="font-weight:600; min-width:120px;"><?= htmlspecialchars($client['name']) ?></span>
            <div class="client-bar-wrap">
                <div class="client-bar-fill" style="width:<?= round($client['ca_total']/$maxCa*100) ?>%;"></div>
            </div>
            <span style="font-weight:700; font-variant-numeric:tabular-nums; white-space:nowrap;"><?= $fmtK((float)$client['ca_total']) ?></span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function(){
    const isDark = () => document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor  = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor  = () => isDark() ? '#797876' : '#7a7974';

    // CA mensuel
    const ctxCA = document.getElementById('chartCA').getContext('2d');
    const caChart = new Chart(ctxCA, {
        type: 'bar',
        data: {
            labels: <?= json_encode($moisLabels) ?>,
            datasets: [
                {
                    label: 'CA factur\u00e9',
                    data: <?= json_encode($caFacture) ?>,
                    backgroundColor: 'rgba(1,105,111,0.75)',
                    borderRadius: 6,
                    order: 2,
                },
                {
                    label: 'CA encaiss\u00e9',
                    data: <?= json_encode($caEncaisse) ?>,
                    type: 'line',
                    borderColor: '#437a22',
                    backgroundColor: 'rgba(67,122,34,0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: textColor(), font: { size: 12 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + '\u00a0: ' + new Intl.NumberFormat('fr-FR',{style:'currency',currency:'EUR'}).format(ctx.parsed.y)
                    }
                }
            },
            scales: {
                x: { ticks: { color: textColor() }, grid: { color: gridColor() } },
                y: {
                    ticks: {
                        color: textColor(),
                        callback: v => new Intl.NumberFormat('fr-FR',{notation:'compact',currency:'EUR',style:'currency'}).format(v)
                    },
                    grid: { color: gridColor() }
                }
            }
        }
    });

    // Donut statuts devis
    const ctxD = document.getElementById('chartDevis').getContext('2d');
    const donutColors = <?= json_encode(array_values(array_intersect_key($donutColors, array_flip(array_column($statutsDevis,'status'))))) ?>;
    new Chart(ctxD, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($donutLabels) ?>,
            datasets: [{ data: <?= json_encode($donutData) ?>, backgroundColor: <?= json_encode(array_map(fn($s) => $donutColors[$s['status']] ?? '#bab9b4', $statutsDevis)) ?>, borderWidth: 0, hoverOffset: 6 }]
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor(), font: { size: 11 }, padding: 12 } }
            }
        }
    });

    // Mise \u00e0 jour couleurs au changement de th\u00e8me
    const toggle = document.querySelector('[data-theme-toggle]');
    if (toggle) toggle.addEventListener('click', () => {
        setTimeout(() => {
            [caChart].forEach(c => {
                c.options.plugins.legend.labels.color = textColor();
                c.options.scales.x.ticks.color = textColor();
                c.options.scales.x.grid.color = gridColor();
                c.options.scales.y.ticks.color = textColor();
                c.options.scales.y.grid.color = gridColor();
                c.update();
            });
        }, 50);
    });
})();
</script>
