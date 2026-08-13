<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px">Tipos de Consulta</h2>
<p style="margin:0 0 16px 0;color:var(--muted);font-size:0.95rem;">
  Cadastro dos tipos de consulta disponíveis para agendamento (antes era uma lista fixa, agora pode ser editada aqui).
</p>

<?php
  $msg = $_GET['msg'] ?? '';
  $msgText = '';
  if ($msg === 'created') $msgText = 'Tipo de consulta criado com sucesso.';
  elseif ($msg === 'updated') $msgText = 'Tipo de consulta atualizado com sucesso.';
  elseif ($msg === 'deleted') $msgText = 'Tipo de consulta excluído com sucesso.';
  elseif ($msg === 'in_use') $msgText = 'Não é possível excluir: este tipo está em uso em um ou mais agendamentos.';
  elseif ($msg === 'notfound') $msgText = 'Tipo de consulta não encontrado.';
?>
<?php if ($msgText !== ''): ?>
  <div style="padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:var(--surface-elev);color:var(--fg);margin-bottom:16px;">
    <?= htmlspecialchars($msgText) ?>
  </div>
<?php endif; ?>

<div style="display:flex;gap:12px;margin-bottom:20px;align-items:stretch;flex-wrap:wrap;">
  <input
    type="text"
    id="searchInput"
    maxlength="50"
    placeholder="Buscar por código ou nome..."
    style="flex:1;min-width:250px;padding:12px 16px;border:1px solid var(--border);border-radius:4px;font-size:16px;background:var(--surface);color:var(--fg);"
    onkeyup="filterTable()"
  >
</div>

<div style="overflow-x:auto;">
  <table id="typesTable" style="border-collapse:collapse;width:100%;background:var(--surface);border:1px solid var(--border)">
    <tr style="background:var(--surface-elev)">
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Código</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Nome</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Situação</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Ações</th>
    </tr>

    <?php foreach (($types ?? []) as $t): ?>
      <tr>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($t->code) ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($t->name) ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)">
          <?= $t->active ? 'Ativo' : 'Inativo' ?>
        </td>
        <td style="padding:10px;border-bottom:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>/?controller=appointmenttype&action=edit&id=<?= (int)$t->id ?>" class="btn btn-primary">
            <i data-lucide="edit-3"></i> Editar
          </a>
          <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= (int)$t->id ?>)">
            <i data-lucide="trash-2"></i> Excluir
          </button>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<div style="margin-top:16px;">
  <a href="<?= BASE_URL ?>/?controller=appointment&action=calendar" class="btn">
    <i data-lucide="arrow-left"></i> Voltar à agenda
  </a>
</div>

<script>
  function confirmDelete(id) {
    Swal.fire({
      title: 'Tem certeza?',
      text: 'Esta ação não poderá ser desfeita! (Não é possível excluir tipos em uso por agendamentos.)',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sim, excluir!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `<?= BASE_URL ?>/?controller=appointmenttype&action=delete&id=${id}`;
      }
    });
  }

  function normalizeText(str) {
    return (str || '').toString().toLowerCase().normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, ' ').trim();
  }

  function filterTable() {
    const input  = document.getElementById('searchInput');
    const filter = normalizeText(input.value);
    const table  = document.getElementById('typesTable');
    if (!table) return;

    const rows = table.getElementsByTagName('tr');
    for (let i = 1; i < rows.length; i++) {
      const cells = rows[i].getElementsByTagName('td');
      if (!cells.length) continue;

      const combined = normalizeText(cells[0].innerText) + ' ' + normalizeText(cells[1].innerText);
      rows[i].style.display = (filter === '' || combined.indexOf(filter) !== -1) ? '' : 'none';
    }
  }
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
