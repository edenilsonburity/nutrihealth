<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px">Tipos de Serviço / Pacotes</h2>
<p style="margin:0 0 16px 0;color:var(--muted);font-size:0.95rem;">
  Cadastro dos serviços/pacotes que podem ser contratados pelos pacientes (ex.: Acompanhamento Mensal, Avaliação Única).
</p>

<?php
  $msg = $_GET['msg'] ?? '';
  $msgText = '';
  if ($msg === 'created') $msgText = 'Tipo de serviço criado com sucesso.';
  elseif ($msg === 'updated') $msgText = 'Tipo de serviço atualizado com sucesso.';
  elseif ($msg === 'deleted') $msgText = 'Tipo de serviço excluído com sucesso.';
  elseif ($msg === 'in_use') $msgText = 'Não é possível excluir: este tipo está em uso em um ou mais contratos.';
  elseif ($msg === 'notfound') $msgText = 'Tipo de serviço não encontrado.';
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
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Valor sugerido</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Situação</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Ações</th>
    </tr>

    <?php foreach (($types ?? []) as $t): ?>
      <tr>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($t->code) ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($t->name) ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)">
          <?= $t->defaultPrice !== null ? 'R$ ' . number_format($t->defaultPrice, 2, ',', '.') : '—' ?>
        </td>
        <td style="padding:10px;border-bottom:1px solid var(--border)">
          <?= $t->active ? 'Ativo' : 'Inativo' ?>
        </td>
        <td style="padding:10px;border-bottom:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>/?controller=servicetype&action=edit&id=<?= (int)$t->id ?>" class="btn btn-primary">
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

<script>
  function confirmDelete(id) {
    Swal.fire({
      title: 'Tem certeza?',
      text: 'Esta ação não poderá ser desfeita! (Não é possível excluir tipos em uso por contratos.)',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sim, excluir!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `<?= BASE_URL ?>/?controller=servicetype&action=delete&id=${id}`;
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
