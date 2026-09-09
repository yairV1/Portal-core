<?php
/**
 * Landing pública de Portal Core — se muestra en "/" cuando no hay sesión
 * iniciada (ver HomeController.php). $csrf/BASE_URL/e() ya vienen listos
 * desde public/index.php.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Core — COREDUCACIÓN</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= v('/assets/web/css/inicio.css') ?>">
</head>
<body>


<nav class="navbar">
  <div class="container nav-row">
    <a href="#inicio" class="brand">
      <svg width="26" height="21" viewBox="0 0 60 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs><linearGradient id="mg" x1="0" y1="0" x2="60" y2="48" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#F15A29"/><stop offset="1" stop-color="#9E1F63"/></linearGradient></defs>
        <path d="M6 6 L30 20 L30 28 L6 42 Z" fill="url(#mg)"/>
        <path d="M34 4 L58 18" stroke="url(#mg)" stroke-width="7" stroke-linecap="round"/>
        <path d="M34 24 L58 24" stroke="url(#mg)" stroke-width="7" stroke-linecap="round"/>
        <path d="M34 44 L58 30" stroke="url(#mg)" stroke-width="7" stroke-linecap="round"/>
      </svg>
      <strong>Portal Core</strong>
    </a>
    <nav class="links" aria-label="Navegación principal">
      <a href="#modulos">Módulos</a>
      <a href="#roles">Para tu rol</a>
      <a href="#pasos">Cómo empiezas</a>
      <a href="<?= BASE_URL ?>/quienes-somos">Quiénes somos</a>
      <a href="<?= BASE_URL ?>/trabaja-con-nosotros">Trabaja con nosotros</a>
    </nav>
    <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-dark">Iniciar sesión</a>
  </div>
</nav>

<section class="hero" id="inicio">
  <div class="hero-watermark" aria-hidden="true">
    <svg viewBox="0 0 800 520" xmlns="http://www.w3.org/2000/svg" fill="none">
      <path d="M120 380 L420 220 L420 300 L120 460 Z" stroke="#fff" stroke-width="10"/>
      <path d="M470 150 L680 230" stroke="#fff" stroke-width="10" stroke-linecap="round"/>
      <path d="M470 280 L700 280" stroke="#fff" stroke-width="10" stroke-linecap="round"/>
      <path d="M470 410 L680 340" stroke="#fff" stroke-width="10" stroke-linecap="round"/>
    </svg>
  </div>
  <div class="hero-content">
    <span class="hero-kicker">Portal institucional de COREDUCACIÓN</span>
    <div class="hero-giant">CORE<small>.portal</small></div>
    <p class="hero-desc">Toda tu institución organizada en un solo lugar: acceso, roles y módulos de COREDUCACIÓN, pensado para docentes, coordinadores y administrativos.</p>
    <div class="hero-ctas">
      <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-white">Iniciar sesión</a>
      <a href="#modulos" class="btn-pill btn-pill-outline">Ver módulos</a>
    </div>
  </div>
</section>

<section class="why">
  <div class="container">
    <div class="row" style="display:grid; grid-template-columns:1.1fr .9fr; gap:56px; align-items:start;">
      <div class="why-copy">
        <span class="hero-kicker" style="color:var(--magenta-dark);">Por qué Portal Core</span>
        <h2 class="mt-2">Un portal pensado para el día a día de tu institución.</h2>
        <p>Nada de hojas de cálculo sueltas ni procesos repartidos entre correos. Portal Core centraliza el acceso y la información según el rol de cada persona.</p>

        <div class="stat-row">
          <div class="stat-item">
            <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
            <div><strong>3 roles</strong><span>Docentes, coordinadores, administrativos</span></div>
          </div>
          <div class="stat-item">
            <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div>
            <div><strong>6 módulos</strong><span>Entre disponibles y en construcción</span></div>
          </div>
          <div class="stat-item">
            <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
            <div><strong>Honda, Tolima</strong><span>Patrimonio educativo regional</span></div>
          </div>
        </div>
      </div>

      <div class="feature-panel">
        <div class="feature-card">
          <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="1"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></div>
          <div><h3>Acceso seguro</h3><p>Cada persona entra con su cuenta institucional y solo ve lo que le corresponde.</p></div>
        </div>
        <div class="feature-card">
          <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div>
          <div><h3>Todo conectado</h3><p>Los módulos comparten la misma base de datos, sin duplicar información entre sistemas.</p></div>
        </div>
        <div class="feature-card">
          <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
          <div><h3>Hecho a la medida</h3><p>Construido específicamente para cómo trabaja COREDUCACIÓN, no adaptado de otro sistema.</p></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="modules" id="modulos">
  <div class="modules-head">
    <div>
      <h2>Módulos de Portal Core</h2>
    </div>
    <p>Lo que ya está disponible y lo que sigue en el roadmap — sin adelantar nada que aún no exista.</p>
  </div>

  <div class="module-carousel">
    <div class="module-track" id="module-track">
    <div class="module-card">
      <div class="module-visual" style="background:linear-gradient(135deg,var(--magenta),var(--magenta-dark));">
        <span class="module-price-badge">Disponible</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="1"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      </div>
      <div class="module-body">
        <h3>Login y roles</h3>
        <p class="meta">Control de acceso por rol</p>
        <p class="loc">📍 Ya construido</p>
      </div>
    </div>

    <div class="module-card">
      <div class="module-visual" style="background:linear-gradient(135deg,var(--orange),var(--orange-dark));">
        <span class="module-price-badge">En desarrollo</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      </div>
      <div class="module-body">
        <h3>Áreas y permisos</h3>
        <p class="meta">Organización institucional</p>
        <p class="loc">📍 En el roadmap actual</p>
      </div>
    </div>

    <div class="module-card">
      <div class="module-visual" style="background:linear-gradient(135deg,var(--orange),var(--orange-dark));">
        <span class="module-price-badge">En desarrollo</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
      </div>
      <div class="module-body">
        <h3>Gestor documental</h3>
        <p class="meta">Documentos por área</p>
        <p class="loc">📍 En el roadmap actual</p>
      </div>
    </div>

    <div class="module-card">
      <div class="module-visual" style="background:linear-gradient(135deg,#9c8f95,#7C6A70);">
        <span class="module-price-badge">Próximamente</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      </div>
      <div class="module-body">
        <h3>Calendario</h3>
        <p class="meta">Fechas institucionales</p>
        <p class="loc">📍 Fase 4 del roadmap</p>
      </div>
    </div>

    <div class="module-card">
      <div class="module-visual" style="background:linear-gradient(135deg,#9c8f95,#7C6A70);">
        <span class="module-price-badge">Próximamente</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
      </div>
      <div class="module-body">
        <h3>Indicadores</h3>
        <p class="meta">Datos para decisiones</p>
        <p class="loc">📍 Fase 4 del roadmap</p>
      </div>
    </div>

    <div class="module-card">
      <div class="module-visual" style="background:linear-gradient(135deg,#9c8f95,#7C6A70);">
        <span class="module-price-badge">Próximamente</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      </div>
      <div class="module-body">
        <h3>Directorio</h3>
        <p class="meta">Personal de la institución</p>
        <p class="loc">📍 Fase 4 del roadmap</p>
      </div>
    </div>
    </div>
  </div>

  <div class="modules-foot">
    <div class="carousel-dots" id="carousel-dots"></div>
    <div class="carousel-arrows">
      <button id="scroll-left" aria-label="Módulos anteriores">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      </button>
      <button id="scroll-right" aria-label="Módulos siguientes">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
      </button>
    </div>
  </div>
</section>

<section class="roles" id="roles">
  <div class="container">
    <div class="roles-grid">
      <div class="role-intro">
        <div>
          <h2 style="font-size:1.25rem;">Un portal, según tu rol.</h2>
          <p>Lo que ves y puedes hacer dentro de Portal Core cambia según el rol que tengas asignado.</p>
        </div>
        <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-white">Iniciar sesión</a>
      </div>

      <div class="role-card" style="background:linear-gradient(160deg, rgba(26,26,26,.15), rgba(158,31,99,.75)), linear-gradient(135deg,#9E1F63,#6E1747);">
        <span class="role-badge"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5"/></svg></span>
        <div>
          <h3>Docentes</h3>
          <p>Acceso a su información académica y a los módulos habilitados para docencia.</p>
        </div>
      </div>

      <div class="role-card" style="background:linear-gradient(160deg, rgba(26,26,26,.15), rgba(241,90,41,.75)), linear-gradient(135deg,#F15A29,#C23F16);">
        <span class="role-badge"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></span>
        <div>
          <h3>Coordinadores</h3>
          <p>Visión de las áreas a su cargo y seguimiento de lo que su permiso habilite.</p>
        </div>
      </div>

      <div class="role-card" style="background:linear-gradient(160deg, rgba(26,26,26,.2), rgba(26,26,26,.85)), linear-gradient(135deg,#3A2A32,#1A1A1A);">
        <span class="role-badge"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="15" r="3"/></svg></span>
        <div>
          <h3>Administrativos</h3>
          <p>Gestión de usuarios, áreas y configuración general del portal.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="steps" id="pasos">
  <div class="container">
    <h2>Así empiezas, en tres pasos.</h2>
    <div class="steps-row">
      <div class="step">
        <div class="step-circle"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="1"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></div>
        <h3>Inicias sesión</h3>
        <p>Con tu correo institucional y la contraseña que te asignó tu administrador.</p>
      </div>
      <div class="step">
        <div class="step-circle"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <h3>El sistema reconoce tu rol</h3>
        <p>Portal Core muestra únicamente los módulos habilitados para docente, coordinador o administrativo.</p>
      </div>
      <div class="step">
        <div class="step-circle"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg></div>
        <h3>Empiezas a trabajar</h3>
        <p>Accedes a tus áreas asignadas sin depender de hojas sueltas ni procesos externos.</p>
      </div>
    </div>
  </div>
</section>



<script src="<?= v('/assets/web/js/inicio.js') ?>"></script>
</body>
</html>
