<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px">Lista de Pacientes</h2>

<div style="display:flex;gap:12px;margin-bottom:20px;align-items:stretch;flex-wrap:wrap;">
  <input 
    type="text" 
    id="searchInput" 
    maxlength="25"
    placeholder="Buscar por Nome ou Celular..." 
    style="flex:1;min-width:250px;padding:12px 16px 12px 40px;border:1px solid var(--border);border-radius:4px;font-size:16px;background:var(--surface);background-image:url('data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;20&quot; height=&quot;20&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;%23999&quot; stroke-width=&quot;2&quot;><circle cx=&quot;11&quot; cy=&quot;11&quot; r=&quot;8&quot;/><path d=&quot;m21 21-4.35-4.35&quot;/></svg>');background-repeat:no-repeat;background-position:12px center;color:var(--fg);"
    onkeyup="filterTable()"
  >
</div>

<table id="patientsTable" style="border-collapse:collapse;width:100%;background:var(--surface);border:1px solid var(--border)">
  <tr style="background:var(--surface-elev);color:var(--fg);">
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">ID</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Nome do Paciente</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Celular</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Status</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Ações</th>
  </tr>
  <?php foreach ($patients as $p): ?>
    <tr>
      <td style="padding:10px;border-bottom:1px solid var(--border)"><?= (int)$p->id ?></td>
      <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($p->fullName, ENT_QUOTES, 'UTF-8') ?></td>
      <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($p->cellphone ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td style="padding:10px;border-bottom:1px solid var(--border)">
        <?= $p->status === 'A' ? 'Ativo' : 'Inativo' ?>
      </td>
      <td style="padding:10px;border-bottom:1px solid var(--border);display:flex;gap:8px">
        <a href="/nutrihealth/public/?controller=patient&action=edit&id=<?= (int)$p->id ?>"
          class="btn btn-primary">
          <i data-lucide="edit-3"></i> Editar
        </a>

        <button type="button"
                class="btn btn-danger"
                onclick="confirmDelete(<?= (int)$p->id ?>)">
          <i data-lucide="trash-2"></i> Excluir
        </button>
      </td>

      
    </tr>
  <?php endforeach; ?>
</table>

<script>
  function confirmDelete(id) {
    Swal.fire({
      title: 'Tem certeza?',
      text: 'Esta ação não poderá ser desfeita!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sim, excluir!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href =          
          `/nutrihealth/public/?controller=patient&action=delete&id=${id}`;
        }
    });
  }

  // Remove acentos e coloca tudo em minúsculo para facilitar busca
  function normalizeText(str) {
    return (str || '')
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')  // remove acentos
      .replace(/\s+/g, ' ')
      .trim();
  }

  function filterTable() {
    const input  = document.getElementById('searchInput');
    const filter = normalizeText(input.value);
    const table  = document.getElementById('patientsTable');
    if (!table) return;

    // pega todas as linhas de dados (ignora o cabeçalho)
    const rows = table.getElementsByTagName('tr');

    // índices das colunas: 0 = ID, 1 = Nome, 2 = Celular
    const colName      = 1;
    const colCellphone = 2;  

    for (let i = 1; i < rows.length; i++) { // começa em 1 para pular o header
      const cells = rows[i].getElementsByTagName('td');
      if (!cells.length) continue;

      const nameText      = normalizeText(cells[colName].innerText);
      const cellphoneText = normalizeText(cells[colCellphone].innerText);

      const combined = nameText + ' ' + cellphoneText;

      if (filter === '' || combined.indexOf(filter) !== -1) {
        rows[i].style.display = '';
      } else {
        rows[i].style.display = 'none';
      }
    }
  }
</script>

<script>
  lucide.createIcons();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
