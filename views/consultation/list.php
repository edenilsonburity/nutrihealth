<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px">Consultas</h2>

<form method="get" action="/nutrihealth/public/" style="margin:16px 0;display:flex;gap:12px;flex-wrap:wrap;align-items:end">
  <input type="hidden" name="controller" value="consultation">
  <input type="hidden" name="action" value="index">

  <div style="display:flex;flex-direction:column;gap:6px;min-width:180px">
    <label style="color:var(--muted)">Data (de)</label>
    <input type="date" name="date_from"
           value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--surface-elev);color:var(--fg)">
  </div>

  <div style="display:flex;flex-direction:column;gap:6px;min-width:180px">
    <label style="color:var(--muted)">Data (até)</label>
    <input type="date" name="date_to"
           value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--surface-elev);color:var(--fg)">
  </div>

  <div style="display:flex;flex-direction:column;gap:6px;flex:1;min-width:240px">
    <label style="color:var(--muted)">Paciente</label>
    <input type="text" name="patient_name" maxlength="60"
           placeholder="Buscar por nome do paciente"
           value="<?= htmlspecialchars($filters['patient_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           style="padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--surface-elev);color:var(--fg)">
  </div>

  <div style="display:flex;flex-direction:column;gap:6px;min-width:240px">
    <label style="color:var(--muted)">Tipo de consulta</label>
    <select name="type"
            style="padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--surface-elev);color:var(--fg)">
      <option value="">Todos</option>
      <option value="PRIMEIRA_CONSULTA"      <?= (($filters['type'] ?? '')==='PRIMEIRA_CONSULTA')?'selected':'' ?>>Primeira consulta</option>
      <option value="RETORNO"               <?= (($filters['type'] ?? '')==='RETORNO')?'selected':'' ?>>Retorno</option>
      <option value="AVALIACAO_CORPORAL"    <?= (($filters['type'] ?? '')==='AVALIACAO_CORPORAL')?'selected':'' ?>>Avaliação corporal</option>
      <option value="ORIENTACAO_NUTRICIONAL"<?= (($filters['type'] ?? '')==='ORIENTACAO_NUTRICIONAL')?'selected':'' ?>>Orientação nutricional</option>
    </select>
  </div>

  <div style="display:flex;gap:8px">
    <button type="submit" class="btn btn-primary">
      <i data-lucide="search"></i> Filtrar
    </button>

    <a href="/nutrihealth/public/?controller=consultation&action=index" class="btn">
      <i data-lucide="rotate-ccw"></i> Limpar
    </a>
  </div>
</form>

<table style="border-collapse:collapse;width:100%;background:var(--surface);border:1px solid var(--border)">
  <tr style="background:var(--surface-elev)">
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Data</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Paciente</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Celular</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Tipo</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Ações</th>
  </tr>

  <?php if (empty($rows)): ?>
    <tr>
      <td colspan="5" style="padding:14px;color:var(--muted)">
        Nenhuma consulta encontrada com os filtros informados.
      </td>
    </tr>
  <?php else: ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td style="padding:10px;border-bottom:1px solid var(--border)">
          <?= htmlspecialchars(date('d/m/Y H:i', strtotime($r['consultation_date'])), ENT_QUOTES, 'UTF-8') ?>
        </td>

        <td style="padding:10px;border-bottom:1px solid var(--border)">
          <?= htmlspecialchars($r['patient_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </td>

        <td style="padding:10px;border-bottom:1px solid var(--border)">
          <?= htmlspecialchars($r['patient_cellphone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </td>

        <td style="padding:10px;border-bottom:1px solid var(--border)">
          <?= htmlspecialchars($r['type'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </td>

        <td style="padding:10px;border-bottom:1px solid var(--border);display:flex;gap:8px">
          <!-- Reutiliza o MESMO view usado na agenda -->          
          <a class="btn btn-primary"
            href="/nutrihealth/public/?controller=consultation&action=view&appointment_id=<?= (int)$r['appointment_id'] ?>&from=list">
            <i data-lucide="eye"></i> Visualizar
          </a>

          <!-- Nova página: evolução do paciente -->
          <a class="btn"
            href="/nutrihealth/public/?controller=consultation&action=evolution&patient_id=<?= (int)$r['patient_id'] ?>&from=list">
            <i data-lucide="trending-up"></i> Evolução
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
</table>

<script>
  // garante ícones na tela (se o footer não estiver chamando)
  if (window.lucide) lucide.createIcons();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
