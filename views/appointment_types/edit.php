<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Editar Tipo de Consulta</h2>

<?php if (!empty($error)): ?>
  <p style="color:#d32f2f;font-size:15px;margin-bottom:16px">
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
  </p>
<?php endif; ?>

<form method="post"
      action="<?= BASE_URL ?>/?controller=appointmenttype&action=edit&id=<?= (int)$type->id ?>"
      style="max-width:520px;padding:20px;margin-top:10px;
             background:var(--surface);border:1px solid var(--border);border-radius:12px">

  <label style="display:block;margin-bottom:16px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">
      Nome do tipo de consulta
    </span>
    <input
      type="text"
      name="name"
      required
      maxlength="100"
      value="<?= htmlspecialchars($type->name, ENT_QUOTES, 'UTF-8'); ?>"
      style="width:100%;padding:10px 12px;border-radius:8px;
             border:1px solid var(--border);
             background:var(--surface-elev);color:var(--on-surface)"
    >
  </label>

  <label style="display:block;margin-bottom:20px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">
      Código interno
    </span>
    <input
      type="text"
      name="code"
      required
      maxlength="50"
      value="<?= htmlspecialchars($type->code, ENT_QUOTES, 'UTF-8'); ?>"
      style="width:100%;padding:10px 12px;border-radius:8px;
             border:1px solid var(--border);
             background:var(--surface-elev);color:var(--on-surface)"
    >
    <small style="font-size:12px;color:var(--muted)">
      Alterar o código não afeta os agendamentos já cadastrados com este tipo.
    </small>
  </label>

  <label style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
    <input type="checkbox" name="active" value="1" <?= $type->active ? 'checked' : '' ?>>
    <span style="font-size:14px;">Ativo (disponível para novos agendamentos)</span>
  </label>

  <button type="submit" class="btn btn-primary">Salvar</button>
  <a href="<?= BASE_URL ?>/?controller=appointmenttype&action=index" class="btn" style="margin-left:8px">
    Voltar
  </a>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
