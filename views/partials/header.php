<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$userName          = $_SESSION['user_name'] ?? null;
$currentController = $_GET['controller'] ?? 'user';

// Título e subtítulo dinâmicos da página
switch ($currentController) {
    case 'occupation':
        $pageTitle = 'Profissões';
        $pageSub   = 'Gestão de profissões';
        break;
    case 'user':
    default:
        $pageTitle = 'Usuários';
        $pageSub   = 'Gestão de usuários';
        break;
}

// Link dinâmico para o botão "Novo"
if ($currentController === 'occupation') {
    $newLink = '/nutrihealth/public/?controller=occupation&action=create';
} else {
    $newLink = '/nutrihealth/public/?controller=user&action=create';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <!-- Aplica o tema salvo ANTES do CSS carregar -->
  <script>
    (function () {
      const key  = 'nh_theme';
      const pref = localStorage.getItem(key) || 'light'; // padrão: claro
      const root = document.documentElement;

      function setThemeClass(theme) {
        root.classList.remove('theme-dark', 'theme-light');
        if (theme === 'dark') root.classList.add('theme-dark');
        else root.classList.add('theme-light');
      }

      if (pref === 'system') {
        const mm = window.matchMedia('(prefers-color-scheme: dark)');
        setThemeClass(mm.matches ? 'dark' : 'light');
      } else {
        setThemeClass(pref);
      }
    })();
  </script>

  <meta charset="UTF-8" />
  <title>NutriHealth</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Ícones e alerts -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      color-scheme: light dark;
      --bg: #f3f4f6;
      --fg: #111827;
      --muted: #6b7280;
      --surface: #ffffff;
      --surface-elev: #ffffff;
      --on-surface: #111827;
      --primary: #16a34a;
      --on-primary: #052e16;
      --danger: #dc2626;
      --on-danger: #ffffff;
      --hover: #e5e7eb;
      --border: #d1d5db;
      --sidebar-w: 260px;
      --topbar-h: 56px;
    }

    .theme-dark {
      --bg: #0f172a;          /* NÃO totalmente preto */
      --fg: #e5e7eb;
      --muted: #9ca3af;
      --surface: #020617;
      --surface-elev: #020617;
      --on-surface: #e5e7eb;
      --primary: #22c55e;
      --on-primary: #052e16;
      --danger: #ef4444;
      --on-danger: #0b1120;
      --hover: #020617;
      --border: #1f2937;
    }

    /* Botão de tema sempre legível */
    #btnTheme i {
      color: #111827;
    }

    .theme-light .sidebar {
      background: var(--surface);
      color: var(--fg);
    }

    .theme-dark .sidebar {
      background: #0f172a;     /* seu padrão escuro */
      color: #cbd5e1; 
    }

    .theme-dark #btnTheme i {
      color: #e5e7eb !important;
      filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.7));
    }

    /* Imagens "sensíveis" ao tema ganham brilho no dark */
    .theme-dark img.theme-sensitive {
      filter: brightness(1.4) contrast(1.1);
    }

    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      background:
        radial-gradient(circle at top left, rgba(56,189,248,.18), transparent 55%),
        radial-gradient(circle at bottom right, rgba(34,197,94,.18), transparent 55%),
        var(--bg);
      color: var(--fg);
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    a { color: inherit; text-decoration: none; }
    i[data-lucide] { width: 18px; height: 18px; display: inline-block; vertical-align: middle; }

    .layout {
      display: flex;
      min-height: 100dvh;
    }

    aside.sidebar {
      position: fixed;
      inset: 0 auto 0 0;
      width: var(--sidebar-w);
      background: var(--surface-elev);
      border-right: 1px solid rgba(148,163,184,.35);
      padding: 14px 12px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      transform: translateX(0);
      transition: transform .25s ease, width .25s ease;
      z-index: 60;
    }
    .sidebar-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
      margin-bottom: 6px;
    }
    .sidebar .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      color: #e5e7eb;
    }
    .sidebar .brand-badge {
      font-size: 11px;
      padding: 2px 8px;
      border-radius: 999px;
      border: 1px solid rgba(148,163,184,.6);
      color: #cbd5f5;
    }

    .badge {
      font-size: 12px;
      border-radius: 999px;
      padding: 2px 10px;
      background: rgba(15,23,42,.7);
      border: 1px solid rgba(148,163,184,.6);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: var(--muted);
    }

    .nav-group {
      margin-top: 8px;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .nav-label {
      font-size: 11px;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--muted);
      margin: 10px 6px 4px;
    }
    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 10px;
      border-radius: 999px;
      font-size: 14px;
      color: var(--muted);
      cursor: pointer;
      border: 1px solid transparent;
    }
    .nav-item .label { flex: 1; }
    .nav-item:hover {
      background: rgba(15,23,42,.95);
      border-color: rgba(148,163,184,.4);
      color: #e5e7eb;
    }
    .nav-item.active {
      background: linear-gradient(135deg, rgba(34,197,94,.1), rgba(59,130,246,.12));
      border-color: rgba(34,197,94,.7);
      color: #bbf7d0;
    }

    .sidebar-footer {
      margin-top: auto;
      padding-top: 10px;
      border-top: 1px dashed rgba(148,163,184,.5);
      display: flex;
      flex-direction: column;
      gap: 6px;
      font-size: 12px;
      color: var(--muted);
    }
    .sidebar-footer strong { color: #e5e7eb; font-size: 13px; }

    .main {
      margin-left: var(--sidebar-w);
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      transition: margin-left .25s ease;
    }

    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(15,23,42,.6);
      backdrop-filter: blur(3px);
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s ease;
      z-index: 50;
    }
    .overlay.visible {
      opacity: 1;
      pointer-events: auto;
    }

    header.topbar {
      position: sticky;
      top: 0;
      z-index: 40;
      height: var(--topbar-h);
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 18px;      
      background: var(--surface-elev);
      border-bottom: 1px solid var(--border);
      backdrop-filter: blur(14px);
      color: var(--fg);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      padding: 6px 10px;
      border-radius: 999px;
      border: 1px solid rgba(148,163,184,.55);
      background: rgba(15,23,42,.85);
      color: #e5e7eb;
      cursor: pointer;
    }
    .btn .label { display: inline; }
    .btn i[data-lucide] { width: 16px; height: 16px; }
    .btn:hover {
      background: rgba(15,23,42,1);
      border-color: rgba(148,163,184,.9);
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary), #22c1c3);
      color: var(--on-primary);
      border-color: transparent;
      box-shadow: 0 12px 30px rgba(34,197,94,.35);
    }
    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 40px rgba(34,197,94,.35);
    }

    .btn-danger {
      background: var(--danger);
      color: var(--on-danger);
      border-color: transparent;
    }

    main.content {
      padding: 18px 18px 24px;
    }

    .page-head {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 20px;
    }
    .page-head i[data-lucide] {
      width: 28px;
      height: 28px;
      padding: 6px;
      border-radius: 999px;
      background: radial-gradient(circle at top left, rgba(34,197,94,.28), rgba(15,23,42,1));
      border: 1px solid rgba(34,197,94,.5);
      color: #bbf7d0;
    }
    .page-title {
      font-size: 18px;
      font-weight: 600;
      letter-spacing: .01em;
    }
    .page-sub {
      font-size: 13px;
      color: var(--muted);
      margin-top: 2px;
    }

    @media (max-width: 900px) {
      aside.sidebar {
        transform: translateX(-100%);
      }
      aside.sidebar.open {
        transform: translateX(0);
      }
      .main {
        margin-left: 0;
      }
    }

    @media (max-width: 640px) {
      .btn .label { display: none; }
      .page-title { font-size: 16px; }
      .page-sub { font-size: 12px; }
    }
  </style>
</head>
<body>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="brand">
        <i data-lucide="heart-pulse"></i>
        <div>
          <div>NutriHealth</div>
          <div class="brand-badge">Painel Admin</div>
        </div>
      </div>
      <button class="btn" id="btnSidebarClose" aria-label="Fechar menu">
        <i data-lucide="x"></i>
      </button>
    </div>

    <div class="nav-label">Menu</div>
    <nav class="nav-group">
      <a class="nav-item <?= ($currentController === 'user') ? 'active' : '' ?>"
         href="/nutrihealth/public/?controller=user&action=index">
        <i data-lucide="users"></i>
        <span class="label">Usuários</span>
      </a>

      <a class="nav-item <?= ($currentController === 'occupation') ? 'active' : '' ?>"
         href="/nutrihealth/public/?controller=occupation&action=index">
        <i data-lucide="briefcase"></i>
        <span class="label">Profissões</span>
      </a>

      <a class="nav-item" href="#"
         onclick="Swal.fire('Em breve','Módulo de relatórios em desenvolvimento.','info')">
        <i data-lucide="bar-chart-2"></i>
        <span class="label">Relatórios</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <?php if ($userName): ?>
        <div><strong>Sessão ativa</strong><br><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div>
      <?php else: ?>
        <div><strong>Sessão convidado</strong><br>Faça login para acessar todos os recursos.</div>
      <?php endif; ?>
      <div>NutriHealth &copy; <?= date('Y') ?></div>
    </div>
  </aside>

  <div class="overlay" id="overlay"></div>

  <div class="main">
    <header class="topbar">
      <button class="btn" id="btnSidebar" aria-label="Alternar menu">
        <i data-lucide="menu"></i><span class="label">Menu</span>
      </button>

      <div style="flex:1"></div>

      <button class="btn" id="btnTheme" title="Tema">
        <i data-lucide="sun"></i>
      </button>

      <?php if ($userName): ?>
        <span class="badge" style="margin-left:8px;margin-right:4px;">
          <i data-lucide="user"></i>
          <span><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
        </span>
      <?php endif; ?>

      <a class="btn btn-primary" href="<?= $newLink ?>" style="margin-left:8px;">
        <i data-lucide="plus"></i><span class="label">Novo</span>
      </a>

      <a class="btn btn-danger" href="/nutrihealth/public/?controller=user&action=logout" style="margin-left:8px;">
        <i data-lucide="log-out"></i><span class="label">Logout</span>
      </a>
    </header>

    <main class="content">
      <div class="page-head">
        <i data-lucide="layout-grid"></i>
        <div>
          <div class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="page-sub"><?= htmlspecialchars($pageSub, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>
