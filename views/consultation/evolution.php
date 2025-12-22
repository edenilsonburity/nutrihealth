<?php include __DIR__ . '/../partials/header.php'; ?>

<style>
  /*
    Correção de "scroll descendo" e gráficos intermitentes:
    Chart.js (responsive + maintainAspectRatio:false) precisa de um contêiner com altura estável.
    Não use atributo height no canvas; controle a altura no contêiner.
  */
  .chart-card{
    padding:14px;
    border:1px solid var(--border);
    border-radius:12px;
    background:var(--surface);
  }
  .chart-title{ margin:0 0 10px; }
  .chart-box{ position:relative; height:260px; width:100%; }
  .chart-box.tall{ height:320px; }
  @media (max-width: 768px){
    .chart-box{ height:220px; }
    .chart-box.tall{ height:280px; }
  }

  /* Impressão */
  @media print {
    .no-print { display: none !important; }
    .chart-card { break-inside: avoid; page-break-inside: avoid; }
    body { background: #fff !important; }
  }
</style>

<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;margin-top:8px">
  <div>
    <h2 style="margin:0">Evolução do Paciente</h2>
    <div style="color:var(--muted);margin-top:6px;display:flex;flex-wrap:wrap;gap:10px">
      <span><strong style="color:var(--fg)">Paciente:</strong>
        <?= htmlspecialchars($patient->fullName ?? $patient->namePatient ?? $patient->name_patient ?? '', ENT_QUOTES, 'UTF-8') ?>
      </span>

      <?php if (!empty($patient->birthDate)): ?>
        <span>•</span>
        <span><strong style="color:var(--fg)">Nascimento:</strong>
          <?= htmlspecialchars(date('d/m/Y', strtotime($patient->birthDate)), ENT_QUOTES, 'UTF-8') ?>
        </span>
      <?php endif; ?>

      <?php if (isset($patientAge) && $patientAge !== null): ?>
        <span>•</span>
        <span><strong style="color:var(--fg)">Idade:</strong>
          <?= (int)$patientAge ?> anos
        </span>
      <?php endif; ?>
    </div>
  </div>

  <div class="no-print" style="display:flex;gap:8px;flex-wrap:wrap">
    <?php
      $from = $from ?? 'list';
      $backUrl  = ($from === 'agenda')
        ? '/nutrihealth/public/?controller=appointment&action=index'
        : '/nutrihealth/public/?controller=consultation&action=index';
      $backText = ($from === 'agenda') ? 'Voltar para Agenda' : 'Voltar para Consultas';
    ?>

    <a class="btn" href="<?= $backUrl ?>">
      <i data-lucide="arrow-left"></i> <?= $backText ?>
    </a>

    <button type="button" class="btn btn-primary" onclick="window.print()">
      <i data-lucide="printer"></i> Imprimir / PDF
    </button>
  </div>
</div>

<?php if (empty($labels)): ?>
  <div style="margin-top:18px;padding:16px;border:1px solid var(--border);border-radius:12px;background:var(--surface)">
    <strong>Nenhum dado encontrado.</strong>
    <p style="margin:8px 0 0;color:var(--muted)">O paciente ainda não possui consultas registradas com medidas.</p>
  </div>
<?php else: ?>

  <div style="display:grid;grid-template-columns:1fr;gap:14px;margin-top:18px">
    <div class="chart-card">
      <h3 class="chart-title">Peso (kg)</h3>
      <div class="chart-box"><canvas id="chartWeight"></canvas></div>
    </div>

    <div class="chart-card">
      <h3 class="chart-title">IMC</h3>
      <div class="chart-box"><canvas id="chartBMI"></canvas></div>
    </div>

    <div class="chart-card">
      <h3 class="chart-title">Gordura corporal (%)</h3>
      <div class="chart-box"><canvas id="chartBodyFat"></canvas></div>
    </div>

    <div class="chart-card">
      <h3 class="chart-title">Dobras cutâneas (mm)</h3>
      <div class="chart-box tall"><canvas id="chartFolds"></canvas></div>
      <div style="color:var(--muted);font-size:13px;margin-top:8px">Dica: você pode ocultar/mostrar séries clicando na legenda.</div>
    </div>

    <div class="chart-card">
      <h3 class="chart-title">Circunferências (cm)</h3>
      <div class="chart-box tall"><canvas id="chartCirc"></canvas></div>
      <div style="color:var(--muted);font-size:13px;margin-top:8px">Dica: você pode ocultar/mostrar séries clicando na legenda.</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
    const series = <?= json_encode($series, JSON_UNESCAPED_UNICODE) ?>;

    // Paleta simples (sem depender de tema). Chart.js usa cores por dataset.
    const colors = [
      '#22c55e', '#ef4444', '#3b82f6', '#f59e0b', '#a855f7', '#14b8a6',
      '#e11d48', '#0ea5e9', '#84cc16', '#f97316', '#8b5cf6', '#06b6d4'
    ];

    function makeLineChart(canvasId, datasets, yTitle) {
      const canvas = document.getElementById(canvasId);
      if (!canvas) return;

      // Evita duplicar instâncias caso o DOM seja reprocessado
      if (canvas.__chartInstance) {
        try { canvas.__chartInstance.destroy(); } catch (e) {}
      }

      const chart = new Chart(canvas, {
        type: 'line',
        data: { labels, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: true,
          resizeDelay: 100,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: true },
            tooltip: { enabled: true }
          },
          scales: {
            x: { title: { display: true, text: 'Data da consulta' } },
            y: { title: { display: !!yTitle, text: yTitle || '' }, beginAtZero: false }
          }
        }
      });

      canvas.__chartInstance = chart;
    }

    // Peso
    makeLineChart('chartWeight', [
      {
        label: 'Peso (kg)',
        data: series.weight_kg,
        borderColor: colors[0],
        backgroundColor: colors[0],
        tension: 0.25,
        spanGaps: true
      }
    ], 'kg');

    // IMC
    makeLineChart('chartBMI', [
      {
        label: 'IMC',
        data: series.bmi,
        borderColor: colors[2],
        backgroundColor: colors[2],
        tension: 0.25,
        spanGaps: true
      }
    ], '');

    // Gordura corporal
    makeLineChart('chartBodyFat', [
      {
        label: '% Gordura',
        data: series.body_fat_percent,
        borderColor: colors[3],
        backgroundColor: colors[3],
        tension: 0.25,
        spanGaps: true
      }
    ], '%');

    // Dobras
    const foldsKeys = [
      ['triceps_mm', 'Tríceps'],
      ['subscapular_mm', 'Subescapular'],
      ['suprailiac_mm', 'Suprailíaca'],
      ['abdominal_mm', 'Abdominal'],
      ['thigh_mm', 'Coxa'],
      ['calf_mm', 'Panturrilha']
    ];
    const foldsDatasets = foldsKeys.map((k, i) => ({
      label: k[1] + ' (mm)',
      data: series[k[0]],
      borderColor: colors[(i + 4) % colors.length],
      backgroundColor: colors[(i + 4) % colors.length],
      tension: 0.25,
      spanGaps: true
    }));
    makeLineChart('chartFolds', foldsDatasets, 'mm');

    // Circunferências
    const circKeys = [
      ['waist_circ_cm', 'Cintura'],
      ['hip_circ_cm', 'Quadril'],
      ['arm_circ_cm', 'Braço'],
      ['thigh_circ_cm', 'Coxa'],
      ['calf_circ_cm', 'Panturrilha']
    ];
    const circDatasets = circKeys.map((k, i) => ({
      label: k[1] + ' (cm)',
      data: series[k[0]],
      borderColor: colors[(i + 1) % colors.length],
      backgroundColor: colors[(i + 1) % colors.length],
      tension: 0.25,
      spanGaps: true
    }));
    makeLineChart('chartCirc', circDatasets, 'cm');

    if (window.lucide) lucide.createIcons();
  </script>

<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
