<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Editar Tipo de Serviço</h2>

<?php if (!empty($error)): ?>
  <p style="color:#d32f2f;font-size:15px;margin-bottom:16px">
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
  </p>
<?php endif; ?>

<form method="post"
      action="<?= BASE_URL ?>/?controller=servicetype&action=edit&id=<?= (int)$type->id ?>"
      style="max-width:560px;padding:20px;margin-top:10px;
             background:var(--surface);border:1px solid var(--border);border-radius:12px">

  <label style="display:block;margin-bottom:16px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Nome do serviço/pacote</span>
    <input type="text" name="name" required maxlength="100"
      value="<?= htmlspecialchars($type->name, ENT_QUOTES, 'UTF-8'); ?>"
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
  </label>

  <label style="display:block;margin-bottom:16px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Descrição (opcional)</span>
    <textarea name="description" rows="2"
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface);resize:vertical"><?= htmlspecialchars($type->description ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
  </label>

  <label style="display:block;margin-bottom:16px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Valor sugerido (R$, opcional)</span>
    <input type="text" name="default_price"
      value="<?= $type->defaultPrice !== null ? htmlspecialchars(number_format($type->defaultPrice, 2, ',', '')) : '' ?>"
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
  </label>

  <label style="display:block;margin-bottom:20px">
    <span style="font-size:14px;font-weight:600;display:block;margin-bottom:6px">Código interno</span>
    <input type="text" name="code" required maxlength="50"
      value="<?= htmlspecialchars($type->code, ENT_QUOTES, 'UTF-8'); ?>"
      style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
  </label>

  <label style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
    <input type="checkbox" name="active" value="1" <?= $type->active ? 'checked' : '' ?>>
    <span style="font-size:14px;">Ativo (disponível para novos contratos)</span>
  </label>

  <button type="submit" class="btn btn-primary">Salvar</button>
  <a href="<?= BASE_URL ?>/?controller=servicetype&action=index" class="btn" style="margin-left:8px">Voltar</a>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
