<?php $titulo = 'Administración'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">

<?php ui_page_header(['title' => 'Administración', 'desc' => 'Todo lo que solo el administrador global puede gestionar, reunido en un solo lugar.']); ?>

<div class="doc-categorias">
  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/usuarios" class="doc-categoria-link">
      <span class="ic" aria-hidden="true"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
      <span class="nombre">Usuarios y roles</span>
      <span class="meta">Crea cuentas, asigna roles y qué dirección administra cada una</span>
    </a>
  </article>

  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/contenido-landing" class="doc-categoria-link">
      <span class="ic" aria-hidden="true"><i class="bi bi-window-stack" aria-hidden="true"></i></span>
      <span class="nombre">Contenido landing</span>
      <span class="meta">Edita lo que ve cualquier visitante en la portada pública</span>
    </a>
  </article>

  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/contrataciones" class="doc-categoria-link">
      <span class="ic" aria-hidden="true"><i class="bi bi-file-earmark-person" aria-hidden="true"></i></span>
      <span class="nombre">Contrataciones</span>
      <span class="meta">Genera enlaces de vinculación y revisa la documentación recibida</span>
    </a>
  </article>

  <article class="doc-categoria-card">
    <a href="<?= BASE_URL ?>/postulaciones" class="doc-categoria-link">
      <span class="ic" aria-hidden="true"><i class="bi bi-person-lines-fill" aria-hidden="true"></i></span>
      <span class="nombre">Postulaciones</span>
      <span class="meta">Hojas de vida recibidas en "Trabaja con nosotros"</span>
    </a>
  </article>
</div>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
