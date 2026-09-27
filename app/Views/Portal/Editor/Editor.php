<?php
// Página standalone (sin sidebar/header del portal — el editor necesita
// todo el alto de la ventana) que embebe OnlyOffice Docs. Todas las
// variables ($configEditor, $onlyofficeUrlPublica, $fila, $volverA...) las
// arma EditorController.php justo antes de este require.
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($fila['nombre']) ?> — Portal CORE</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet" integrity="sha384-CK2SzKma4jA5H/MXDUU7i1TqZlCFaD4T01vtyDFvPlD97JQyS+IsSh1nI2EFbpyk" crossorigin="anonymous">
<link rel="stylesheet" href="<?= v('/assets/core/tokens.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/core/components.css') ?>">
<style>
  html, body { height: 100%; }
  body { display: flex; flex-direction: column; overflow: hidden; }
  .editor-barra {
    display: flex; align-items: center; gap: var(--space-3); flex: 0 0 auto;
    height: 52px; padding: 0 var(--space-4);
    border-bottom: 1px solid var(--border); background: var(--bg-surface);
  }
  .editor-nombre { min-width: 0; font-weight: var(--weight-semibold); font-size: var(--text-base); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .editor-modo { margin-left: auto; flex: 0 0 auto; }
  #editorPortalCore { flex: 1 1 auto; min-height: 0; }
</style>
</head>
<body>
  <div class="editor-barra">
    <?php if ($volverA): ?>
      <a href="<?= e($volverA) ?>" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver</a>
    <?php endif; ?>
    <span class="editor-nombre"><?= e($fila['nombre']) ?></span>
    <span class="editor-modo badge <?= $puedeEditar ? 'badge-primary' : 'badge-neutral' ?> badge-dot"><?= $puedeEditar ? 'Editando' : 'Solo lectura' ?></span>
  </div>
  <div id="editorPortalCore"></div>

  <script src="<?= e($onlyofficeUrlPublica) ?>/web-apps/apps/api/documents/api.js"></script>
  <script>
    new DocsAPI.DocEditor('editorPortalCore', <?= json_encode($configEditor, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
  </script>
</body>
</html>
