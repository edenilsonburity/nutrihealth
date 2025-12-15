<?php include __DIR__ . '/../partials/header.php'; ?>

<?php
$errors = $errors ?? [];
$old    = $old ?? [];

function old_val(array $old, string $key, string $default = ''): string {
    return htmlspecialchars($old[$key] ?? $default);
}
?>

<h2 style="margin-top:8px;margin-bottom:4px;">Registrar consulta</h2>
<p style="margin:0 0 12px 0;color:var(--muted);font-size:0.95rem;">
  Preencha os dados da consulta para o paciente.
</p>

<div class="card" style="margin-top:8px;">
  <div style="margin-bottom:12px;font-size:0.9rem;color:var(--muted);">
    <strong>Paciente:</strong> <?= htmlspecialchars($patient->fullName) ?> ·
    <strong>Nutricionista:</strong> <?= htmlspecialchars($nutritionist->name) ?> ·
    <strong>Data/Hora agendada:</strong>
    <?= (new DateTime($appointment->startDatetime))->format('d/m/Y H:i') ?>
  </div>

  <?php if (!empty($errors)): ?>
    <div style="
        margin-bottom:16px;
        padding:10px 12px;
        border-radius:8px;
        background:rgba(248,113,113,0.12);
        border:1px solid rgba(239,68,68,0.7);
        color:#7f1d1d;
        font-size:0.9rem;">
      <strong>Verifique os seguintes pontos:</strong>
      <ul style="margin:8px 0 0 18px;padding:0;">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" style="display:flex;flex-direction:column;gap:20px;">
    <!-- Bloco antropométrico -->
    <section>
      <h3 style="margin:0 0 8px 0;font-size:1rem;">Dados antropométricos</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;align-items:end;">

    <!-- Peso -->
    <div>
      <label for="weight_kg" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
        Peso atual (kg)
      </label>
      <input
        type="text"
        id="weight_kg"
        name="weight_kg"
        value="<?= old_val($old, 'weight_kg') ?>"
        placeholder="Ex.: 72,5"
        style="width:100%;padding:10px 12px;border-radius:6px;
              border:1px solid var(--border);background:var(--surface);color:var(--fg);"
      >
    </div>

    <!-- Altura -->
    <div>
      <label for="height_m" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
        Altura (m)
      </label>
      <input
        type="text"
        id="height_m"
        name="height_m"
        value="<?= old_val($old, 'height_m') ?>"
        placeholder="Ex.: 1,75"
        style="width:100%;padding:10px 12px;border-radius:6px;
              border:1px solid var(--border);background:var(--surface);color:var(--fg);"
      >
    </div>

    <!-- IMC automático -->
    <div>
      <label style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
        IMC (automático)
      </label>
      <input
        type="text"
        id="bmi_display"
        readonly
        value="<?php
            if (!empty($old['weight_kg']) && !empty($old['height_m'])) {
                $w = (float)str_replace(',', '.', $old['weight_kg']);
                $h = (float)str_replace(',', '.', $old['height_m']);
                echo number_format($w / ($h*$h), 1, ',', '.');
            }
        ?>"
        style="width:100%;padding:10px 12px;border-radius:6px;
              border:1px solid var(--border);background:var(--surface-elev);
              color:var(--fg);opacity:0.8;"
      >
    </div>

        <div>
          <label for="activity_level" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
            Nível de atividade física
          </label>
          <select
            id="activity_level"
            name="activity_level"
            style="width:100%;padding:10px 12px;border-radius:6px;
                   border:1px solid var(--border);background:var(--surface);color:var(--fg);"
          >
            <option value="">Selecione...</option>
            <option value="SEDENTARIO" <?= old_val($old, 'activity_level') === 'SEDENTARIO' ? 'selected' : '' ?>>
              Sedentário
            </option>
            <option value="LEVE" <?= old_val($old, 'activity_level') === 'LEVE' ? 'selected' : '' ?>>
              Leve (1–3x/semana)
            </option>
            <option value="MODERADO" <?= old_val($old, 'activity_level') === 'MODERADO' ? 'selected' : '' ?>>
              Moderado (3–5x/semana)
            </option>
            <option value="INTENSO" <?= old_val($old, 'activity_level') === 'INTENSO' ? 'selected' : '' ?>>
              Intenso (5–7x/semana)
            </option>
            <option value="MUITO_INTENSO" <?= old_val($old, 'activity_level') === 'MUITO_INTENSO' ? 'selected' : '' ?>>
              Muito intenso (2x/dia)
            </option>
          </select>
        </div>
      </div>
    </section>

    <!-- Bloco clínico -->
    <section>
      <h3 style="margin:0 0 8px 0;font-size:1rem;">Informações clínicas</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
        <div>
          <label for="goal" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
            Objetivo da consulta
          </label>
          <textarea id="goal" name="goal" rows="2"
                    style="width:100%;padding:10px 12px;border-radius:6px;
                           border:1px solid var(--border);background:var(--surface);color:var(--fg);resize:vertical;"><?= old_val($old, 'goal') ?></textarea>
        </div>

        <div>
          <label for="dietary_restrictions" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
            Restrições alimentares / intolerâncias
          </label>
          <textarea id="dietary_restrictions" name="dietary_restrictions" rows="2"
                    style="width:100%;padding:10px 12px;border-radius:6px;
                           border:1px solid var(--border);background:var(--surface);color:var(--fg);resize:vertical;"><?= old_val($old, 'dietary_restrictions') ?></textarea>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:12px;">
        <div>
          <label for="diseases" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
            Doenças pré-existentes
          </label>
          <textarea id="diseases" name="diseases" rows="2"
                    style="width:100%;padding:10px 12px;border-radius:6px;
                           border:1px solid var(--border);background:var(--surface);color:var(--fg);resize:vertical;"><?= old_val($old, 'diseases') ?></textarea>
        </div>

        <div>
          <label for="medications" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
            Medicamentos em uso
          </label>
          <textarea id="medications" name="medications" rows="2"
                    style="width:100%;padding:10px 12px;border-radius:6px;
                           border:1px solid var(--border);background:var(--surface);color:var(--fg);resize:vertical;"><?= old_val($old, 'medications') ?></textarea>
        </div>
      </div>

      <div style="margin-top:12px;">
        <label for="notes" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
          Observações adicionais
        </label>
        <textarea id="notes" name="notes" rows="3"
                  style="width:100%;padding:10px 12px;border-radius:6px;
                         border:1px solid var(--border);background:var(--surface);color:var(--fg);resize:vertical;"><?= old_val($old, 'notes') ?></textarea>
      </div>
    </section>

    <!-- Medidas + imagem de referência -->
    <section>
      <h3 style="margin:0 0 8px 0;font-size:1rem;">Dobras cutâneas e circunferências</h3>

      <div style="display:flex;flex-wrap:wrap;gap:24px;">
        <div style="flex:1;min-width:260px;">
          <h4 style="margin:0 0 6px 0;font-size:0.95rem;">Dobras cutâneas (mm)</h4>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;">
            <?php
            $folds = [
              'triceps_mm'     => 'Tríceps',
              'subscapular_mm' => 'Subescapular',
              'suprailiac_mm'  => 'Suprailíaca',
              'abdominal_mm'   => 'Abdominal',
              'thigh_mm'       => 'Coxa',
              'calf_mm'        => 'Panturrilha',
            ];
            foreach ($folds as $field => $label):
            ?>
              <div>
                <label for="<?= $field ?>" style="display:block;font-size:0.85rem;color:var(--muted);margin-bottom:4px;">
                  <?= $label ?>
                </label>
                <input
                  type="text"
                  id="<?= $field ?>"
                  name="<?= $field ?>"
                  value="<?= old_val($old, $field) ?>"
                  style="width:100%;padding:8px 10px;border-radius:6px;
                         border:1px solid var(--border);background:var(--surface);color:var(--fg);font-size:0.9rem;"
                >
              </div>
            <?php endforeach; ?>
          </div>

          <h4 style="margin:14px 0 6px 0;font-size:0.95rem;">Circunferências (cm)</h4>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;">
            <?php
            $circs = [
              'waist_circ_cm' => 'Cintura',
              'hip_circ_cm'   => 'Quadril',
              'arm_circ_cm'   => 'Braço',
              'thigh_circ_cm' => 'Coxa',
              'calf_circ_cm'  => 'Panturrilha',
            ];
            foreach ($circs as $field => $label):
            ?>
              <div>
                <label for="<?= $field ?>" style="display:block;font-size:0.85rem;color:var(--muted);margin-bottom:4px;">
                  <?= $label ?>
                </label>
                <input
                  type="text"
                  id="<?= $field ?>"
                  name="<?= $field ?>"
                  value="<?= old_val($old, $field) ?>"
                  style="width:100%;padding:8px 10px;border-radius:6px;
                         border:1px solid var(--border);background:var(--surface);color:var(--fg);font-size:0.9rem;"
                >
              </div>
            <?php endforeach; ?>
          </div>

          <div style="margin-top:10px;">
            <label for="body_fat_percent" style="display:block;font-size:0.85rem;color:var(--muted);margin-bottom:4px;">
              % de gordura (estimado)
            </label>
            <input
              type="text"
              id="body_fat_percent"
              name="body_fat_percent"
              value="<?= old_val($old, 'body_fat_percent') ?>"
              placeholder="Ex.: 18,5"
              style="width:180px;padding:8px 10px;border-radius:6px;
                     border:1px solid var(--border);background:var(--surface);color:var(--fg);font-size:0.9rem;"
            >
          </div>
        </div>

        <!-- Imagem de referência -->
        <div style="flex:1;min-width:220px;text-align:center;">
          <img
            src="/nutrihealth/public/img/body_reference.png"
            alt="Referência para dobras e circunferências"
            style="max-width:260px;width:100%;height:auto;opacity:0.9;border-radius:12px;
                   background:var(--surface);border:1px solid var(--border);padding:8px;"
          >
          <p style="margin-top:8px;font-size:0.8rem;color:var(--muted);">
            Utilize a imagem como referência visual para localização das dobras cutâneas
            e circunferências (valores devem ser inseridos em milímetros e centímetros).
          </p>
        </div>
      </div>
    </section>

    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
      <a href="/nutrihealth/public/?controller=appointment&action=calendar"
         class="btn"
         style="padding:8px 14px;border-radius:999px;border:1px solid var(--border);
                background:var(--surface-elev);">
        Voltar à agenda
      </a>
      <button type="submit"
              class="btn"
              style="padding:8px 14px;border-radius:999px;border:none;
                     background:var(--primary);color:var(--on-primary);">
        Salvar consulta
      </button>
    </div>
  </form>
</div>

<script>
  function calcBMI() {
      const w = parseFloat(document.getElementById('weight_kg').value.replace(',', '.'));
      const h = parseFloat(document.getElementById('height_m').value.replace(',', '.'));

      if (!isNaN(w) && !isNaN(h) && h > 0) {
          const bmi = (w / (h * h)).toFixed(1);
          document.getElementById('bmi_display').value = bmi.replace('.', ',');
      } else {
          document.getElementById('bmi_display').value = '';
      }
  }

  document.getElementById('weight_kg').addEventListener('input', calcBMI);
  document.getElementById('height_m').addEventListener('input', calcBMI);
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
