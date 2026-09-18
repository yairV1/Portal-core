<?php $titulo = 'Todos los módulos'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">

<h1 class="page-title">Todos los módulos</h1>
<p class="page-desc">Todo el Portal, agrupado en un solo lugar — cada tarjeta te lleva a su propia página.</p>

<?php if (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
  <div class="section-head"><h4>Administración</h4></div>
  <div class="doc-categorias">
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL ?>/usuarios" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-people-fill"></i></span>
        <span class="nombre">Usuarios y roles</span>
      </a>
    </article>
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL ?>/contenido-landing" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-window-stack"></i></span>
        <span class="nombre">Contenido landing</span>
      </a>
    </article>
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL ?>/contrataciones" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-file-earmark-person"></i></span>
        <span class="nombre">Contrataciones</span>
      </a>
    </article>
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL ?>/postulaciones" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-person-lines-fill"></i></span>
        <span class="nombre">Postulaciones</span>
      </a>
    </article>
  </div>
<?php endif; ?>

<?php
// Si esta cuenta tiene un área de trabajo asignada (ver
// usuario_area_asignada() en public/index.php), tacha del listado las
// direcciones que no sean la suya — misma regla que ya bloquea la URL
// directa en PortalController.php, para que el menú nunca ofrezca un
// enlace que de todos modos va a dar 403.
$direcciones = [
    ['slug' => 'institucional',   'ruta' => '/gestion-institucional',     'icono' => 'bank',          'nombre' => 'Gestión Institucional'],
    ['slug' => 'sgi',             'ruta' => '/sgi',                       'icono' => 'folder2-open',  'nombre' => 'Sistema de Gestión Integral'],
    ['slug' => 'academica',       'ruta' => '/vicerrectoria-academica',   'icono' => 'mortarboard',   'nombre' => 'Vicerrectoría Académica'],
    ['slug' => 'financiera',      'ruta' => '/administrativa-financiera', 'icono' => 'cash-coin',     'nombre' => 'Administrativa y Financiera'],
    ['slug' => 'talento-humano',  'ruta' => '/talento-humano',            'icono' => 'people',        'nombre' => 'Talento Humano'],
    ['slug' => 'investigacion',   'ruta' => '/investigacion-innovacion',  'icono' => 'stars',         'nombre' => 'Investigación e Innovación'],
];
?>
<div class="section-head"><h4>Direcciones</h4></div>
<div class="doc-categorias">
  <?php foreach ($direcciones as $d): ?>
    <?php if ($areaAsignada !== null && ($direccionIdPorSlug[$d['slug']] ?? null) !== $areaAsignada) continue; ?>
    <article class="doc-categoria-card">
      <a href="<?= BASE_URL . $d['ruta'] ?>" class="doc-categoria-link">
        <span class="ic"><i class="bi bi-<?= e($d['icono']) ?>"></i></span>
        <span class="nombre"><?= e($d['nombre']) ?></span>
      </a>
    </article>
  <?php endforeach; ?>
</div>

<div class="section-head" style="margin-top:32px"><h4>Recursos</h4></div>
<div class="doc-categorias">
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/gestion-documental" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-folder2"></i></span>
      <span class="nombre">Gestión Documental</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/normatividad" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-file-earmark-text"></i></span>
      <span class="nombre">Normatividad</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/novedades" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-newspaper"></i></span>
      <span class="nombre">Novedades</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/aplicaciones" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-grid"></i></span>
      <span class="nombre">Aplicaciones</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/directorio" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-person-badge"></i></span>
      <span class="nombre">Directorio</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/calendario" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-calendar3"></i></span>
      <span class="nombre">Calendario</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/trello" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-kanban"></i></span>
      <span class="nombre">Trello</span>
    </a>
  </article>
</div>

<div class="section-head" style="margin-top:32px"><h4>Analítica</h4></div>
<div class="doc-categorias">
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/cuadro-mando-integral" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-bar-chart"></i></span>
      <span class="nombre">Cuadro de Mando Integral</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/mapa-portal" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-map"></i></span>
      <span class="nombre">Mapa del portal</span>
    </a>
  </article>
</div>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
