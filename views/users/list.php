<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px">Lista de Usuários</h2>

<div style="display:flex;gap:12px;margin-bottom:20px;align-items:stretch;flex-wrap:wrap;">
  <input 
    type="text" 
    id="searchInput" 
    maxlength="25"
    placeholder="Buscar por Nome..." 
    style="flex:1;min-width:250px;padding:12px 16px 12px 40px;border:1px solid var(--border);border-radius:4px;font-size:16px;background:var(--surface);background-image:url('data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;20&quot; height=&quot;20&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;%23999&quot; stroke-width=&quot;2&quot;><circle cx=&quot;11&quot; cy=&quot;11&quot; r=&quot;8&quot;/><path d=&quot;m21 21-4.35-4.35&quot;/></svg>');background-repeat:no-repeat;background-position:12px center;color:var(--fg);"
    onkeyup="filterTable()"
  >
</div>

<table id="usersTable" style="border-collapse:collapse;width:100%;background:var(--surface);border:1px solid var(--border)">
  <tr style="background:var(--surface-elev)">
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">ID</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Nome</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Email</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Tipo</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Ações</th>
  </tr>
  <?php foreach ($users as $u): ?>
    <tr>
      <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars((string)$u->id) ?></td>
      <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($u->name) ?></td>
      <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($u->email) ?></td>
      <td style="padding:10px;border-bottom:1px solid var(--border)">
        <?php echo $u->typeUser === 'A' ? 'Administrador' : ($u->typeUser === 'N' ? 'Nutricionista' : 'Usuário'); ?>
      </td>
      <td style="padding:10px;border-bottom:1px solid var(--border);display:flex;gap:8px">
        <a href="?action=edit&id=<?= (int)$u->id ?>" class="btn btn-primary"><i data-lucide="edit-3"></i> Editar</a>
        <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= (int)$u->id ?>)"><i data-lucide="trash-2"></i> Excluir</button>
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
        window.location.href = `?action=delete&id=${id}`;
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
      const table  = document.getElementById('usersTable');
      if (!table) return;

      // pega todas as linhas de dados (ignora o cabeçalho)
      const rows = table.getElementsByTagName('tr');

      // índices das colunas: 0 = ID, 1 = Nome
      const colName      = 1;      

      for (let i = 1; i < rows.length; i++) { // começa em 1 para pular o header
        const cells = rows[i].getElementsByTagName('td');
        if (!cells.length) continue;

        const nameText      = normalizeText(cells[colName].innerText);        

        const combined = nameText;

        if (filter === '' || combined.indexOf(filter) !== -1) {
          rows[i].style.display = '';
        } else {
          rows[i].style.display = 'none';
        }
      }
    }
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
