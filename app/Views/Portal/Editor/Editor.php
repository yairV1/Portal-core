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
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?= e($fila['nombre']) ?> — Portal CORE</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= v('/assets/layouts/css/paneles.css') ?>">
<style>
  html, body { height: 100%; }
  body { display: flex; flex-direction: column; overflow: hidden; }
  .editor-barra {
    display: flex; align-items: center; gap: 12px; padding: 10px 18px;
    border-bottom: 1px solid var(--color-divider); background: var(--color-bg); flex: 0 0 auto;
  }
  .editor-volver {
    display: inline-flex; align-items: center; gap: 6px; color: var(--color-text); text-decoration: none;
    font-weight: 700; font-size: 13.5px; opacity: .75; transition: opacity .15s ease;
  }
  .editor-volver:hover { opacity: 1; }
  .editor-nombre { font-weight: 800; font-size: 14px; opacity: .85; }
  .editor-modo { margin-left: auto; font-size: 11px; letter-spacing: .06em; text-transform: uppercase; font-weight: 800; opacity: .5; }
  #editorPortalCore { flex: 1 1 auto; min-height: 0; }
</style>
</head>
<body>
  <div class="editor-barra">
    <?php if ($volverA): ?>
      <a href="<?= e($volverA) ?>" class="editor-volver"><i class="bi bi-arrow-left"></i> Volver</a>
    <?php endif; ?>
    <span class="editor-nombre"><?= e($fila['nombre']) ?></span>
    <span class="editor-modo"><?= $puedeEditar ? 'Editando' : 'Solo lectura' ?></span>
  </div>
  <div id="editorPortalCore"></div>

  <script src="<?= e($onlyofficeUrlPublica) ?>/web-apps/apps/api/documents/api.js"></script>
  <script>
    new DocsAPI.DocEditor('editorPortalCore', <?= json_encode($configEditor, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
  </script>
</body>
</html>
