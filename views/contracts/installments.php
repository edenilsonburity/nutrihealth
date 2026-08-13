<?php
use App\Models\Contract;
use App\Models\ContractInstallment;

include __DIR__ . '/../partials/header.php';

$installments = $summary['installments'];
?>

<h2 style="margin-top:8px">Parcelas do Contrato #<?= (int)$contract->id ?></h2>
<p style="margin:0 0 16px 0;color:var(--muted);font-size:0.95rem;">
  <?= htmlspecialchars($contract->patientName ?? '') ?> — <?= htmlspecialchars($contract->serviceTypeName ?? '') ?>
</p>

<?php if (isset($_GET['chargeOk'])): ?>
  <div style="padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:rgba(34,197,94,0.12);color:var(--fg);margin-bottom:16px;">
    Link de cobrança gerado com sucesso! Copie e envie para o paciente.
  </div>
<?php elseif (!empty($_GET['chargeError'])): ?>
  <div style="padding:12px 14px;border:1px solid rgba(239,68,68,0.5);border-radius:10px;background:rgba(239,68,68,0.12);color:var(--fg);margin-bottom:16px;">
    Não foi possível gerar a cobrança: <?= htmlspecialchars($_GET['chargeError'] === 'condicao_invalida' ? 'a condição de pagamento deste contrato é Boleto (gerado manualmente pelo app da InfinitePay).' : $_GET['chargeError']) ?>
  </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px;">
  <div style="padding:14px;border:1px solid var(--border);border-radius:10px;background:var(--surface-elev);">
    <div style="font-size:12px;color:var(--muted);">Valor total</div>
    <div style="font-size:18px;font-weight:700;">R$ <?= number_format($contract->totalValue, 2, ',', '.') ?></div>
  </div>
  <div style="padding:14px;border:1px solid var(--border);border-radius:10px;background:rgba(34,197,94,0.12);">
    <div style="font-size:12px;color:var(--muted);">Pago</div>
    <div style="font-size:18px;font-weight:700;">R$ <?= number_format($summary['totalPago'], 2, ',', '.') ?></div>
  </div>
  <div style="padding:14px;border:1px solid var(--border);border-radius:10px;background:var(--surface-elev);">
    <div style="font-size:12px;color:var(--muted);">Pendente</div>
    <div style="font-size:18px;font-weight:700;">R$ <?= number_format($summary['totalPendente'], 2, ',', '.') ?></div>
  </div>
  <?php if ($summary['atrasadas'] > 0): ?>
    <div style="padding:14px;border:1px solid rgba(239,68,68,0.5);border-radius:10px;background:rgba(239,68,68,0.12);">
      <div style="font-size:12px;color:var(--muted);">Parcelas atrasadas</div>
      <div style="font-size:18px;font-weight:700;"><?= (int)$summary['atrasadas'] ?></div>
    </div>
  <?php endif; ?>
</div>

<div style="overflow-x:auto;">
  <table style="border-collapse:collapse;width:100%;background:var(--surface);border:1px solid var(--border)">
    <tr style="background:var(--surface-elev)">
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Parcela</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Vencimento</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Valor previsto</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Situação</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Pago em / valor / forma</th>
      <th style="padding:10px;border-bottom:1px solid var(--border);text-align:left">Ações</th>
    </tr>

    <?php foreach ($installments as $inst): ?>
      <?php
        $atrasada = $inst->isOverdue();
        $corSituacao = $inst->status === 'PAGO' ? '#16a34a' : ($atrasada ? '#dc2626' : 'var(--fg)');
        $labelSituacao = $inst->status === 'PAGO' ? 'Pago' : ($atrasada ? 'Atrasada' : 'Pendente');
      ?>
      <tr>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= (int)$inst->installmentNumber ?>/<?= count($installments) ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)"><?= date('d/m/Y', strtotime($inst->dueDate)) ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border)">R$ <?= number_format($inst->amount, 2, ',', '.') ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border);color:<?= $corSituacao ?>;font-weight:600;"><?= $labelSituacao ?></td>
        <td style="padding:10px;border-bottom:1px solid var(--border);font-size:13px;color:var(--muted)">
          <?php if ($inst->status === 'PAGO'): ?>
            <?= $inst->paidAt ? date('d/m/Y', strtotime($inst->paidAt)) : '—' ?>
            — R$ <?= number_format($inst->paidAmount ?? $inst->amount, 2, ',', '.') ?>
            <?= $inst->paymentMethod ? '(' . htmlspecialchars($inst->paymentMethod) . ')' : '' ?>
          <?php elseif (!empty($inst->infinitepayLink)): ?>
            <div style="display:flex;gap:6px;align-items:center;">
              <input type="text" readonly value="<?= htmlspecialchars($inst->infinitepayLink) ?>"
                     id="link_<?= (int)$inst->id ?>"
                     style="width:160px;padding:4px 6px;font-size:12px;border-radius:4px;border:1px solid var(--border);background:var(--surface);color:var(--fg);">
              <button type="button" class="btn" style="padding:4px 8px;" onclick="copiarLink(<?= (int)$inst->id ?>)">
                <i data-lucide="copy"></i>
              </button>
            </div>
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
        <td style="padding:10px;border-bottom:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;">
          <?php if ($inst->status === 'PAGO'): ?>
            <a class="btn"
               href="<?= BASE_URL ?>/?controller=contract&action=unmarkInstallmentPaid&installment_id=<?= (int)$inst->id ?>&contract_id=<?= (int)$contract->id ?>"
               onclick="return confirm('Desfazer o pagamento desta parcela?');">
              <i data-lucide="undo-2"></i> Desfazer
            </a>
          <?php else: ?>
            <?php if (in_array($contract->paymentCondition, ['PIX', 'CARTAO'], true)): ?>
              <form method="post" action="<?= BASE_URL ?>/?controller=contract&action=generateCharge" style="display:inline;">
                <input type="hidden" name="contract_id" value="<?= (int)$contract->id ?>">
                <input type="hidden" name="installment_id" value="<?= (int)$inst->id ?>">
                <button type="submit" class="btn">
                  <i data-lucide="link"></i> <?= $inst->infinitepayLink ? 'Gerar novo link' : 'Gerar cobrança' ?>
                </button>
              </form>
            <?php endif; ?>
            <button type="button" class="btn btn-primary" onclick="abrirPagamento(<?= (int)$inst->id ?>, <?= $inst->amount ?>)">
              <i data-lucide="check"></i> Marcar como pago
            </button>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<div style="margin-top:16px;">
  <a href="<?= BASE_URL ?>/?controller=contract&action=index" class="btn">
    <i data-lucide="arrow-left"></i> Voltar
  </a>
  <a href="<?= BASE_URL ?>/?controller=contract&action=print&id=<?= (int)$contract->id ?>" class="btn" target="_blank">
    <i data-lucide="printer"></i> Ver contrato
  </a>
</div>

<!-- Modal simples de confirmação de pagamento -->
<div id="modalPagamento" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
  <form method="post" action="<?= BASE_URL ?>/?controller=contract&action=markInstallmentPaid"
        style="background:var(--surface);border-radius:12px;padding:24px;max-width:400px;width:90%;border:1px solid var(--border);">
    <h3 style="margin-top:0;">Registrar pagamento</h3>
    <input type="hidden" name="contract_id" value="<?= (int)$contract->id ?>">
    <input type="hidden" name="installment_id" id="modal_installment_id">

    <label style="display:block;margin-bottom:12px;">
      <span style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px;">Data do pagamento</span>
      <input type="date" name="paid_at" value="<?= date('Y-m-d') ?>" required
             style="width:100%;padding:8px;border-radius:6px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    </label>

    <label style="display:block;margin-bottom:12px;">
      <span style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px;">Valor pago (R$)</span>
      <input type="text" name="paid_amount" id="modal_paid_amount" required
             style="width:100%;padding:8px;border-radius:6px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
    </label>

    <label style="display:block;margin-bottom:16px;">
      <span style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px;">Forma de pagamento</span>
      <select name="payment_method"
              style="width:100%;padding:8px;border-radius:6px;border:1px solid var(--border);background:var(--surface-elev);color:var(--fg);">
        <option value="Pix">Pix</option>
        <option value="Cartão">Cartão</option>
        <option value="Boleto">Boleto</option>
        <option value="Dinheiro">Dinheiro</option>
        <option value="Outro">Outro</option>
      </select>
    </label>

    <div style="display:flex;gap:8px;">
      <button type="submit" class="btn btn-primary">Confirmar pagamento</button>
      <button type="button" class="btn" onclick="fecharPagamento()">Cancelar</button>
    </div>
  </form>
</div>

<script>
  function abrirPagamento(installmentId, valor) {
    document.getElementById('modal_installment_id').value = installmentId;
    document.getElementById('modal_paid_amount').value = valor.toFixed(2).replace('.', ',');
    document.getElementById('modalPagamento').style.display = 'flex';
  }
  function fecharPagamento() {
    document.getElementById('modalPagamento').style.display = 'none';
  }

  function copiarLink(installmentId) {
    const input = document.getElementById('link_' + installmentId);
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
      alert('Link copiado! Agora é só enviar para o paciente.');
    }).catch(() => {
      document.execCommand('copy'); // navegadores mais antigos
      alert('Link copiado!');
    });
  }
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
