<?php
use App\Repositories\ReportRepository;

include __DIR__ . '/../partials/header.php';

$fixedTypes = [
  'PRIMEIRA_CONSULTA' => 0,
  'RETORNO' => 0,
  'AVALIACAO_CORPORAL' => 0,
  'ORIENTACAO_NUTRICIONAL' => 0,
];

foreach ($byType as $row) {
  $t = $row['type'] ?? '';
  if ($t && array_key_exists($t, $fixedTypes)) {
    $fixedTypes[$t] = (int)$row['total'];
  }
}

$max = max($fixedTypes);
$max = $max > 0 ? $max : 1;

function pill_class(string $status): string {
  return match ($status) {
    'PENDENTE' => 'pill pill-pendente',
    'CONFIRMADO' => 'pill pill-confirmado',
    'CONCLUIDO' => 'pill pill-concluido',
    'CANCELADO' => 'pill pill-cancelado',
    default => 'pill',
  };
}
?>

<style>
  .grid-4 { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 0 0 1px rgba(255,255,255,.02) inset;
  }
  .kpi .icon {
    width:34px;height:34px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    background: var(--surface-elev);
    border: 1px solid var(--border);
  }
  .value { font-size:28px; font-weight:800; margin-top:6px; color: var(--primary); }
  .label { color:var(--muted); font-size:.9rem; margin-top:4px; }

  .section-title{
    display:flex; align-items:center; gap:10px;
    font-weight:800; margin:0 0 10px 0;
  }

  .bar-row{ display:flex; align-items:center; gap:12px; margin:10px 0; }
  .bar-label{ width:220px; color:var(--fg); font-size:.92rem; }
  .bar-track{
    flex:1; height:14px; border-radius:999px;
    background: var(--surface-elev);
    border:1px solid var(--border);
    overflow:hidden;
  }
  .bar-fill{
    height:100%; border-radius:999px;
    background: var(--primary);
  }
  .bar-val{ width:26px; text-align:right; color:var(--muted); font-weight:700; }

  table{ width:100%; border-collapse:separate; border-spacing:0; }
  th, td{ padding:12px 10px; border-top:1px solid var(--border); }
  th{ color:var(--muted); font-weight:700; font-size:.85rem; text-align:left; }
  tr:first-child th{ border-top:none; }

  /* Status pill (cores inspiradas na UI do projeto; funciona no dark por usar rgba) */
  .pill{
    display:inline-flex; align-items:center;
    padding:4px 10px; border-radius:999px;
    border:1px solid var(--border);
    background: var(--surface-elev);
    font-size:.82rem; font-weight:800;
    line-height:1;
  }
  .pill-pendente{
    background: rgba(245, 158, 11, 0.14);
    border-color: rgba(245, 158, 11, 0.45);
    color: rgba(245, 158, 11, 1);
  }
  .pill-confirmado{
    background: rgba(34, 197, 94, 0.14);
    border-color: rgba(34, 197, 94, 0.45);
    color: rgba(34, 197, 94, 1);
  }
  .pill-cancelado{
    background: rgba(239, 68, 68, 0.14);
    border-color: rgba(239, 68, 68, 0.45);
    color: rgba(239, 68, 68, 1);
  }
  .pill-concluido{
    background: rgba(59, 130, 246, 0.14);
    border-color: rgba(59, 130, 246, 0.45);
    color: rgba(59, 130, 246, 1);
  }

  @media (max-width: 1100px){
    .grid-4{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .bar-label{ width:160px; }
  }
  @media (max-width: 640px){
    .grid-4{ grid-template-columns:1fr; }
    .bar-label{ width:140px; }
  }
</style>

<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px;">
  <div>
    <h1 style="margin:0;">Relatórios</h1>
    <div style="color:var(--muted);margin-top:4px;">Análise e estatísticas dos agendamentos</div>
  </div>

  <a href="/nutrihealth/public/?controller=report&action=exportCsv"
     style="display:inline-flex;align-items:center;gap:10px;
            padding:10px 14px;border-radius:10px;
            background:var(--primary);color:var(--on-primary);
            text-decoration:none;border:1px solid transparent;">
    <span data-lucide="download"></span>
    Exportar CSV
  </a>
</div>

<div class="grid-4" style="margin-top:10px;">
  <div class="card">
    <div class="kpi"><div class="icon"><span data-lucide="calendar"></span></div></div>
    <div class="value"><?= (int)$summary['total'] ?></div>
    <div class="label">Total de Agendamentos</div>
  </div>

  <div class="card">
    <div class="kpi"><div class="icon"><span data-lucide="clock"></span></div></div>
    <div class="value"><?= (int)$summary['pending'] ?></div>
    <div class="label">Pendentes</div>
  </div>

  <div class="card">
    <div class="kpi"><div class="icon"><span data-lucide="check-circle"></span></div></div>
    <div class="value"><?= (int)$summary['confirmed'] ?></div>
    <div class="label">Confirmados</div>
  </div>

  <div class="card">
    <div class="kpi"><div class="icon"><span data-lucide="x-circle"></span></div></div>
    <div class="value"><?= (int)$summary['canceled'] ?></div>
    <div class="label">Cancelados</div>
  </div>
</div>

<div class="grid-4" style="margin-top:14px;">
  <div class="card">
    <div style="color:var(--muted);font-weight:800;">Taxa de Confirmação</div>
    <div class="value"><?= (int)$summary['confirm_rate'] ?>%</div>
  </div>
  <div class="card">
    <div style="color:var(--muted);font-weight:800;">Taxa de Cancelamento</div>
    <div class="value"><?= (int)$summary['cancel_rate'] ?>%</div>
  </div>
  <div class="card">
    <div style="color:var(--muted);font-weight:800;">Agendamentos Este Mês</div>
    <div class="value"><?= (int)$summary['month_total'] ?></div>
  </div>
  <div class="card">
    <div style="color:var(--muted);font-weight:800;">Próximos 7 Dias</div>
    <div class="value"><?= (int)$summary['next7_total'] ?></div>
  </div>
</div>

<div class="card" style="margin-top:14px;">
  <div class="section-title">
    <span data-lucide="bar-chart-2"></span>
    Tipos de Consulta
  </div>

  <?php foreach ($fixedTypes as $type => $count): ?>
    <div class="bar-row">
      <div class="bar-label"><?= htmlspecialchars(ReportRepository::typeLabel($type)) ?></div>
      <div class="bar-track">
        <div class="bar-fill" style="width:<?= (int)round(($count/$max)*100) ?>%"></div>
      </div>
      <div class="bar-val"><?= (int)$count ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card" style="margin-top:14px;">
  <div class="section-title">
    <span data-lucide="calendar-check"></span>
    Próximos Agendamentos
  </div>

  <table>
    <thead>
      <tr>
        <th>Paciente</th>
        <th>Data/Hora</th>
        <th>Tipo</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($upcoming)): ?>
        <tr><td colspan="4" style="color:var(--muted);">Nenhum agendamento nos próximos 7 dias.</td></tr>
      <?php else: ?>
        <?php foreach ($upcoming as $u): ?>
          <tr>
            <td style="font-weight:900;"><?= htmlspecialchars($u['patient_name']) ?></td>
            <td><?= date('d/m/Y \à\s H:i', strtotime($u['start_datetime'])) ?></td>
            <td><?= htmlspecialchars(ReportRepository::typeLabel($u['type'])) ?></td>
            <td><span class="<?= pill_class($u['status']) ?>"><?= htmlspecialchars(ReportRepository::statusLabel($u['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="card" style="margin-top:14px;">
  <div class="section-title">
    <span data-lucide="clock"></span>
    Agendamentos Recentes
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Paciente</th>
        <th>Data/Hora</th>
        <th>Tipo</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($recent)): ?>
        <tr><td colspan="5" style="color:var(--muted);">Sem registros recentes.</td></tr>
      <?php else: ?>
        <?php foreach ($recent as $r): ?>
          <tr>
            <td style="color:var(--muted);font-weight:800;">#<?= (int)$r['id'] ?></td>
            <td style="font-weight:900;"><?= htmlspecialchars($r['patient_name']) ?></td>
            <td><?= date('d/m/Y \à\s H:i', strtotime($r['start_datetime'])) ?></td>
            <td><?= htmlspecialchars(ReportRepository::typeLabel($r['type'])) ?></td>
            <td><span class="<?= pill_class($r['status']) ?>"><?= htmlspecialchars(ReportRepository::statusLabel($r['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
