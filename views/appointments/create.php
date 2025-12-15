<?php include __DIR__ . '/../partials/header.php'; ?>

<?php
// Valores antigos em caso de erro de validação
$old = $old ?? [
    'patient_id'      => '',
    'nutritionist_id' => '',
    'date'            => '',
    'time'            => '',
    'type'            => 'PRIMEIRA_CONSULTA',
    'status'          => 'PENDENTE',
    'notes'           => '',
];

$errors = $errors ?? [];
?>

<h2 style="margin-top:8px;margin-bottom:4px;">Novo agendamento</h2>
<p style="margin:0 0 12px 0;color:var(--muted);font-size:0.95rem;">
  Cadastre um novo horário de consulta para um paciente.
</p>

<div class="card" style="margin-top:8px;max-width:900px;">
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

  <form method="post" style="display:flex;flex-direction:column;gap:16px;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;">
      <!-- Paciente -->
      <div>
        <label for="patient_id" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
          Paciente
        </label>
        <select
          id="patient_id"
          name="patient_id"
          required
          style="width:100%;padding:10px 12px;border-radius:6px;
                 border:1px solid var(--border);background:var(--surface);color:var(--fg);"
        >
          <option value="">Selecione...</option>
          <?php foreach ($patients as $p): ?>
            <option
              value="<?= (int)$p->id ?>"
              <?= (string)$old['patient_id'] === (string)$p->id ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($p->fullName) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Nutricionista -->
      <div>
        <label for="nutritionist_id" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
          Nutricionista
        </label>
        <select
          id="nutritionist_id"
          name="nutritionist_id"
          required
          style="width:100%;padding:10px 12px;border-radius:6px;
                 border:1px solid var(--border);background:var(--surface);color:var(--fg);"
        >
          <option value="">Selecione...</option>
          <?php foreach ($nutritionists as $n): ?>
            <option
              value="<?= (int)$n->id ?>"
              <?= (string)$old['nutritionist_id'] === (string)$n->id ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($n->name) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Data -->
      <div>
        <label for="date" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
          Data da consulta
        </label>
        <input
          type="date"
          id="date"
          name="date"
          required
          value="<?= htmlspecialchars($old['date']) ?>"
          style="width:100%;padding:10px 12px;border-radius:6px;
                 border:1px solid var(--border);background:var(--surface);color:var(--fg);"
        >
      </div>

      <!-- Hora -->
      <div>
        <label for="time" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
          Horário
        </label>
        <input
          type="time"
          id="time"
          name="time"
          required
          value="<?= htmlspecialchars($old['time']) ?>"
          min="08:00"
          max="18:00"
          step="1800"
          style="width:100%;padding:10px 12px;border-radius:6px;
                 border:1px solid var(--border);background:var(--surface);color:var(--fg);"
        >
        <small style="font-size:0.8rem;color:var(--muted);">
          Horário comercial entre 08:00 e 18:00.
        </small>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;">
      <!-- Tipo da consulta -->
      <div>
        <label for="type" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
          Tipo da consulta
        </label>
        <select
          id="type"
          name="type"
          style="width:100%;padding:10px 12px;border-radius:6px;
                 border:1px solid var(--border);background:var(--surface);color:var(--fg);"
        >
          <option value="PRIMEIRA_CONSULTA"
            <?= $old['type'] === 'PRIMEIRA_CONSULTA' ? 'selected' : '' ?>>
            Primeira Consulta
          </option>
          <option value="RETORNO"
            <?= $old['type'] === 'RETORNO' ? 'selected' : '' ?>>
            Retorno
          </option>
          <option value="AVALIACAO_CORPORAL"
            <?= $old['type'] === 'AVALIACAO_CORPORAL' ? 'selected' : '' ?>>
            Avaliação Corporal
          </option>
          <option value="ORIENTACAO_NUTRICIONAL"
            <?= $old['type'] === 'ORIENTACAO_NUTRICIONAL' ? 'selected' : '' ?>>
            Orientação Nutricional
          </option>
        </select>
      </div>

      <!-- Status -->
      <div>
        <label for="status" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
          Status do agendamento
        </label>
        <select
          id="status"
          name="status"
          style="width:100%;padding:10px 12px;border-radius:6px;
                 border:1px solid var(--border);background:var(--surface);color:var(--fg);"
        >
          <option value="PENDENTE"
            <?= $old['status'] === 'PENDENTE' ? 'selected' : '' ?>>
            Pendente
          </option>
          <option value="CONFIRMADO"
            <?= $old['status'] === 'CONFIRMADO' ? 'selected' : '' ?>>
            Confirmado
          </option>
          <option value="CONCLUIDO"
            <?= $old['status'] === 'CONCLUIDO' ? 'selected' : '' ?>>
            Concluído
          </option>
          <option value="CANCELADO"
            <?= $old['status'] === 'CANCELADO' ? 'selected' : '' ?>>
            Cancelado
          </option>
        </select>
      </div>
    </div>

    <!-- Observações -->
    <div>
      <label for="notes" style="display:block;font-size:0.9rem;color:var(--muted);margin-bottom:4px;">
        Observações
      </label>
      <textarea
        id="notes"
        name="notes"
        rows="3"
        style="width:100%;padding:10px 12px;border-radius:6px;
               border:1px solid var(--border);background:var(--surface);color:var(--fg);resize:vertical;"
      ><?= htmlspecialchars($old['notes']) ?></textarea>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
      <a href="/nutrihealth/public/?controller=appointment&action=calendar"
         class="btn"
         style="padding:8px 14px;border-radius:999px;border:1px solid var(--border);
                background:var(--surface-elev);">
        Cancelar
      </a>
      <button type="submit"
              class="btn"
              style="padding:8px 14px;border-radius:999px;border:none;
                     background:var(--primary);color:var(--on-primary);">
        Salvar agendamento
      </button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
