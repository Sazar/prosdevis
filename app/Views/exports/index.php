<?php $csrf = App\Helpers\Session::csrf(); ?>
<style>
.exp-shell { max-width: 860px; margin: 0 auto; padding: 2rem 1rem 4rem; }
.exp-title { font-size: 1.6rem; font-weight: 900; color: #0f172a; margin-bottom: 1.5rem; }
.exp-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.exp-card  { background:#fff; border:1px solid #e5e7eb; border-radius: 22px; padding: 1.5rem; box-shadow: 0 20px 45px rgba(15,23,42,.06); }
.exp-head  { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; }
.exp-icon  { width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; }
.exp-icon-csv { background:#dcfce7; color:#166534; }
.exp-icon-fec { background:#dbeafe; color:#1d4ed8; }
.exp-label { font-size:1.1rem; font-weight:900; color:#0f172a; }
.exp-badge { display:inline-flex; margin-top:.25rem; padding:.25rem .55rem; border-radius:999px; font-size:.7rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
.exp-badge-csv { background:#d1fae5; color:#065f46; }
.exp-badge-fec { background:#dbeafe; color:#1e40af; }
.exp-desc  { color:#475569; line-height:1.7; margin-bottom:1.1rem; font-size:.92rem; }
.exp-form  { display:grid; gap:.75rem; }
.exp-row   { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }
.exp-group label { display:block; font-size:.8rem; font-weight:800; color:#334155; margin-bottom:.35rem; }
.exp-input { width:100%; border:1px solid #d6dbe3; border-radius:12px; padding:.75rem .9rem; font:inherit; }
.exp-btn   { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; border:none; padding:.85rem 1rem; border-radius:14px; font-weight:800; cursor:pointer; width:100%; }
.exp-btn-csv { background:#01696f; color:#fff; }
.exp-btn-fec { background:#1d4ed8; color:#fff; }
.exp-note  { font-size:.78rem; color:#94a3b8; margin-top:.4rem; line-height:1.6; }
@media (max-width: 760px){ .exp-grid{grid-template-columns:1fr;} }
</style>

<div class="exp-shell">
  <div class="exp-title">Export comptable</div>
  <div class="exp-grid">

    <!-- CSV -->
    <div class="exp-card">
      <div class="exp-head">
        <div class="exp-icon exp-icon-csv">
          <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <div>
          <div class="exp-label">Export CSV</div>
          <span class="exp-badge exp-badge-csv">Excel / LibreOffice</span>
        </div>
      </div>
      <p class="exp-desc">Tableau complet de vos factures sur la période sélectionnée : numéro, dates, client, montants HT/TVA/TTC, encaissement, solde dû. Compatible Excel, LibreOffice et tout logiciel de comptabilité acceptant le CSV.</p>
      <form method="POST" action="/exports/csv" class="exp-form">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="exp-row">
          <div class="exp-group"><label>Du</label><input type="date" name="from" class="exp-input" value="<?= date('Y-01-01') ?>"></div>
          <div class="exp-group"><label>Au</label><input type="date" name="to" class="exp-input" value="<?= date('Y-12-31') ?>"></div>
        </div>
        <button type="submit" class="exp-btn exp-btn-csv">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Télécharger CSV
        </button>
      </form>
    </div>

    <!-- FEC -->
    <div class="exp-card">
      <div class="exp-head">
        <div class="exp-icon exp-icon-fec">
          <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </div>
        <div>
          <div class="exp-label">Export FEC</div>
          <span class="exp-badge exp-badge-fec">Contrôle fiscal DGFiP</span>
        </div>
      </div>
      <p class="exp-desc">Fichier des Écritures Comptables au format <strong>NF Z55-200</strong> (18 colonnes, délimiteur pipe), conforme aux exigences de la DGFiP en cas de contrôle fiscal. Nommage automatique <code>SIRETFECannée.txt</code> selon la convention officielle.</p>
      <form method="POST" action="/exports/fec" class="exp-form">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="exp-row">
          <div class="exp-group"><label>Du</label><input type="date" name="from" class="exp-input" value="<?= date('Y-01-01') ?>"></div>
          <div class="exp-group"><label>Au</label><input type="date" name="to" class="exp-input" value="<?= date('Y-12-31') ?>"></div>
        </div>
        <button type="submit" class="exp-btn exp-btn-fec">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Télécharger FEC
        </button>
      </form>
      <p class="exp-note">⚠ Ce fichier est à remettre à votre expert-comptable ou à l'administration fiscale. Il est généré sur la base des factures non annulées.</p>
    </div>

  </div>
</div>
