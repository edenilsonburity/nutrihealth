<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Nova Profissão</h2>

<?php if (!empty($error)): ?>
  <p style="color:#d32f2f;font-size:15px;margin-bottom:16px">
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
  </p>
<?php endif; ?>

<?php
$old = $old ?? [];
?>

<form method="post"
      action="/nutrihealth/public/?controller=occupation&action=create"
      style="max-width:520px;padding:20px;margin-top:10px;
             background:var(--surface);border:1px solid var(--border);border-radius:12px">

  <label style="display:block;margin-bottom:16px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">
      Código da Profissão
    </span>
    <input
      type="text"
      name="code"
      required
      maxlength="7"
      pattern="\d{4}-\d{2}"
      oninput="formatarCodigo(this)"
      value="<?= htmlspecialchars($old['code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
      style="width:100%;padding:10px 12px;border-radius:8px;
             border:1px solid var(--border);
             background:var(--surface-elev);color:var(--on-surface)"
    >
    <small style="font-size:12px;color:var(--muted)">
      Formato: 9999-99 (apenas números).
    </small>
  </label>

  <label style="display:block;margin-bottom:20px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">
      Descrição da Profissão
    </span>
    <input
      type="text"
      name="description"
      required
      maxlength="25"
      oninput="validarDescricao(this)"
      value="<?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
      style="width:100%;padding:10px 12px;border-radius:8px;
             border:1px solid var(--border);
             background:var(--surface-elev);color:var(--on-surface)"
    >
  </label>

  <button type="submit" class="btn btn-primary">Salvar</button>
  <a href="/nutrihealth/public/?controller=occupation&action=index" class="btn" style="margin-left:8px">
    Voltar
  </a>
</form>

<script>
function formatarCodigo(input) {
  let value = input.value.replace(/\D/g, '');
  if (value.length > 4) {
    value = value.slice(0, 4) + '-' + value.slice(4, 6);
  }
  input.value = value;
}

function validarDescricao(input) {
  input.value = input.value.replace(/[^A-Za-zÀ-ú\s]/g, '');
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
