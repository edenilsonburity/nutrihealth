<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px;margin-bottom:4px;">Consulta registrada</h2>
<p style="margin:0 0 12px 0;color:var(--muted);">Visualização dos dados da consulta.</p>

<div class="card" style="padding:20px;display:flex;flex-direction:column;gap:16px;">

  <div style="font-size:0.95rem;color:var(--muted);">
    <strong>Paciente:</strong> <?= htmlspecialchars($patient->fullName) ?><br>
    <strong>Nutricionista:</strong> <?= htmlspecialchars($nutritionist->name) ?><br>
    <strong>Data da consulta:</strong> <?= date('d/m/Y H:i', strtotime($consultation->consultationDate)) ?><br>
  </div>

  <h3>Dados Antropométricos</h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
    <div><strong>Peso:</strong> <?= htmlspecialchars($consultation->weightKg) ?> kg</div>
    <div><strong>Altura:</strong> <?= htmlspecialchars($consultation->heightM) ?> m</div>
    <div><strong>IMC:</strong> <?= number_format($consultation->bmi, 1, ',', '.') ?></div>
    <div><strong>Nível de atividade:</strong> <?= htmlspecialchars($consultation->activityLevel) ?></div>
  </div>

  <h3>Informações clínicas</h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
    <div><strong>Objetivo:</strong><br><?= nl2br(htmlspecialchars($consultation->goal)) ?></div>
    <div><strong>Restrições / Intolerâncias:</strong><br><?= nl2br(htmlspecialchars($consultation->dietaryRestrictions)) ?></div>
    <div><strong>Doenças pré-existentes:</strong><br><?= nl2br(htmlspecialchars($consultation->diseases)) ?></div>
    <div><strong>Medicamentos em uso:</strong><br><?= nl2br(htmlspecialchars($consultation->medications)) ?></div>
  </div>

  <?php if (!empty($measurements)): ?>
    <h3>Dobras cutâneas (mm)</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">
      <div><strong>Tríceps:</strong> <?= htmlspecialchars($measurements->tricepsMm) ?></div>
      <div><strong>Subescapular:</strong> <?= htmlspecialchars($measurements->subscapularMm) ?></div>
      <div><strong>Suprailíaca:</strong> <?= htmlspecialchars($measurements->suprailiacMm) ?></div>
      <div><strong>Abdominal:</strong> <?= htmlspecialchars($measurements->abdominalMm) ?></div>
      <div><strong>Coxa:</strong> <?= htmlspecialchars($measurements->thighMm) ?></div>
      <div><strong>Panturrilha:</strong> <?= htmlspecialchars($measurements->calfMm) ?></div>
    </div>

    <h3>Circunferências (cm)</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">
      <div><strong>Cintura:</strong> <?= htmlspecialchars($measurements->waistCircCm) ?></div>
      <div><strong>Quadril:</strong> <?= htmlspecialchars($measurements->hipCircCm) ?></div>
      <div><strong>Braço:</strong> <?= htmlspecialchars($measurements->armCircCm) ?></div>
      <div><strong>Coxa:</strong> <?= htmlspecialchars($measurements->thighCircCm) ?></div>
      <div><strong>Panturrilha:</strong> <?= htmlspecialchars($measurements->calfCircCm) ?></div>
    </div>

    <h3>% Gordura</h3>
    <p><?= htmlspecialchars($measurements->bodyFatPercent) ?> %</p>
  <?php endif; ?>

  <div style="margin-top:12px;">
    <a href="/nutrihealth/public/?controller=appointment&action=calendar"
       style="padding:8px 14px;border-radius:999px;border:1px solid var(--border);
              background:var(--surface-elev);text-decoration:none;">
      Voltar para Agenda
    </a>
  </div>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
