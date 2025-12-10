<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Login</h2>

<?php if (!empty($error)): ?>
  <div style="color:#f87171;margin-bottom:1rem;">
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<form method="post" action="/nutrihealth/public/?controller=user&action=login" style="max-width:320px;">
  <div style="margin-bottom:12px;">
    <label for="email">E-mail</label><br>
    <input type="email" name="email" id="email" required style="width:100%;padding:8px;">
  </div>

  <div style="margin-bottom:12px;">
    <label for="password">Senha</label><br>
    <input type="password" name="password" id="password" required style="width:100%;padding:8px;">
  </div>

  <button type="submit" class="btn btn-primary">Entrar</button>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
