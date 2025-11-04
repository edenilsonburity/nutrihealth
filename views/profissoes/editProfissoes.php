<?php include __DIR__ . '/../partials/headerProfissoes.php'; ?>

<h2>Editar Profissão</h2>
<?php if (!empty($error)): ?><p style="color:#ef4444"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" action="?action=editProfissoes&id=<?= (int)$profissao->id ?>" style="max-width:520px;padding:16px;background:var(--surface);border:1px solid var(--border);border-radius:12px">
  <label>Código<br>
    <input type="text" name="codigo" required value="<?= htmlspecialchars($profissao->codigo) ?>" style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
  </label><br><br>
  <label>Descrição<br>
    <input type="text" name="descricao_profissao" required value="<?= htmlspecialchars($profissao->descricao_profissao) ?>" style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--surface-elev);color:var(--on-surface)">
  </label><br><br>
  <button type="submit" class="btn btn-primary">Salvar</button>
  <a href="?action=indexProfissoes" class="btn" style="margin-left:8px">Voltar</a>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
