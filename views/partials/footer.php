    </main>
  </div>
  <script>
    lucide.createIcons();
    const sidebar=document.getElementById('sidebar'),overlay=document.getElementById('overlay'),btnSidebar=document.getElementById('btnSidebar');
    const btnTheme=document.getElementById('btnTheme');
    function isDesktop(){return window.matchMedia('(min-width:1024px)').matches;}
    function openMobile(){sidebar.classList.add('open');overlay.classList.add('show');document.body.classList.add('sidebar-open');}
    function closeMobile(){sidebar.classList.remove('open');overlay.classList.remove('show');document.body.classList.remove('sidebar-open');}
    btnSidebar.addEventListener('click',()=>{if(isDesktop()){document.body.classList.toggle('sidebar-collapsed');}else{if(sidebar.classList.contains('open'))closeMobile();else openMobile();}});
    overlay.addEventListener('click',closeMobile);
    sidebar.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{if(!isDesktop())closeMobile();}));
    // Theme toggle
    const THEME_KEY='nh_theme'; const mm=window.matchMedia('(prefers-color-scheme: dark)');
    function applyTheme(pref){
      document.documentElement.classList.remove('theme-dark','theme-light');
      if(pref==='dark'){document.documentElement.classList.add('theme-dark');btnTheme.innerHTML='<i data-lucide="moon"></i>';}
      else if(pref==='light'){document.documentElement.classList.add('theme-light');btnTheme.innerHTML='<i data-lucide="sun"></i>';}
      else {btnTheme.innerHTML=mm.matches?'<i data-lucide="moon-star"></i>':'<i data-lucide="sun-medium"></i>';}
      lucide.createIcons();
    }
    function cycleTheme(){const cur=localStorage.getItem(THEME_KEY)||'system';const next=cur==='system'?'dark':(cur==='dark'?'light':'system');localStorage.setItem(THEME_KEY,next);applyTheme(next);}
    btnTheme.addEventListener('click',cycleTheme);
    mm.addEventListener('change',()=>{if((localStorage.getItem(THEME_KEY)||'system')==='system')applyTheme('system');});
    (function init(){applyTheme(localStorage.getItem(THEME_KEY)||'system');})();
    // SweetAlert via ?msg= e limpar URL
    (function alertsFromQuery(){const usp=new URLSearchParams(location.search);const msg=usp.get('msg');if(msg==='success'){Swal.fire({icon:'success',title:'Sucesso!',text:'Registro Salvo com Sucesso.'});}else if(msg==='deleted'){Swal.fire({icon:'info',title:'Excluído!',text:'Registro Removido com Sucesso.'});}if(msg)history.replaceState(null,'',location.pathname);})(); 
  </script>
</body>
</html>