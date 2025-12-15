<?php include __DIR__ . '/../partials/header.php'; ?>

<style>
  .appt-actions{ display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }

  .appt-btn{
    display:inline-flex; align-items:center; justify-content:center;
    padding:6px 12px; border-radius:999px;
    border:1px solid var(--border);
    background:var(--surface-elev);
    color:var(--fg);
    font-size:.82rem; font-weight:600;
    text-decoration:none;
    transition: filter .12s ease, transform .05s ease, opacity .12s ease;
  }
  .appt-btn:hover{ filter:brightness(.98); }
  .appt-btn:active{ transform: translateY(1px); }

  .appt-btn-primary{
    background:var(--primary);
    border-color:transparent;
    color:var(--on-primary);
  }

  .appt-btn-danger{
    background: rgba(239, 68, 68, 0.14);
    border-color: rgba(239, 68, 68, 0.55);
    color: rgba(239, 68, 68, 1);
  }
  .appt-btn-danger:hover{
    background: rgba(239, 68, 68, 0.18);
    border-color: rgba(239, 68, 68, 0.75);
  }

  .appt-btn-muted{
    background: transparent;
  }

  .appt-btn-disabled{
    opacity: .45;
    cursor:not-allowed;
    pointer-events:none;
  }
</style>


<?php
// Gera slots de horário de 08:00 até 18:00 (intervalo de 30 min)
$start    = new DateTime($date . ' 08:00');
$end      = new DateTime($date . ' 18:00');
$interval = new DateInterval('PT30M');

$timeSlots = [];
for ($t = clone $start; $t <= $end; $t->add($interval)) {
    $timeSlots[] = $t->format('H:i');
}

// Organiza os agendamentos por horário (HH:MM)
$appointmentsByTime = [];
foreach ($appointments as $a) {
    $timeKey = (new DateTime($a['start_datetime']))->format('H:i');
    $appointmentsByTime[$timeKey][] = $a;
}

// Helper para label de tipo
function nh_renderAppointmentTypeLabel(string $type): string
{
    return match ($type) {
        'PRIMEIRA_CONSULTA'      => 'Primeira Consulta',
        'RETORNO'                => 'Retorno',
        'AVALIACAO_CORPORAL'     => 'Avaliação Corporal',
        'ORIENTACAO_NUTRICIONAL' => 'Orientação Nutricional',
        default                  => $type,
    };
}

// Helper para badge de status
function nh_renderStatusBadge(string $status): string
{
    $label = match ($status) {
        'PENDENTE'   => 'Pendente',
        'CONFIRMADO' => 'Confirmado',
        'CONCLUIDO'  => 'Concluído',
        'CANCELADO'  => 'Cancelado',
        default      => $status,
    };

    // classes de cor – você pode integrar com seu CSS se quiser
    $class = match ($status) {
        'PENDENTE'   => 'badge-warning',
        'CONFIRMADO' => 'badge-success',
        'CONCLUIDO'  => 'badge-info',
        'CANCELADO'  => 'badge-danger',
        default      => 'badge-muted',
    };

    return "<span class=\"badge {$class}\" style=\"
        display:inline-flex;
        align-items:center;
        padding:2px 8px;
        border-radius:999px;
        font-size:0.75rem;
        border:1px solid var(--border);
        background:var(--surface-elev);
    \">{$label}</span>";
}
?>

<h2 style="margin-top:8px;margin-bottom:4px;">Agenda de Pacientes</h2>
<p style="margin:0 0 12px 0;color:var(--muted);font-size:0.95rem;">
  Visualização diária da agenda por horário comercial.
</p>

<div class="card" style="margin-top:8px;">
  <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <input type="hidden" name="controller" value="appointment">
    <input type="hidden" name="action" value="calendar">

    <div style="display:flex;flex-direction:column;gap:4px;">
      <label for="date" style="font-size:0.9rem;color:var(--muted);">
        Data
      </label>
      <input
        type="date"
        id="date"
        name="date"
        value="<?= htmlspecialchars($date) ?>"
        style="padding:8px 10px;border-radius:4px;border:1px solid var(--border);
               background:var(--surface);color:var(--fg);min-width:160px;"
      >
    </div>

    <div style="display:flex;flex-direction:column;gap:4px;">
      <label for="nutritionist_id" style="font-size:0.9rem;color:var(--muted);">
        Nutricionista
      </label>
      <select
        id="nutritionist_id"
        name="nutritionist_id"
        style="padding:8px 10px;border-radius:4px;border:1px solid var(--border);
               background:var(--surface);color:var(--fg);min-width:220px;"
      >
        <option value="">Todos</option>
        <?php foreach ($nutritionists as $n): ?>
          <option
            value="<?= (int)$n->id ?>"
            <?= $selectedNutritionist == $n->id ? 'selected' : '' ?>
          >
            <?= htmlspecialchars($n->name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn"
            style="padding:8px 14px;border-radius:999px;border:1px solid var(--border);
                   background:var(--surface-elev);color:var(--fg);">
      Filtrar
    </button>

    <a href="/nutrihealth/public/?controller=appointment&action=calendar"
       class="btn"
       style="padding:8px 14px;border-radius:999px;border:none;color:var(--primary);font-size:0.9rem;">
      Hoje
    </a>
  </form>
</div>

<div class="card" style="margin-top:16px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
    <h3 style="margin:0;font-size:1rem;">
      Agenda do dia <?= (new DateTime($date))->format('d/m/Y') ?>
    </h3>
    <span style="font-size:0.9rem;color:var(--muted);">
      Horário comercial das 08:00 às 18:00
    </span>
  </div>

  <div style="overflow-x:auto;margin-top:8px;">
    <table style="width:100%;border-collapse:collapse;font-size:0.95rem;background:var(--surface);border:1px solid var(--border);">
      <thead>
        <tr style="background:var(--surface-elev);">
          <th style="text-align:left;padding:8px 10px;border-bottom:1px solid var(--border);width:90px;">
            Horário
          </th>
          <th style="text-align:left;padding:8px 10px;border-bottom:1px solid var(--border);">
            Agendamentos
          </th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($timeSlots as $slot): ?>
        <?php
          $slotAppointments = $appointmentsByTime[$slot] ?? [];
          $hasAppointments  = count($slotAppointments) > 0;
        ?>
        <tr>
          <td style="padding:8px 10px;border-bottom:1px solid var(--border);
                     white-space:nowrap;color:var(--muted);font-weight:500;">
            <?= $slot ?>
          </td>
          <td style="padding:8px 10px;border-bottom:1px solid var(--border);">
            <?php if (!$hasAppointments): ?>
              <span style="font-size:0.85rem;color:var(--muted);">
                Livre
              </span>
            <?php else: ?>
              <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($slotAppointments as $a): ?>
                  <div
                    style="
                      padding:10px 12px;
                      border-radius:8px;
                      background:var(--surface-elev);
                      border:1px solid var(--border);
                      min-width:240px;
                      display:flex;
                      flex-direction:column;
                      gap:4px;
                    "
                  >
                    <div style="display:flex;justify-content:space-between;gap:8px;">
                      <strong style="font-size:0.95rem;">
                        <?= htmlspecialchars($a['patient_name']) ?>
                      </strong>
                      <?= nh_renderStatusBadge($a['status']) ?>
                    </div>
                    <div style="font-size:0.85rem;color:var(--muted);">
                      <?= nh_renderAppointmentTypeLabel($a['type']) ?>
                      <?php if (!empty($a['nutritionist_name'])): ?>
                        · Nutri: <?= htmlspecialchars($a['nutritionist_name']) ?>
                      <?php endif; ?>
                    </div>
                    
                    <div class="appt-actions">
                    <?php
                      $status = $a['status'] ?? '';
                      $locked = ($status === 'CONCLUIDO'); // ajuste se seu status for 'Concluído' ou outro
                    ?>

                    <a class="appt-btn appt-btn-muted <?= $locked ? 'appt-btn-disabled' : '' ?>"
                      <?= $locked ? 'aria-disabled="true"' : 'href="/nutrihealth/public/?controller=appointment&action=edit&id='.(int)$a['id'].'"' ?>>
                      Editar
                    </a>

                    <a class="appt-btn appt-btn-danger <?= $locked ? 'appt-btn-disabled' : '' ?>"
                      href="#"
                      <?= $locked ? 'aria-disabled="true"' : 'onclick="return confirmDelete('.(int)$a['id'].');"' ?>>
                      <i data-lucide="trash-2"></i>
                      Excluir
                    </a>

                    <?php if (empty($a['has_consultation'])): ?>
                      <a class="appt-btn appt-btn-primary"
                        href="/nutrihealth/public/?controller=consultation&action=create&appointment_id=<?= (int)$a['id'] ?>">
                        Registrar Consulta
                      </a>
                    <?php else: ?>
                      <a class="appt-btn"
                        href="/nutrihealth/public/?controller=consultation&action=view&appointment_id=<?= (int)$a['id'] ?>">
                        Visualizar Consulta
                      </a>
                    <?php endif; ?>
                  </div>

                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  function confirmDelete(id) {
    Swal.fire({
      title: 'Tem certeza de excluir este agendamento?',
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
          `/nutrihealth/public/?controller=appointment&action=delete&id=${id}`;
        }
    });
  }
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
