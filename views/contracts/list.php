<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 style="margin-top:8px">Contratos</h2>
<p style="margin:0 0 16px 0;color:var(--muted);font-size:0.95rem;">
  Contratos de prestação de serviço firmados com os pacientes.
</p>

<?php
  use App\Models\Contract;
  $msg = $_GET['msg'] ?? '';
  $msgText = '';
  if ($msg === 'updated') $msgText = 'Contrato atualizado com sucesso.';
  elseif ($msg === 'updated_kept_installments') $msgText = 'Contrato atualizado. O parcelamento não foi recalculado porque já existem parcelas pagas — ajuste manualmente na tela de Parcelas, se necessário.';
  elseif ($msg === 'deleted') $msgText = 'Contrato excluído com sucesso.';
  elseif ($msg === 'notfound') $msgText = 'Contrato não encontrado.';
?>
<?php if ($msgText !== ''): ?>
  <div style="padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:var(--surface-elev);color:var(--fg);margin-bottom:16px;">
    <?= htmlspecialchars($msgText) ?>
  </div>
<?php endif; ?>

<div style="overflow-x:auto;">
  <table style="border-collapse:collapse;width:100%;background:var(--surface);border:1px solid var(--border)">
    <tr style="background:var(--surface-elev)">
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Paciente</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Serviço</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Valor total</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Parcelas</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Pagamento</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Situação</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Ações</th>
    </tr>

    <?php if (empty($contracts)): ?>
      <tr><td colspan="7" style="padding:14px;color:var(--muted)">Nenhum contrato cadastrado ainda.</td></tr>
    <?php endif; ?>

    <?php foreach (($contracts ?? []) as $c): ?>
      <tr>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($c->patientName ?? '') ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= htmlspecialchars($c->serviceTypeName ?? '') ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)">R$ <?= number_format($c->totalValue, 2, ',', '.') ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)">
          <?= (int)$c->installments ?>x de R$ <?= number_format($c->installmentValue(), 2, ',', '.') ?>
        </td>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= Contract::paymentConditionLabel($c->paymentCondition) ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= Contract::statusLabel($c->status) ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>/?controller=contract&action=installments&id=<?= (int)$c->id ?>" class="btn">
            <i data-lucide="wallet"></i> Parcelas
          </a>
          <a href="<?= BASE_URL ?>/?controller=contract&action=print&id=<?= (int)$c->id ?>" class="btn" target="_blank">
            <i data-lucide="printer"></i> Imprimir
          </a>
          <a href="<?= BASE_URL ?>/?controller=contract&action=downloadDocx&id=<?= (int)$c->id ?>" class="btn">
            <i data-lucide="file-text"></i> Baixar Word
          </a>
          <a href="<?= BASE_URL ?>/?controller=contract&action=edit&id=<?= (int)$c->id ?>" class="btn btn-primary">
            <i data-lucide="edit-3"></i> Editar
          </a>
          <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= (int)$c->id ?>)">
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
      text: 'Esta ação não poderá ser desfeita!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sim, excluir!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `<?= BASE_URL ?>/?controller=contract&action=delete&id=${id}`;
      }
    });
  }
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
