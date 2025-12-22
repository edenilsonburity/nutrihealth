<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Alterar senha</h2>

<?php if (!empty($error)): ?>
  <p style="color:#d32f2f;font-size:15px;margin-bottom:16px">
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
  </p>
<?php endif; ?>

<form method="post"
      action="/nutrihealth/public/?controller=user&action=changePassword&id=<?= (int)$userId ?>"
      style="max-width:560px;padding:20px;background:var(--surface);border:1px solid var(--border);border-radius:12px">

  <?php if (!empty($isSelf) && empty($isAdmin)): ?>
    <label style="display:block;margin-bottom:12px">
      <span>Senha atual</span>
      <input type="password" name="current_password" required
             style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    </label>
  <?php endif; ?>

  <label style="display:block;margin-bottom:12px">
    <span>Nova senha</span>
    <input type="password" name="new_password" required
           style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:16px">
    <span>Confirmar nova senha</span>
    <input type="password" name="confirm_password" required
           style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <button type="submit" class="btn btn-primary">
    <i data-lucide="save"></i> Salvar
  </button>

  <a href="/nutrihealth/public/?controller=user&action=index" class="btn" style="margin-left:8px">
    <i data-lucide="arrow-left"></i> Voltar
  </a>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
