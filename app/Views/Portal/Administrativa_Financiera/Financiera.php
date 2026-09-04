<?php $titulo = 'Administrativa y Financiera'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/administrativa-financiera.css">
<div class="modulo-head">
        <div class="modulo-kicker" id="moduloKicker"></div>
        <h1 class="modulo-titulo" id="moduloTitulo"></h1>
        <p class="modulo-desc" id="moduloDesc"></p>
      </div>

      <div class="modulo-kpis" id="moduloKpis"></div>

      <div class="modulo-grid">
        <div>
          <div class="section-head"><h4>Áreas del módulo</h4></div>
          <div id="moduloAreas"></div>

          <div class="section-head" style="margin-top:40px"><h4>Documentación destacada</h4></div>
          <table class="table">
            <thead><tr><th>Documento</th><th>Tipo</th><th>Ver.</th><th>Actualizado</th></tr></thead>
            <tbody id="moduloDocs"></tbody>
          </table>
        </div>

        <div style="display:flex;flex-direction:column;gap:28px">
          <div class="side-box">
            <div class="side-box-title">Responsables</div>
            <div id="moduloResponsables"></div>
          </div>
          <div class="side-box">
            <div class="side-box-title">Software relacionado</div>
            <div id="moduloSoftware"></div>
          </div>
        </div>
      </div>
<script src="<?= BASE_URL ?>/assets/portal/js/administrativa-financiera.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
