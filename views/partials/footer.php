    </main> <!-- fecha .content -->
  </div>   <!-- fecha .main -->
</div>     <!-- fecha .layout -->

<footer style="
    text-align:center;
    padding:18px;
    font-size:13px;
    color:var(--muted);
    border-top:1px solid var(--border);
    background:var(--surface-elev);
    backdrop-filter:blur(10px);
    margin-top:20px;">
  <div style="margin-bottom:4px; color:var(--fg); font-weight:500;">
    NutriHealth v1.0 © <?= date('Y') ?>
  </div>
</footer>

<script src="https://unpkg.com/imask"></script>

<script>
  // ===== THEME ENGINE (global) =====
  const THEME_KEY = 'nh_theme';
  const mm       = window.matchMedia('(prefers-color-scheme: dark)');

  function applyTheme(pref) {
    const root    = document.documentElement;
    const btnTheme = document.getElementById('btnTheme');

    root.classList.remove('theme-dark', 'theme-light');

    let effective = pref;
    if (pref === 'system') {
      effective = mm.matches ? 'dark' : 'light';
    }
    root.classList.add(effective === 'dark' ? 'theme-dark' : 'theme-light');

    if (btnTheme) {
      if (pref === 'system') {
        btnTheme.innerHTML = mm.matches
          ? '<i data-lucide="moon-star"></i>'
          : '<i data-lucide="sun-medium"></i>';
      } else if (pref === 'dark') {
        btnTheme.innerHTML = '<i data-lucide="moon"></i>';
      } else {
        btnTheme.innerHTML = '<i data-lucide="sun"></i>';
      }
    }

    if (window.lucide) {
      lucide.createIcons();
    }
  }

  function cycleTheme() {
    const cur  = localStorage.getItem(THEME_KEY) || 'light';
    const next = cur === 'light' ? 'dark' : cur === 'dark' ? 'system' : 'light';
    localStorage.setItem(THEME_KEY, next);
    applyTheme(next);
  }

  mm.addEventListener('change', () => {
    const pref = localStorage.getItem(THEME_KEY) || 'light';
    if (pref === 'system') applyTheme('system');
  });

  (function initTheme() {
    const pref = localStorage.getItem(THEME_KEY) || 'light';
    applyTheme(pref);
  })();

  // ===== LAYOUT: sidebar, overlay, eventos =====
  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) {
      lucide.createIcons();
    }

    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('overlay');
    const btnOpen  = document.getElementById('btnSidebar');
    const btnClose = document.getElementById('btnSidebarClose');
    const btnTheme = document.getElementById('btnTheme');

    function isDesktop() {
      return window.matchMedia('(min-width:1024px)').matches;
    }

    function openMobile() {
      sidebar.classList.add('open');
      overlay.classList.add('visible');
      document.body.classList.add('sidebar-open');
    }

    function closeMobile() {
      sidebar.classList.remove('open');
      overlay.classList.remove('visible');
      document.body.classList.remove('sidebar-open');
    }

    btnOpen && btnOpen.addEventListener('click', () => {
      if (isDesktop()) {
        document.documentElement.classList.toggle('sidebar-collapsed');
      } else {
        sidebar.classList.contains('open') ? closeMobile() : openMobile();
      }
    });

    btnClose && btnClose.addEventListener('click', closeMobile);
    overlay && overlay.addEventListener('click', closeMobile);

    if (sidebar) {
      sidebar.querySelectorAll('a').forEach(a =>
        a.addEventListener('click', () => {
          if (!isDesktop()) closeMobile();
        })
      );
    }

    // botão de tema usa a engine global
    btnTheme && btnTheme.addEventListener('click', function () {
      cycleTheme();
    });

    // ===== SweetAlert via ?msg= =====
    (function alertsFromQuery() {
      const usp = new URLSearchParams(location.search);
      const msg = usp.get('msg');
      if (!msg) return;

      const map = {
        created:  { icon: 'success', title: 'Registro criado com sucesso!' },
        updated:  { icon: 'success', title: 'Registro atualizado com sucesso!' },
        deleted:  { icon: 'success', title: 'Registro excluído com sucesso!' },
        consultation_created: {icon: 'success', title: 'Consulta registrada com sucesso!' },
        appointment_created: {icon: 'success', title: 'Agendamento criado com sucesso!' },
        ppointment_updated: {icon: 'success', title: 'Agendamento atualizado com sucesso!'},
        no_consultation: {icon: 'warning', title: 'Nenhuma consulta encontrada para este agendamento.'},
        notfound: { icon: 'warning', title: 'Registro não encontrado.' }
      };

      const cfg = map[msg] || { icon: 'info', title: msg };
      Swal.fire(cfg);

      history.replaceState(null, '', location.pathname);
    })();
  });

  document.addEventListener("DOMContentLoaded", () => {   
      // Telefone fixo (formato 10 dígitos)
      const phoneInput = document.querySelector("input[name='phone']");
      if (phoneInput) {
        IMask(phoneInput, {
              mask: "(00) 0000-0000"
        });
      }

      // Celular (formato 11 dígitos com 9)
      const cellphoneInput = document.querySelector("input[name='cellphone']");
      if (cellphoneInput) {
        IMask(cellphoneInput, {
              mask: "(00) 00000-0000"
        });
      }
  });
</script>

</body>
</html>
