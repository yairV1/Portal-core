<?php $titulo = 'Tableros Estratégicos'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/tableros.css">
<h1 style="font-size:40px;margin:0 0 8px">Tableros Estratégicos</h1>
<p style="opacity:.65;max-width:640px;margin:0 0 28px">Indicadores institucionales en tiempo real. Vista Rectoría, con descenso a dirección y área.</p>

<div class="box-card" style="display:flex;gap:0;padding:0;width:fit-content;margin-bottom:28px;overflow:hidden" id="tableroTabs"></div>

<div class="kpis" id="kpisTablero" style="margin-bottom:36px"></div>

<div style="display:grid;grid-template-columns:1.3fr 1fr;gap:36px;align-items:start">
  <div class="box-card">
    <div style="display:flex;justify-content:space-between;align-items:baseline;border-bottom:2px solid var(--color-divider);padding-bottom:12px;margin-bottom:24px">
      <h4>Matrícula por facultad · 2026-II</h4>
      <span style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;opacity:.5;font-weight:800">Power BI · en vivo</span>
    </div>
    <div style="display:flex;align-items:flex-end;gap:18px;height:230px" id="barras"></div>
  </div>

  <div style="display:flex;flex-direction:column;gap:24px">
    <div class="box-card">
      <h4 style="margin:0 0 18px">Ejecución presupuestal</h4>
      <div id="ejecucion"></div>
    </div>
    <div class="box-card">
      <h4 style="margin:0 0 14px">Alertas de indicador</h4>
      <div id="alertasKpi"></div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/portal/js/tableros.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
