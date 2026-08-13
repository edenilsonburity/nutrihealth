<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Editar Contrato</h2>

<?php if (!empty($errors)): ?>
  <div style="padding:12px 14px;border:1px solid #d32f2f;border-radius:10px;background:rgba(211,47,47,0.08);color:#d32f2f;margin-bottom:16px;">
    <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
$old = $old ?? [
    'patient_id'        => $contract->patientId,
    'service_type_id'   => $contract->serviceTypeId,
    'total_value'        => number_format($contract->totalValue, 2, ',', ''),
    'installments'       => $contract->installments,
    'payment_condition'  => $contract->paymentCondition,
    'start_date'         => $contract->startDate,
    'notes'              => $contract->notes,
];
?>

<form method="post"
      action="<?= BASE_URL ?>/?controller=contract&action=edit&id=<?= (int)$contract->id ?>"
      style="max-width:640px;padding:20px;margin-top:10px;
             background:var(--surface);border:1px solid var(--border);border-radius:12px">

  <label style="display:block;margin-bottom:16px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Paciente</span>
    <select name="patient_id" required
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
      <?php foreach (($patients ?? []) as $p): ?>
        <option value="<?= (int)$p->id ?>" <?= ((int)$old['patient_id'] === (int)$p->id) ? 'selected' : '' ?>>
          <?= htmlspecialchars($p->fullName) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label style="display:block;margin-bottom:16px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Tipo de serviço / pacote</span>
    <select name="service_type_id" required
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
      <?php foreach (($serviceTypes ?? []) as $s): ?>
        <option value="<?= (int)$s->id ?>" <?= ((int)$old['service_type_id'] === (int)$s->id) ? 'selected' : '' ?>>
          <?= htmlspecialchars($s->name) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px;">
    <label style="display:block;">
      <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Valor total (R$)</span>
      <input type="text" name="total_value" required
        value="<?= htmlspecialchars((string)$old['total_value'], ENT_QUOTES, 'UTF-8') ?>"
        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
    </label>

    <label style="display:block;">
      <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Número de parcelas</span>
      <input type="number" name="installments" min="1" max="60" required
        value="<?= htmlspecialchars((string)$old['installments'], ENT_QUOTES, 'UTF-8') ?>"
        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
    </label>

    <label style="display:block;">
      <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Data de início</span>
      <input type="date" name="start_date" required
        value="<?= htmlspecialchars((string)$old['start_date'], ENT_QUOTES, 'UTF-8') ?>"
        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
    </label>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:20px;">
    <label style="display:block;">
      <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Condição de pagamento</span>
      <select name="payment_condition" required
        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
        <?php $pc = $old['payment_condition']; ?>
        <option value="PIX"    <?= $pc==='PIX'?'selected':'' ?>>Pix</option>
        <option value="CARTAO" <?= $pc==='CARTAO'?'selected':'' ?>>Cartão</option>
        <option value="BOLETO" <?= $pc==='BOLETO'?'selected':'' ?>>Boleto</option>
      </select>
    </label>

    <label style="display:block;">
      <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Situação</span>
      <select name="status" required
        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
        <?php $st = $old['status'] ?? $contract->status; ?>
        <option value="ATIVO"     <?= $st==='ATIVO'?'selected':'' ?>>Ativo</option>
        <option value="CONCLUIDO" <?= $st==='CONCLUIDO'?'selected':'' ?>>Concluído</option>
        <option value="CANCELADO" <?= $st==='CANCELADO'?'selected':'' ?>>Cancelado</option>
      </select>
    </label>
  </div>

  <label style="display:block;margin-bottom:20px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Observações (opcional)</span>
    <textarea name="notes" rows="3"
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface);resize:vertical"><?= htmlspecialchars((string)($old['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
  </label>

  <button type="submit" class="btn btn-primary">Salvar</button>
  <a href="<?= BASE_URL ?>/?controller=contract&action=print&id=<?= (int)$contract->id ?>" class="btn" style="margin-left:8px" target="_blank">
    <i data-lucide="printer"></i> Imprimir
  </a>
  <a href="<?= BASE_URL ?>/?controller=contract&action=index" class="btn" style="margin-left:8px">Voltar</a>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
