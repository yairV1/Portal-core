<?php $titulo = 'Todos los módulos'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">

<h1 class="page-title">Todos los módulos</h1>
<p class="page-desc">Todo el Portal, agrupado en un solo lugar — cada tarjeta te lleva a su propia página.</p>

<div class="section-head"><h4>Direcciones</h4></div>
<div class="doc-categorias">
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/gestion-institucional" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-bank"></i></span>
      <span class="nombre">Gestión Institucional</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/sgi" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-folder2-open"></i></span>
      <span class="nombre">Sistema de Gestión Integral</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/vicerrectoria-academica" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-mortarboard"></i></span>
      <span class="nombre">Vicerrectoría Académica</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/administrativa-financiera" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-cash-coin"></i></span>
      <span class="nombre">Administrativa y Financiera</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/talento-humano" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-people"></i></span>
      <span class="nombre">Talento Humano</span>
    </a>
  </article>
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/investigacion-innovacion" class="doc-categoria-link">
      <span class="ic"><i class="bi bi-stars"></i></span>
      <span class="nombre">Investigación e Innovación</span>
    </a>
  </article>
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
