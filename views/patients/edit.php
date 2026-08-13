<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Editar Paciente</h2>

<?php if (!empty($error)): ?>
  <p style="color:#d32f2f;font-size:15px;margin-bottom:16px">
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
  </p>
<?php endif; ?>

<?php
$old = $old ?? [];
// Valores default vindos do objeto
$val = function(string $field) use ($old, $patient) {
    switch ($field) {
        case 'name_patient':      return $old['name_patient']      ?? $patient->fullName;
        case 'cpf':               return $old['cpf']               ?? $patient->cpf;
        case 'birth_date':        return $old['birth_date']        ?? $patient->birthDate;
        case 'phone':             return $old['phone']             ?? $patient->phone;
        case 'cellphone':         return $old['cellphone']         ?? $patient->cellphone;
        case 'email':             return $old['email']             ?? $patient->email;
        case 'address':           return $old['address']           ?? $patient->address;
        case 'emergency_contact': return $old['emergency_contact'] ?? $patient->emergencyContact;
        case 'guardian_name':     return $old['guardian_name']     ?? $patient->guardianName;
        case 'rg':                return $old['rg']                ?? $patient->rg;
        case 'nationality':       return $old['nationality']       ?? $patient->nationality;
        case 'marital_status':    return $old['marital_status']    ?? $patient->maritalStatus;
        case 'cep':               return $old['cep']               ?? $patient->cep;
        case 'idOccupation':      return $old['idOccupation']      ?? $patient->occupationId;
        case 'occupation':        return $old['occupation_text']   ?? $patient->occupationDescription;
        case 'status':            return $old['status']            ?? $patient->status;
        case 'notes':             return $old['notes']             ?? $patient->notes;
    }
    return '';
};
?>

<form method="post"
      action="<?= BASE_URL ?>/?controller=patient&action=edit&id=<?= (int)$patient->id; ?>"
      style="max-width:720px;padding:20px;margin-top:10px;
             background:var(--surface);border:1px solid var(--border);border-radius:12px">

  <label style="display:block;margin-bottom:12px">
    <span>Nome completo</span>
    <input type="text" name="name_patient" required
           value="<?= htmlspecialchars($val('name_patient') ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>CPF</span>
    <input type="text" name="cpf" required
           value="<?= htmlspecialchars($val('cpf') ?? '', ENT_QUOTES, 'UTF-8') ?>"
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
      value="<?= htmlspecialchars($val('occupation') ?? '', ENT_QUOTES, 'UTF-8') ?>"
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
           value="<?= htmlspecialchars($val('birth_date') ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Telefone</span>
    <input type="text" name="phone"
           value="<?= htmlspecialchars($val('phone') ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Celular</span>
    <input type="text" name="cellphone"
           value="<?= htmlspecialchars($val('cellphone') ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>E-mail</span>
    <input type="email" name="email"
           value="<?= htmlspecialchars($val('email') ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Endereço</span>
    <input type="text" name="address"
           value="<?= htmlspecialchars($val('address') ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:12px;">
    <label style="display:block;">
      <span>CEP</span>
      <input type="text" name="cep" maxlength="9" placeholder="00000-000"
             value="<?= htmlspecialchars($val('cep') ?? '', ENT_QUOTES, 'UTF-8') ?>"
             style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    </label>

    <label style="display:block;">
      <span>RG</span>
      <input type="text" name="rg"
             value="<?= htmlspecialchars($val('rg') ?? '', ENT_QUOTES, 'UTF-8') ?>"
             style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    </label>

    <label style="display:block;">
      <span>Nacionalidade</span>
      <input type="text" name="nationality" placeholder="Brasileiro(a)"
             value="<?= htmlspecialchars($val('nationality') ?? '', ENT_QUOTES, 'UTF-8') ?>"
             style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    </label>

    <label style="display:block;">
      <span>Estado civil</span>
      <select name="marital_status"
              style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
        <?php $ms = $val('marital_status') ?? ''; ?>
        <option value="" <?= $ms===''?'selected':'' ?>>Não informado</option>
        <option value="Solteiro(a)" <?= $ms==='Solteiro(a)'?'selected':'' ?>>Solteiro(a)</option>
        <option value="Casado(a)"   <?= $ms==='Casado(a)'?'selected':'' ?>>Casado(a)</option>
        <option value="Divorciado(a)" <?= $ms==='Divorciado(a)'?'selected':'' ?>>Divorciado(a)</option>
        <option value="Viúvo(a)"    <?= $ms==='Viúvo(a)'?'selected':'' ?>>Viúvo(a)</option>
        <option value="União estável" <?= $ms==='União estável'?'selected':'' ?>>União estável</option>
      </select>
    </label>
  </div>

  <label style="display:block;margin-bottom:12px">
    <span>Contato de emergência</span>
    <input type="text" name="emergency_contact"
           value="<?= htmlspecialchars($val('emergency_contact') ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Filiação / Responsável (para menor)</span>
    <input type="text" name="guardian_name"
           value="<?= htmlspecialchars($val('guardian_name') ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
  </label>

  <label style="display:block;margin-bottom:12px">
    <span>Status</span>
    <select name="status"
            style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
      <?php $statusVal = $val('status') ?? 'A'; ?>
      <option value="A" <?= $statusVal === 'A' ? 'selected' : '' ?>>Ativo</option>
      <option value="I" <?= $statusVal === 'I' ? 'selected' : '' ?>>Inativo</option>
    </select>
  </label>

  <label style="display:block;margin-bottom:16px">
    <span>Observações</span>
    <textarea name="notes" rows="3"
              style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);"><?= htmlspecialchars($val('notes') ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </label>

  <button type="submit" class="btn btn-primary">Salvar</button>
  <a href="<?= BASE_URL ?>/?controller=patient&action=index" class="btn" style="margin-left:8px">Voltar</a>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
