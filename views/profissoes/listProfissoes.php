<?php include __DIR__ . '/../partials/headerProfissoes.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Lista de Profissões</h2>

<div style="display:flex;gap:12px;margin-bottom:20px;align-items:stretch;flex-wrap:wrap">
  <input 
    type="text" 
    id="searchInput" 
    placeholder="Buscar por Código ou Descrição..." 
    style="flex:1;min-width:250px;padding:12px 16px 12px 40px;border:1px solid var(--border);border-radius:4px;font-size:16px;background:var(--surface);background-image:url('data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;20&quot; height=&quot;20&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;%23999&quot; stroke-width=&quot;2&quot;><circle cx=&quot;11&quot; cy=&quot;11&quot; r=&quot;8&quot;/><path d=&quot;m21 21-4.35-4.35&quot;/></svg>');background-repeat:no-repeat;background-position:12px center"
    onkeyup="filterTable()"
  >
  <a href="/nutrihealth/public/indexProfissoes.php?action=createProfissoes" class="btn btn-primary" style="background:#2b93d8;white-space:nowrap;padding:21px 20px;display:flex;align-items:center;text-decoration:none">
    + Nova Profissão
  </a>
</div>

<table id="professionsTable" style="border-collapse:collapse;width:100%;background:var(--surface);border:1px solid var(--border)">
  <tr style="background:var(--surface-elev)">
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Código</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Descrição</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Ações</th>
  </tr>
  <?php foreach ($profissoes as $p): ?>
    <tr>
      <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($p->codigo) ?></td>
      <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($p->descricao_profissao) ?></td>
      <td style="padding:10px;border-bottom:1px solid var(--border)">
        <div style="display:flex;gap:8px">
          <a href="?action=editProfissoes&id=<?= (int)$p->id ?>" class="btn btn-primary"><i data-lucide="edit-3"></i> Editar</a>
          <button type="button" class="btn btn-danger" onclick="excluirConteudo(<?= (int)$p->id ?>)"><i data-lucide="trash-2"></i> Excluir</button>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<script>
function filterTable() {
  const input = document.getElementById('searchInput');
  const filter = input.value.toUpperCase();
  const table = document.getElementById('professionsTable');
  const tr = table.getElementsByTagName('tr');

  for (let i = 1; i < tr.length; i++) { // Começa em 1 para pular o cabeçalho (th)
    let display = 'none';
    const td = tr[i].getElementsByTagName('td');
    for (let j = 0; j < td.length; j++) {
      if (td[j]) {
        if (td[j].innerHTML.toUpperCase().indexOf(filter) > -1) {
          display = '';
          break; // Sai do loop das células se encontrar uma correspondência
        }
      }
    }
    tr[i].style.display = display;
  }
}

function excluirConteudo(id) {
  Swal.fire({
    title: 'Você tem certeza?',
    text: 'Esta ação não poderá ser desfeita!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sim, Excluir!',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `?action=deleteProfissoes&id=${id}`;
    }
  });
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>