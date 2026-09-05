<?php $titulo = 'Inicio'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/inicio.css">

<div class="hero">
  <div>
    <div class="kicker" id="saludoKicker">Buenos días</div>
    <h1>Jeiver, la institución está en marcha.</h1>
    <p>Tienes 4 pendientes, 2 documentos en revisión y el avance del PDI cerró la semana en 62%. Todo lo institucional, en un solo lugar.</p>
  </div>
  <div class="status-box">
    <div class="status-cell">
      <div class="status-label">Estado institucional</div>
      <div class="status-value"><span class="dot"></span>Operación normal</div>
    </div>
    <div class="status-cell">
      <div class="status-label">Ciclo</div>
      <div class="status-value">2026 · Semestre II</div>
    </div>
  </div>
</div>

<div class="kpis" id="kpis"></div>

<div class="grid-main">
  <div>
    <div class="section-head">
      <h4>Accesos rápidos</h4>
      <span class="link">Centro de aplicaciones</span>
    </div>
    <div class="accesos" id="accesos"></div>

    <div class="section-head" style="margin-top:44px">
      <h4>Documentos recientes</h4>
      <span class="link">Repositorio</span>
    </div>
    <table class="table">
      <thead><tr><th>Documento</th><th>Área</th><th>Ver.</th><th>Actualizado</th></tr></thead>
      <tbody id="docsRecientes"></tbody>
    </table>

    <div class="section-head" style="margin-top:44px">
      <h4>Novedades institucionales</h4>
    </div>
    <div class="noticias" id="noticias"></div>
  </div>

  <div class="side-col">
    <div class="side-card">
      <div class="section-head" style="margin-bottom:6px"><h4>Mis pendientes</h4></div>
      <div id="pendientes"></div>
    </div>
    <div class="side-card">
      <div class="section-head" style="margin-bottom:6px"><h4>Agenda de la semana</h4></div>
      <div id="eventos"></div>
    </div>
    <div class="side-card">
      <div class="section-head" style="margin-bottom:6px"><h4>Cumpleaños</h4></div>
      <div id="cumpleanos"></div>
    </div>
    <div class="pdi-card">
      <div class="pdi-label">Plan de Desarrollo Institucional</div>
      <div class="pdi-pct">62%</div>
      <div class="pdi-bar"><div class="pdi-bar-fill" style="width:62%"></div></div>
      <div class="pdi-desc">Avance acumulado 2026 sobre 48 metas cascadeadas a 11 áreas.</div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/portal/js/inicio.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>