<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Novo Paciente</h2>

<?php if (!empty($error)): ?>
  <p style="color:#d32f2f;font-size:15px;margin-bottom:16px">
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
  </p>
<?php endif; ?>

<?php $old = $old ?? []; ?>

<form method="post"
      action="<?= BASE_URL ?>/?controller=patient&action=create"
      style="max-width:720px;padding:20px;margin-top:10px;
             background:var(--surface);border:1px solid var(--border);border-radius:12px">

  <label style="display:block;margin-bottom:12px">
    <span>Nome do Paciente</span>
    <input type="text" name="name_patient" required
           value="<?= htmlspecialchars($old['name_patient'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>CPF</span>
    <input type="text" name="cpf" required
           value="<?= htmlspecialchars($old['cpf'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           placeholder="Somente números"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Profissão</span>
    <input
      type="text"
      name="occupation"
      list="occupationsList"
      required
      autocomplete="off"
      maxlength="100"
      placeholder="Digite a profissão..."
      value="<?= htmlspecialchars($old['occupation_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
      style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    <datalist id="occupationsList">
      <?php foreach (($occupations ?? []) as $o): ?>
        <option value="<?= htmlspecialchars($o->description ?? '', ENT_QUOTES, 'UTF-8') ?>"></option>
      <?php endforeach; ?>
    </datalist>
    <small style="font-size:12px;color:var(--muted)">
      Se a profissão digitada ainda não existir no cadastro, ela será criada automaticamente.
    </small>
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Data de nascimento</span>
    <input type="date" name="birth_date"
           value="<?= htmlspecialchars($old['birth_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Telefone</span>
    <input type="text" name="phone" class="form-field"
           value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Celular</span>
    <input type="text" name="cellphone" class="form-field"
           value="<?= htmlspecialchars($old['cellphone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>E-mail</span>
    <input type="email" name="email"
           value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Endereço</span>
    <input type="text" name="address"
           value="<?= htmlspecialchars($old['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:12px;">
    <label style="display:block;">
      <span>CEP</span>
      <input type="text" name="cep" maxlength="9" placeholder="00000-000"
             value="<?= htmlspecialchars($old['cep'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
             style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    </label>

    <label style="display:block;">
      <span>RG</span>
      <input type="text" name="rg"
             value="<?= htmlspecialchars($old['rg'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
             style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    </label>

    <label style="display:block;">
      <span>Nacionalidade</span>
      <input type="text" name="nationality" placeholder="Brasileiro(a)"
             value="<?= htmlspecialchars($old['nationality'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
             style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    </label>

    <label style="display:block;">
      <span>Estado civil</span>
      <select name="marital_status"
              style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
        <?php $ms = $old['marital_status'] ?? ''; ?>
        <option value="" <?= $ms===''?'selected':'' ?>>Não informado</option>
        <option value="Solteiro(a)" <?= $ms==='Solteiro(a)'?'selected':'' ?>>Solteiro(a)</option>
        <option value="Casado(a)"   <?= $ms==='Casado(a)'?'selected':'' ?>>Casado(a)</option>
        <option value="Divorciado(a)" <?= $ms==='Divorciado(a)'?'selected':'' ?>>Divorciado(a)</option>
        <option value="Viúvo(a)"    <?= $ms==='Viúvo(a)'?'selected':'' ?>>Viúvo(a)</option>
        <option value="União estável" <?= $ms==='União estável'?'selected':'' ?>>União estável</option>
      </select>
    </label>
  </div>
  <p style="margin:-4px 0 12px 0;font-size:12px;color:var(--muted)">
    RG, nacionalidade, estado civil e CEP são usados na geração de contratos.
  </p>

  <label style="display:block;margin-bottom:12px">
    <span>Contato de emergência</span>
    <input type="text" name="emergency_contact"
           value="<?= htmlspecialchars($old['emergency_contact'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Filiação / Responsável (para menor)</span>
    <input type="text" name="guardian_name"
           value="<?= htmlspecialchars($old['guardian_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Status</span>
    <select name="status"
            style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
      <option value="A" <?= (isset($old['status']) ? $old['status'] === 'A' : true) ? 'selected' : '' ?>>Ativo</option>
      <option value="I" <?= (isset($old['status']) && $old['status'] === 'I') ? 'selected' : '' ?>>Inativo</option>
    </select>
  </label>

  <label style="display:block;margin-bottom:16px">
    <span>Observações</span>
    <textarea name="notes" rows="3"
              style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);"><?= htmlspecialchars($old['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </label>

  <button type="submit" class="btn btn-primary">Salvar</button>
  <a href="<?= BASE_URL ?>/?controller=patient&action=index" class="btn" style="margin-left:8px">Voltar</a>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
