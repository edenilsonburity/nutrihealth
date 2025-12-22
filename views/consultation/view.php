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

  <h3>Exames anexados</h3>
  <p style="margin:0;color:var(--muted);">Anexe arquivos de exames do paciente (PDF, imagens ou documentos). Os arquivos ficam armazenados no servidor.</p>

  <?php
    $msg = $_GET['msg'] ?? '';
    $msgText = '';
    if ($msg === 'upload_ok') {
        $msgText = 'Arquivo(s) enviado(s) com sucesso.';
    } elseif ($msg === 'upload_failed') {
        $msgText = 'Não foi possível enviar os arquivos. Verifique formato e tamanho.';
    } elseif ($msg === 'upload_empty') {
        $msgText = 'Selecione ao menos um arquivo para enviar.';
    } elseif ($msg === 'upload_dir_error') {
        $msgText = 'Falha ao preparar a pasta de armazenamento no servidor.';
    } elseif ($msg === 'delete_ok') {
        $msgText = 'Arquivo excluído com sucesso.';
    } elseif ($msg === 'delete_failed') {
        $msgText = 'Não foi possível excluir o arquivo.';
    }
  ?>

  <?php if ($msgText !== ''): ?>
    <div style="padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:var(--surface-elev);color:var(--fg);">
      <?= htmlspecialchars($msgText) ?>
    </div>
  <?php endif; ?>

  <form method="post"
        action="/nutrihealth/public/?controller=consultation&action=uploadExam&appointment_id=<?= (int)$appointment->id ?>&from=<?= urlencode($from ?? 'agenda') ?>"
        enctype="multipart/form-data"
        style="display:flex;flex-direction:column;gap:10px;max-width:680px;">

    <input type="file" name="exam_files[]" multiple
           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
           style="padding:12px;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--fg);">

    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <button type="submit" class="btn btn-primary">
        <i data-lucide="upload"></i> Enviar arquivos
      </button>
      <small style="color:var(--muted);align-self:center;">Tamanho máximo: 10 MB por arquivo.</small>
    </div>
  </form>

  <?php if (!empty($examFiles)): ?>
    <div style="display:flex;flex-direction:column;gap:8px;">
      <?php foreach ($examFiles as $f): ?>
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:var(--surface);">
          <div style="display:flex;flex-direction:column;gap:2px;min-width:0;">
            <strong style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:520px;">
              <?= htmlspecialchars($f['name']) ?>
            </strong>
            <span style="color:var(--muted);font-size:0.9rem;">
              <?= date('d/m/Y H:i', (int)$f['mtime']) ?> · <?= number_format(((int)$f['size']) / 1024, 1, ',', '.') ?> KB
            </span>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end">
            <a class="btn" href="/nutrihealth/public/?controller=consultation&action=downloadExam&appointment_id=<?= (int)$appointment->id ?>&file=<?= urlencode($f['name']) ?>">
              <i data-lucide="download"></i> Baixar
            </a>

            <button type="button" class="btn btn-danger"
                    onclick="confirmDeleteExam('<?= htmlspecialchars(addslashes($f['name'])) ?>')">
              <i data-lucide="trash-2"></i> Excluir
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="margin:0;color:var(--muted);">Nenhum exame anexado para esta consulta.</p>
  <?php endif; ?>

  <script>
    function confirmDeleteExam(fileName) {
      const url = `/nutrihealth/public/?controller=consultation&action=deleteExam&appointment_id=<?= (int)$appointment->id ?>&from=<?= urlencode($from ?? 'agenda') ?>&file=` + encodeURIComponent(fileName);

      if (window.Swal) {
        Swal.fire({
          title: 'Excluir arquivo?',
          text: 'Esta ação não poderá ser desfeita.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sim, excluir',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = url;
          }
        });
      } else {
        if (confirm('Deseja realmente excluir este arquivo?')) {
          window.location.href = url;
        }
      }
    }
  </script>

  <div style="margin-top:12px;">   
    <?php
    $from = $from ?? ($_GET['from'] ?? 'agenda');

    if ($from === 'list') {
        $backUrl  = '/nutrihealth/public/?controller=consultation&action=index';
        $backText = 'Voltar para Consultas';
    } else {
        $backUrl  = '/nutrihealth/public/?controller=appointment&action=index';
        $backText = 'Voltar para Agenda';
    }
    ?>

    <a href="<?= $backUrl ?>" class="btn">
      <i data-lucide="arrow-left"></i> <?= $backText ?>
    </a>

  </div>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
