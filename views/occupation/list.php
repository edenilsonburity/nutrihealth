<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px">Lista de Profissões</h2>

<table style="border-collapse:collapse;width:100%;background:var(--surface);border:1px solid var(--border)">
  <tr style="background:var(--surface-elev)">
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">ID</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Código</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Descrição</th>
    <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Ações</th>
  </tr>

  <?php foreach (($occupations ?? []) as $o): ?>
    <tr>
      <td style="padding:10px;border-bottom:1px solid var(--border)">
        <?= htmlspecialchars((string)$o->id) ?>
      </td>

      <td style="padding:10px;border-bottom:1px solid var(--border)">
        <?= htmlspecialchars($o->code) ?>
      </td>

      <td style="padding:10px;border-bottom:1px solid var(--border)">
        <?= htmlspecialchars($o->description) ?>
      </td>

      <td style="padding:10px;border-bottom:1px solid var(--border);display:flex;gap:8px">
        <a href="/nutrihealth/public/?controller=occupation&action=edit&id=<?= (int)$o->id ?>"
           class="btn btn-primary">
          <i data-lucide="edit-3"></i> Editar
        </a>

        <button type="button"
                class="btn btn-danger"
                onclick="confirmDelete(<?= (int)$o->id ?>)">
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
        `/nutrihealth/public/?controller=occupation&action=delete&id=${id}`;
    }
  });
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
