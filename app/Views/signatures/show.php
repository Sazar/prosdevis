<?php
$fmt = fn(float $n) => number_format($n, 2, ',', '\u00a0') . '\u00a0€';
$csrf = App\Helpers\Session::csrf();
$signed = !empty($_GET['signed']);
$declined = !empty($_GET['declined']);
$alreadySigned = ($quote['signature_status'] ?? 'none') === 'signed';
$alreadyDeclined = ($quote['signature_status'] ?? 'none') === 'declined';
?>
<style>
.sign-shell { max-width: 1180px; margin: 0 auto; padding: 2rem 1rem 4rem; }
.sign-grid { display:grid; grid-template-columns: minmax(0,1.35fr) 420px; gap:1.5rem; align-items:start; }
.sign-card { background:#fff; border:1px solid #e5e7eb; border-radius:20px; box-shadow:0 20px 50px rgba(0,0,0,.06); overflow:hidden; }
.sign-header { padding:1.25rem 1.5rem; border-bottom:1px solid #eef0f3; display:flex; justify-content:space-between; gap:1rem; align-items:center; }
.sign-body { padding:1.5rem; }
.sign-badge { display:inline-flex; align-items:center; gap:.45rem; padding:.45rem .75rem; border-radius:999px; font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
.badge-pending { background:#fff7ed; color:#c2410c; }
.badge-signed { background:#ecfdf5; color:#047857; }
.badge-declined { background:#fef2f2; color:#b91c1c; }
.quote-meta { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-top:1rem; }
.quote-meta .box { background:#f8fafc; border:1px solid #e5e7eb; border-radius:16px; padding:1rem; }
.quote-meta .k { display:block; font-size:.75rem; color:#64748b; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.35rem; }
.quote-meta .v { font-size:1.05rem; font-weight:800; color:#0f172a; }
.line-table { width:100%; border-collapse:collapse; margin-top:1.25rem; font-size:.92rem; }
.line-table th { background:#01696f; color:#fff; text-align:left; padding:.8rem; font-size:.76rem; letter-spacing:.05em; text-transform:uppercase; }
.line-table td { padding:.9rem .8rem; border-bottom:1px solid #edf1f5; vertical-align:top; }
.line-table td.num, .line-table th.num { text-align:right; white-space:nowrap; }
.summary { margin-top:1.25rem; margin-left:auto; width:min(100%,360px); }
.summary-row { display:flex; justify-content:space-between; gap:1rem; padding:.55rem 0; border-bottom:1px solid #eef2f5; }
.summary-row.total { font-size:1.15rem; font-weight:900; color:#01696f; border-top:2px solid #01696f; border-bottom:none; padding-top:.8rem; }
.side-sticky { position:sticky; top:1rem; }
.signature-preview { border:1px dashed #cbd5e1; border-radius:14px; min-height:100px; display:flex; align-items:center; justify-content:center; background:#f8fafc; overflow:hidden; }
.signature-preview img { max-width:100%; height:auto; display:block; }
.canvas-wrap { border:1px dashed #94a3b8; border-radius:14px; background:#fff; overflow:hidden; }
#signature-pad { width:100%; height:200px; display:block; background:#fff; }
.form-grid { display:grid; gap:1rem; }
.form-group label { display:block; font-size:.85rem; font-weight:700; margin-bottom:.45rem; color:#334155; }
.form-control, textarea { width:100%; border:1px solid #d6dbe3; border-radius:12px; padding:.8rem .9rem; font:inherit; }
.btn-row { display:flex; gap:.75rem; flex-wrap:wrap; margin-top:1rem; }
.btn-primary, .btn-secondary, .btn-danger { display:inline-flex; justify-content:center; align-items:center; min-height:46px; padding:.8rem 1rem; border-radius:12px; font-weight:700; text-decoration:none; border:none; cursor:pointer; }
.btn-primary { background:#01696f; color:#fff; }
.btn-secondary { background:#e2e8f0; color:#0f172a; }
.btn-danger { background:#b91c1c; color:#fff; }
.alert { border-radius:14px; padding:1rem 1.1rem; margin-bottom:1rem; font-weight:600; }
.alert-success { background:#ecfdf5; color:#065f46; }
.alert-danger { background:#fef2f2; color:#991b1b; }
@media (max-width: 1024px){ .sign-grid{grid-template-columns:1fr;} .side-sticky{position:static;} .quote-meta{grid-template-columns:1fr;} }
</style>

<div class="sign-shell">
  <?php if ($signed): ?><div class="alert alert-success">Merci, le devis a bien été signé électroniquement.</div><?php endif; ?>
  <?php if ($declined): ?><div class="alert alert-danger">Le refus du devis a bien été enregistré.</div><?php endif; ?>

  <div class="sign-grid">
    <div class="sign-card">
      <div class="sign-header">
        <div>
          <div style="font-size:1.6rem;font-weight:900;color:#0f172a;">Signature du devis <?= htmlspecialchars($quote['number']) ?></div>
          <div style="margin-top:.3rem;color:#64748b;"><?= htmlspecialchars($quote['company_name']) ?> &mdash; <?= htmlspecialchars($quote['client_name']) ?></div>
        </div>
        <?php if ($alreadySigned): ?>
          <span class="sign-badge badge-signed">Signé</span>
        <?php elseif ($alreadyDeclined): ?>
          <span class="sign-badge badge-declined">Refusé</span>
        <?php else: ?>
          <span class="sign-badge badge-pending">En attente</span>
        <?php endif; ?>
      </div>
      <div class="sign-body">
        <div class="quote-meta">
          <div class="box"><span class="k">Date d’émission</span><span class="v"><?= date('d/m/Y', strtotime($quote['issue_date'])) ?></span></div>
          <div class="box"><span class="k">Validité</span><span class="v"><?= date('d/m/Y', strtotime($quote['validity_date'])) ?></span></div>
          <div class="box"><span class="k">Total TTC</span><span class="v"><?= $fmt((float)$quote['total_ttc']) ?></span></div>
        </div>

        <?php if (!empty($quote['title'])): ?>
        <div style="margin-top:1.2rem; font-size:1.05rem; font-weight:800; color:#0f172a;">Objet : <?= htmlspecialchars($quote['title']) ?></div>
        <?php endif; ?>
        <?php if (!empty($quote['description'])): ?>
        <p style="margin-top:.65rem; color:#475569; line-height:1.7;"><?= nl2br(htmlspecialchars($quote['description'])) ?></p>
        <?php endif; ?>

        <table class="line-table">
          <thead>
            <tr>
              <th>Désignation</th>
              <th class="num">Qté</th>
              <th class="num">PU HT</th>
              <th class="num">TVA</th>
              <th class="num">Total HT</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($quote['lines'] as $line): ?>
            <tr>
              <td><strong><?= htmlspecialchars($line['name']) ?></strong><?php if (!empty($line['description'])): ?><br><span style="font-size:.82rem;color:#64748b;"><?= nl2br(htmlspecialchars($line['description'])) ?></span><?php endif; ?></td>
              <td class="num"><?= number_format((float)$line['quantity'], 2, ',', '') ?></td>
              <td class="num"><?= $fmt((float)$line['unit_price']) ?></td>
              <td class="num"><?= number_format((float)$line['vat_rate'], 1, ',', '') ?>%</td>
              <td class="num"><strong><?= $fmt((float)$line['total_ht']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="summary">
          <div class="summary-row"><span>Sous-total HT</span><strong><?= $fmt((float)$quote['subtotal_ht']) ?></strong></div>
          <?php if ((float)$quote['total_discount'] > 0): ?><div class="summary-row" style="color:#b91c1c;"><span>Remise</span><strong>- <?= $fmt((float)$quote['total_discount']) ?></strong></div><?php endif; ?>
          <div class="summary-row"><span>TVA</span><strong><?= $fmt((float)$quote['total_vat']) ?></strong></div>
          <div class="summary-row total"><span>Total TTC</span><strong><?= $fmt((float)$quote['total_ttc']) ?></strong></div>
        </div>
      </div>
    </div>

    <div class="side-sticky">
      <div class="sign-card">
        <div class="sign-header">
          <div style="font-size:1rem;font-weight:800;color:#0f172a;">Validation électronique</div>
        </div>
        <div class="sign-body">
          <?php if ($alreadySigned && !empty($quote['signature_data'])):
            $data = json_decode($quote['signature_data'], true) ?: [];
          ?>
            <div class="alert alert-success" style="margin:0 0 1rem;">Ce devis a déjà été signé.</div>
            <div class="form-grid">
              <div><strong>Signataire</strong><br><?= htmlspecialchars($data['signer_name'] ?? '—') ?></div>
              <div><strong>Email</strong><br><?= htmlspecialchars($data['signer_email'] ?? '—') ?></div>
              <div><strong>Date</strong><br><?= !empty($quote['signature_at']) ? date('d/m/Y H:i', strtotime($quote['signature_at'])) : '—' ?></div>
            </div>
            <?php if (!empty($data['signature_data'])): ?>
            <div style="margin-top:1rem;">
              <div style="font-size:.85rem;font-weight:700;margin-bottom:.45rem;">Signature</div>
              <div class="signature-preview"><img src="<?= htmlspecialchars($data['signature_data']) ?>" alt="Signature électronique"></div>
            </div>
            <?php endif; ?>
          <?php elseif ($alreadyDeclined): ?>
            <div class="alert alert-danger" style="margin:0;">Ce devis a été refusé.</div>
          <?php else: ?>
          <form method="POST" action="/sign/<?= htmlspecialchars($quote['signature_token']) ?>" id="signature-form" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="signature_data" id="signature_data">
            <div class="form-group">
              <label for="signer_name">Nom du signataire</label>
              <input id="signer_name" name="signer_name" type="text" class="form-control" required>
            </div>
            <div class="form-group">
              <label for="signer_email">Email</label>
              <input id="signer_email" name="signer_email" type="email" class="form-control" value="<?= htmlspecialchars($quote['client_email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Signature manuscrite</label>
              <div class="canvas-wrap">
                <canvas id="signature-pad"></canvas>
              </div>
              <div class="btn-row" style="margin-top:.75rem;">
                <button type="button" id="clear-signature" class="btn-secondary">Effacer</button>
              </div>
            </div>
            <label style="display:flex; gap:.7rem; align-items:flex-start; font-size:.9rem; color:#334155;">
              <input type="checkbox" name="consent" value="1" required style="margin-top:.25rem;">
              Je confirme accepter ce devis et reconnaître la valeur de ma signature électronique.
            </label>
            <button type="submit" class="btn-primary">Signer le devis</button>
          </form>

          <form method="POST" action="/sign/<?= htmlspecialchars($quote['signature_token']) ?>/decline" style="margin-top:1rem; padding-top:1rem; border-top:1px solid #e5e7eb;">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="form-group">
              <label for="reason">Motif de refus</label>
              <textarea id="reason" name="reason" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn-danger">Refuser ce devis</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (!$alreadySigned && !$alreadyDeclined): ?>
<script>
(function(){
  const canvas = document.getElementById('signature-pad');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const hidden = document.getElementById('signature_data');
  const clearBtn = document.getElementById('clear-signature');
  let drawing = false;
  let hasDrawn = false;

  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * ratio;
    canvas.height = 200 * ratio;
    canvas.style.height = '200px';
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(ratio, ratio);
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#0f172a';
  }
  resizeCanvas();
  window.addEventListener('resize', resizeCanvas);

  function point(e) {
    const rect = canvas.getBoundingClientRect();
    const touch = e.touches ? e.touches[0] : e;
    return { x: touch.clientX - rect.left, y: touch.clientY - rect.top };
  }

  function start(e) {
    drawing = true;
    hasDrawn = true;
    const p = point(e);
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
    e.preventDefault();
  }
  function move(e) {
    if (!drawing) return;
    const p = point(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    e.preventDefault();
  }
  function end() {
    if (!drawing) return;
    drawing = false;
    hidden.value = canvas.toDataURL('image/png');
  }

  canvas.addEventListener('mousedown', start);
  canvas.addEventListener('mousemove', move);
  window.addEventListener('mouseup', end);
  canvas.addEventListener('touchstart', start, { passive:false });
  canvas.addEventListener('touchmove', move, { passive:false });
  canvas.addEventListener('touchend', end);

  clearBtn.addEventListener('click', function(){
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hidden.value = '';
    hasDrawn = false;
  });

  document.getElementById('signature-form').addEventListener('submit', function(e){
    if (!hasDrawn || !hidden.value) {
      alert('Merci de dessiner votre signature avant validation.');
      e.preventDefault();
    }
  });
})();
</script>
<?php endif; ?>
