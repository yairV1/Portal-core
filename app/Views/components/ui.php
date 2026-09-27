<?php
// ══════════════════════════════════════════════════════════
//  app/Views/components/ui.php — helpers de marcado del sistema de diseño
//  (CSS en public/assets/core/components.css). Los carga portal-header.php,
//  así que cualquier vista del Portal los tiene disponibles.
//
//  Solo arman HTML que se repetía a mano en cada vista; no consultan la BD
//  ni tocan la sesión. Todo texto que reciben se escapa con e(); lo único
//  que se imprime tal cual es lo que la propia vista escribe dentro de un
//  callable ('actions'), igual que si lo hubiera escrito en línea.
// ══════════════════════════════════════════════════════════

if (!function_exists('ui_page_header')) {

    /**
     * Encabezado de página: volver + eyebrow + título + descripción + acciones.
     *
     *   ui_page_header([
     *       'title'   => 'Hojas de vida',
     *       'desc'    => 'Hoja de vida por empleado…',
     *       'eyebrow' => 'Talento Humano',                           // opcional
     *       'back'    => ['href' => BASE_URL . '/talento-humano',
     *                     'label' => 'Volver a Talento Humano'],      // opcional
     *       'actions' => function () { ?> <button …>…</button> <?php }, // opcional
     *   ]);
     */
    function ui_page_header(array $o): void
    {
        ?>
<header class="page-header">
  <div class="page-header-main">
    <?php if (!empty($o['back'])): ?>
      <a class="page-back" href="<?= e($o['back']['href']) ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> <?= e($o['back']['label'] ?? 'Volver') ?></a>
    <?php endif; ?>
    <?php if (!empty($o['eyebrow'])): ?>
      <p class="page-eyebrow"><?= e($o['eyebrow']) ?></p>
    <?php endif; ?>
    <h1 class="page-title"><?= e($o['title'] ?? '') ?></h1>
    <?php if (!empty($o['desc'])): ?>
      <p class="page-desc"><?= e($o['desc']) ?></p>
    <?php endif; ?>
  </div>
  <?php if (!empty($o['actions']) && is_callable($o['actions'])): ?>
    <div class="page-actions"><?php ($o['actions'])(); ?></div>
  <?php endif; ?>
</header>
        <?php
    }

    /**
     * Estado vacío con ícono, explicación y acción opcional.
     *
     *   ui_empty_state([
     *       'icon'    => 'people',              // nombre de Bootstrap Icons sin "bi-"
     *       'title'   => 'Aún no hay empleados',
     *       'text'    => 'Agrega el primero…',  // opcional
     *       'compact' => false,                 // true: versión en fila, para widgets/celdas
     *       'actions' => function () { ?> … <?php }, // opcional
     *   ]);
     */
    function ui_empty_state(array $o): void
    {
        $clase = 'empty-state' . (!empty($o['compact']) ? ' empty-state--compact' : '');
        ?>
<div class="<?= $clase ?>">
  <div class="ic" aria-hidden="true"><i class="bi bi-<?= e($o['icon'] ?? 'inbox') ?>"></i></div>
  <div>
    <h4><?= e($o['title'] ?? '') ?></h4>
    <?php if (!empty($o['text'])): ?><p><?= e($o['text']) ?></p><?php endif; ?>
  </div>
  <?php if (!empty($o['actions']) && is_callable($o['actions'])): ?>
    <div class="empty-state-actions"><?php ($o['actions'])(); ?></div>
  <?php endif; ?>
</div>
        <?php
    }
}
