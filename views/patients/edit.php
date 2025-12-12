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
        case 'status':            return $old['status']            ?? $patient->status;
        case 'notes':             return $old['notes']             ?? $patient->notes;
    }
    return '';
};
?>

<form method="post"
      action="/nutrihealth/public/?controller=patient&action=edit&id=<?= (int)$patient->id; ?>"
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
  <a href="/nutrihealth/public/?controller=patient&action=index" class="btn" style="margin-left:8px">Voltar</a>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
