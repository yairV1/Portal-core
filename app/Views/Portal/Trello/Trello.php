<?php $titulo = 'Trello'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/trello.css') ?>">
<h1 class="page-title">Trello</h1>
<p class="page-desc">Tablero de Trello conectado al portal — solo lectura, se gestiona desde Trello mismo.</p>

<?php if (!$trelloConfigurado): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-kanban"></i></div>
    <h4>Trello no está conectado</h4>
    <p>Falta configurar TRELLO_API_KEY, TRELLO_TOKEN y TRELLO_BOARD_ID (ver .env.example).</p>
  </div>
<?php elseif ($trelloListas === null): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-exclamation-triangle"></i></div>
    <h4>No se pudo cargar el tablero</h4>
    <p>Revisa que el token de Trello siga vigente y que el tablero exista.</p>
  </div>
<?php elseif (!$trelloListas): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-kanban"></i></div>
    <h4>El tablero está vacío</h4>
    <p>Este tablero de Trello todavía no tiene listas.</p>
  </div>
<?php else: ?>
  <div class="trello-board">
    <?php foreach ($trelloListas as $lista): ?>
      <div class="trello-lista box-card box-card--flat">
        <h4 class="trello-lista-titulo"><?= e($lista['nombre']) ?></h4>
        <?php if (!$lista['tarjetas']): ?>
          <p class="trello-vacia">Sin tarjetas</p>
        <?php else: foreach ($lista['tarjetas'] as $tarjeta): ?>
          <a class="trello-tarjeta" href="<?= e($tarjeta['url']) ?>" target="_blank" rel="noopener">
            <span><?= e($tarjeta['nombre']) ?></span>
            <?php if ($tarjeta['vencimiento']): ?>
              <span class="trello-vencimiento"><i class="bi bi-clock"></i> <?= e($tarjeta['vencimiento']) ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
