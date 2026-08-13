<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Novo Contrato</h2>

<?php if (!empty($errors)): ?>
  <div style="padding:12px 14px;border:1px solid #d32f2f;border-radius:10px;background:rgba(211,47,47,0.08);color:#d32f2f;margin-bottom:16px;">
    <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<?php $old = $old ?? []; ?>

<?php if (empty($serviceTypes)): ?>
  <div style="padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:var(--surface-elev);color:var(--fg);margin-bottom:16px;">
    Nenhum tipo de serviço cadastrado ainda.
    <a href="<?= BASE_URL ?>/?controller=servicetype&action=create" style="color:var(--primary)">Cadastre um tipo de serviço</a> antes de criar um contrato.
  </div>
<?php endif; ?>

<form method="post"
      action="<?= BASE_URL ?>/?controller=contract&action=create"
      style="max-width:640px;padding:20px;margin-top:10px;
             background:var(--surface);border:1px solid var(--border);border-radius:12px">

  <label style="display:block;margin-bottom:16px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Paciente</span>
    <select name="patient_id" required
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
      <option value="">Selecione...</option>
      <?php foreach (($patients ?? []) as $p): ?>
        <option value="<?= (int)$p->id ?>" <?= ((int)($old['patient_id'] ?? 0) === (int)$p->id) ? 'selected' : '' ?>>
          <?= htmlspecialchars($p->fullName) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label style="display:block;margin-bottom:16px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Tipo de serviço / pacote</span>
    <select name="service_type_id" id="service_type_id" required
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
      <option value="">Selecione...</option>
      <?php foreach (($serviceTypes ?? []) as $s): ?>
        <option value="<?= (int)$s->id ?>"
                data-price="<?= $s->defaultPrice !== null ? htmlspecialchars(number_format($s->defaultPrice, 2, '.', '')) : '' ?>"
                <?= ((int)($old['service_type_id'] ?? 0) === (int)$s->id) ? 'selected' : '' ?>>
          <?= htmlspecialchars($s->name) ?><?= $s->defaultPrice !== null ? ' (R$ ' . number_format($s->defaultPrice, 2, ',', '.') . ')' : '' ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px;">
    <label style="display:block;">
      <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Valor total (R$)</span>
      <input type="text" name="total_value" id="total_value" required
        placeholder="Ex.: 1200,00"
        value="<?= htmlspecialchars($old['total_value'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
    </label>

    <label style="display:block;">
      <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Número de parcelas</span>
      <input type="number" name="installments" min="1" max="60" required
        value="<?= htmlspecialchars((string)($old['installments'] ?? 1), ENT_QUOTES, 'UTF-8') ?>"
        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
    </label>

    <label style="display:block;">
      <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Data de início</span>
      <input type="date" name="start_date" required
        value="<?= htmlspecialchars($old['start_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
    </label>
  </div>

  <label style="display:block;margin-bottom:20px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Condição de pagamento</span>
    <select name="payment_condition" required
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
      <?php $pc = $old['payment_condition'] ?? 'PIX'; ?>
      <option value="PIX"    <?= $pc==='PIX'?'selected':'' ?>>Pix</option>
      <option value="CARTAO" <?= $pc==='CARTAO'?'selected':'' ?>>Cartão</option>
      <option value="BOLETO" <?= $pc==='BOLETO'?'selected':'' ?>>Boleto</option>
    </select>
    <small style="font-size:12px;color:var(--muted)">
      Pix e Cartão poderão futuramente gerar automaticamente o link de cobrança pela InfinitePay.
      Boleto, por enquanto, é gerado manualmente pelo app da InfinitePay.
    </small>
  </label>

  <label style="display:block;margin-bottom:20px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Observações (opcional)</span>
    <textarea name="notes" rows="3"
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface);resize:vertical"><?= htmlspecialchars($old['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </label>

  <button type="submit" class="btn btn-primary">Salvar e gerar contrato para impressão</button>
  <a href="<?= BASE_URL ?>/?controller=contract&action=index" class="btn" style="margin-left:8px">Cancelar</a>
</form>

<script>
  // Preenche o valor total automaticamente com o valor sugerido do serviço escolhido
  // (só se o campo de valor ainda estiver vazio, para não sobrescrever o que o usuário já digitou)
  document.getElementById('service_type_id').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const price = opt ? opt.getAttribute('data-price') : '';
    const valueInput = document.getElementById('total_value');
    if (price && valueInput.value.trim() === '') {
      valueInput.value = price.replace('.', ',');
    }
  });
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
