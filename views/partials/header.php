<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>nutrihealth</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      color-scheme: dark light;
      --bg:#0f172a; --fg:#e5e7eb; --muted:#9ca3af;
      --surface:#0b1020; --surface-elev:#111827; --on-surface:var(--fg);
      --primary:#22c55e; --on-primary:#052e16;
      --danger:#dc2626; --on-danger:#ffffff;
      --hover:#1f2937; --border:#1f2937;
      --sidebar-w:260px; --sidebar-w-collapsed:76px; --topbar-h:56px;
    }
    @media (prefers-color-scheme: light) {
      :root:not(.theme-dark):not(.theme-light) {
        --bg:#f9fafb; --fg:#111827; --muted:#6b7280;
        --surface:#ffffff; --surface-elev:#ffffff; --on-surface:#111827;
        --primary:#16a34a; --on-primary:#052e16;
        --danger:#dc2626; --on-danger:#ffffff;
        --hover:#e5e7eb; --border:#d1d5db;
      }
    }
    .theme-light {
      --bg:#f9fafb; --fg:#111827; --muted:#6b7280;
      --surface:#ffffff; --surface-elev:#ffffff; --on-surface:#111827;
      --primary:#16a34a; --on-primary:#052e16;
      --danger:#dc2626; --on-danger:#ffffff;
      --hover:#e5e7eb; --border:#d1d5db;
    }
    *{box-sizing:border-box}
    html,body{margin:0;padding:0;height:100%;background:linear-gradient(180deg,var(--surface) 0%, var(--bg) 100%);color:var(--fg);font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif}
    a{color:inherit;text-decoration:none}
    i[data-lucide]{width:18px;height:18px;display:inline-block;vertical-align:middle}
    .layout{display:flex;min-height:100dvh}
    aside.sidebar{position:fixed;inset:0 auto 0 0;width:var(--sidebar-w);background:var(--surface-elev);border-right:1px solid var(--border);padding:14px 12px;display:flex;flex-direction:column;gap:8px;transform:translateX(-100%);transition:transform .25s ease,width .25s ease;z-index:60;}
    .sidebar.open{ transform:translateX(0) }
    .sidebar .brand{display:flex;align-items:center;gap:10px;margin-bottom:6px;font-weight:700}
    .sidebar .badge{font-size:12px;background:rgba(34,197,94,.12);color:#065f46;padding:2px 8px;border-radius:999px;border:1px solid rgba(34,197,94,.25)}
    .nav-group{margin-top:8px;display:flex;flex-direction:column;gap:6px}
    .nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;background:transparent;border:1px solid transparent;color:var(--on-surface);}
    .nav-item:hover{background:var(--hover);border-color:var(--border)}
    .nav-item.active{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.25);color:#065f46}
    .nav-item .label{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    header.topbar{position:fixed;top:0;left:0;right:0;height:var(--topbar-h);display:flex;align-items:center;gap:10px;background:var(--surface-elev);border-bottom:1px solid var(--border);padding:0 12px;z-index:50;}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:36px;padding:0 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface);cursor:pointer}
    .btn:hover{background:var(--hover)}
    .btn-primary{background:var(--primary);border-color:transparent;color:var(--on-primary);font-weight:600}
    .btn-primary:hover{filter:brightness(0.95)}
    .btn-danger{background:var(--danger);border-color:transparent;color:var(--on-danger);font-weight:600}
    .btn-danger:hover{filter:brightness(0.95)}
    main.content{flex:1;width:100%;padding:calc(var(--topbar-h) + 16px) 16px 24px}
    .page-head{margin:6px 0 12px;display:flex;align-items:center;gap:12px}
    .page-title{font-size:22px;font-weight:700}
    .page-sub{font-size:14px;color:var(--muted)}
    .overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);opacity:0;pointer-events:none;transition:opacity .2s ease;z-index:55;}
    .overlay.show{opacity:1;pointer-events:auto}
    @media (min-width: 1024px){
      aside.sidebar{transform:none;}
      .overlay{display:none}
      .layout{padding-left:var(--sidebar-w)}
      body.sidebar-collapsed .layout{padding-left:var(--sidebar-w-collapsed)}
      body.sidebar-collapsed aside.sidebar{width:var(--sidebar-w-collapsed)}
      body.sidebar-collapsed .nav-item .label{display:none}
    }
  </style>
</head>
<body>
  <div class="layout">
    <aside class="sidebar" id="sidebar">
      <div class="brand">
        <i data-lucide="leaf"></i>
        <span class="label">nutrihealth</span>
        <span class="badge label">v1</span>
      </div>
      <nav class="nav-group">
        <a class="nav-item <?= ($_GET['action']??'index')==='index'?'active':'' ?>" href="/nutrihealth/public/?action=index"><i data-lucide="users"></i><span class="label">Usuários</span></a>        
        <a class="nav-item" href="#" onclick="Swal.fire('Em breve','Módulo de relatórios','info')"><i data-lucide="bar-chart-2"></i><span class="label">Relatórios</span></a>
      </nav>
    </aside>
    <div class="overlay" id="overlay"></div>
    <header class="topbar">
      <button class="btn" id="btnSidebar" aria-label="Alternar menu"><i data-lucide="menu"></i><span class="label">Menu</span></button>
      <div style="flex:1"></div>
      <button class="btn" id="btnTheme" title="Tema"><i data-lucide="sun"></i></button>
      <a class="btn btn-primary" href="/nutrihealth/public/?action=create"><i data-lucide="plus"></i> Novo</a>
    </header>
    <main class="content">
      <div class="page-head"><i data-lucide="layout-grid"></i><div><div class="page-title">Usuários</div><div class="page-sub">Gestão de usuários do sistema</div></div></div>
