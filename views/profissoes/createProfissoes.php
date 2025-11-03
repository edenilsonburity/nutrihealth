<?php include __DIR__ . '/../partials/headerProfissoes.php'; ?>

<h2 style="margin-top:8px;margin-bottom:20px">Modo: Nova Profissão</h2>

<?php if (!empty($error)): ?>
  <p style="color:#d32f2f;font-size:15px;margin-bottom:16px">
    O Código [<?= htmlspecialchars($old['codigo'] ?? 'X') ?>] já está cadirsado. Porvar, insira um codigo unique.
  </p>
<?php endif; ?>

<form method="post" action="?action=createProfissoes" style="max-width:700px;padding:40px;background:var(--surface);border:1px solid var(--border);border-radius:8px">
  
  <label style="display:block;margin-bottom:24px">
    <span style="font-size:18px;font-weight:600;color:#fffff;display:block;margin-bottom:8px">
      Código da Profissão <span style="color:#fffff">(*)</span>
    </span>
    <input 
      type="text" 
      name="codigo" 
      required 
      value="<?= htmlspecialchars($old['codigo'] ?? '') ?>" 
      placeholder="9999-99"
      style="width:100%;padding:12px 16px;border-radius:4px;border:1px solid #ccc;background:var(--surface);color:#fffff;font-size:16px"
    >
  </label>

  <label style="display:block;margin-bottom:30px">
    <span style="font-size:18px;font-weight:600;color:#fffff;display:block;margin-bottom:8px">
      Descrição da Profissão <span style="color:#fffff">(*)</span>
    </span>
    <input 
      type="text" 
      name="descricao_profissao" 
      required 
      value="<?= htmlspecialchars($old['descricao_profissao'] ?? '') ?>"
      style="width:100%;padding:12px 16px;border-radius:4px;border:1px solid #ccc;background:var(--surface);color:#fffff;font-size:16px"
    >
  </label>

  <div style="display:flex;gap:16px">
    <button type="submit" class="btn btn-primary" style="flex:1;background:#2b93d8;color:white;padding:14px 24px;font-size:18px;font-weight:500;border:none;border-radius:4px;cursor:pointer">
      Salvar
    </button>
    <a href="?action=indexProfissoes" class="btn" style="flex:1;background:var(--surface);color:#fffff;padding:14px 24px;font-size:18px;font-weight:500;border:1px solid var(--border);border-radius:4px;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center">
      Voltar
    </a>
  </div>

</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>