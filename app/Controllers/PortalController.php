<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/PortalController.php
//  Un solo controlador para todos los módulos del Portal:
//  exige sesión y muestra la vista correspondiente a $uri.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$modulos = [
    '/tableros'                  => ['titulo' => 'Tableros Estratégicos',       'vista' => 'Tablero_Estratégicos/Tablero.php'],
    '/mapa-portal'                => ['titulo' => 'Mapa del portal',             'vista' => 'Mapa_Portal/Mapa.php'],
    '/gestion-institucional'      => ['titulo' => 'Gestión Institucional',       'vista' => 'Gestión_Institucional/Gestión_Ins.php',        'slug' => 'institucional'],
    '/sgi'                        => ['titulo' => 'Sistema de Gestión Integral', 'vista' => 'Sistema_Gestión_Integral/Sistema_Integral.php', 'slug' => 'sgi'],
    '/vicerrectoria-academica'    => ['titulo' => 'Vicerrectoría Académica',     'vista' => 'Vicerrectoría_Académica/Vicerrectoria.php',     'slug' => 'academica'],
    '/administrativa-financiera'  => ['titulo' => 'Administrativa y Financiera', 'vista' => 'Administrativa_Financiera/Financiera.php',      'slug' => 'financiera'],
    '/talento-humano'             => ['titulo' => 'Talento Humano',              'vista' => 'Talento_Humano/Tal_Humano.php'],
    '/investigacion-innovacion'   => ['titulo' => 'Investigación e Innovación',  'vista' => 'Investigacón_Innovación/Investigacion.php',     'slug' => 'investigacion'],
    '/gestion-documental'         => ['titulo' => 'Gestión Documental',          'vista' => 'Gestión_Documental/Documental.php'],
    '/normatividad'               => ['titulo' => 'Normatividad',                'vista' => 'Normatividad/Normatividad.php'],
    '/novedades'                  => ['titulo' => 'Novedades',                   'vista' => 'Novedades/Novedades.php',                       'slug' => 'novedades'],
    '/aplicaciones'               => ['titulo' => 'Aplicaciones',                'vista' => 'Aplicaciones/Aplicaciones.php'],
];

$modulo = $modulos[$uri] ?? null;

if (!$modulo) {
    mostrar_error(404);
    exit;
}

$titulo = $modulo['titulo'];

// ── Módulo genérico de dirección (6 rutas comparten esta única consulta,
//    parametrizada por slug — ver database/migrations/002_kpis_e_iconos.sql
//    y las tablas direcciones/direccion_kpis/direccion_areas/
//    direccion_documentos (ver 007_direccion_documentos.sql)). Responsables
//    y software todavía no tienen tabla real (ver plan) y siguen viniendo
//    del MODULO.responsables/software de cada *.js. ──
if (!empty($modulo['slug'])) {
    $stmt = $pdo->prepare('SELECT id, kicker, titulo, descripcion FROM direcciones WHERE slug = :slug');
    $stmt->execute([':slug' => $modulo['slug']]);
    $direccion = $stmt->fetch();

    $moduloKicker = $direccion['kicker'] ?? '';
    $moduloTitulo = $direccion['titulo'] ?? $titulo;
    $moduloDesc = $direccion['descripcion'] ?? '';

    $moduloKpis = [];
    $moduloAreas = [];
    $moduloDocumentos = [];
    if ($direccion) {
        $stmt = $pdo->prepare('SELECT label, valor FROM direccion_kpis WHERE direccion_id = :id ORDER BY orden');
        $stmt->execute([':id' => $direccion['id']]);
        $moduloKpis = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT label, meta FROM direccion_areas WHERE direccion_id = :id ORDER BY orden');
        $stmt->execute([':id' => $direccion['id']]);
        $moduloAreas = $stmt->fetchAll();

        $meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        $stmt = $pdo->prepare('SELECT nombre, tipo, version, fecha FROM direccion_documentos WHERE direccion_id = :id ORDER BY orden');
        $stmt->execute([':id' => $direccion['id']]);
        foreach ($stmt->fetchAll() as $d) {
            $fecha = new DateTime($d['fecha']);
            $moduloDocumentos[] = [
                'nombre'  => $d['nombre'],
                'tipo'    => $d['tipo'],
                'version' => $d['version'],
                'fecha'   => $fecha->format('d') . ' ' . $meses[(int) $fecha->format('n') - 1] . ' ' . $fecha->format('Y'),
            ];
        }
    }
}

// ── Novedades ──
// "Noticias" y "Eventos" ya eran datos reales (tablas noticias/eventos,
// usadas hasta ahora solo en el dashboard de Inicio — ver HomeController.php)
// pero el módulo Novedades nunca las mostraba; novedades.js solo traía
// docs/responsables/software quemados como los otros 5 módulos genéricos,
// nada de noticias ni eventos. Se agregan acá con el mismo formato de
// fecha que ya usa Inicio, para que se vea igual en ambos lados.
if ($uri === '/novedades') {
    $mesesNovedades = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

    $moduloNoticias = [];
    foreach ($pdo->query('SELECT categoria, titulo, fecha FROM noticias ORDER BY fecha DESC')->fetchAll() as $r) {
        $fecha = new DateTime($r['fecha']);
        $moduloNoticias[] = [
            'categoria' => $r['categoria'],
            'titulo'    => $r['titulo'],
            'fecha'     => $fecha->format('d') . ' ' . $mesesNovedades[(int) $fecha->format('n') - 1] . ' ' . $fecha->format('Y'),
        ];
    }

    $moduloEventos = [];
    foreach ($pdo->query('SELECT titulo, fecha, hora_lugar FROM eventos ORDER BY fecha')->fetchAll() as $r) {
        $fecha = new DateTime($r['fecha']);
        $moduloEventos[] = [
            'titulo'     => $r['titulo'],
            'fecha'      => $fecha->format('d') . ' ' . $mesesNovedades[(int) $fecha->format('n') - 1] . ' ' . $fecha->format('Y'),
            'hora_lugar' => $r['hora_lugar'],
        ];
    }
}

// ── Tableros ──
if ($uri === '/tableros') {
    $tableroKpis = $pdo->query('SELECT label, valor, meta, pct FROM kpis_tablero ORDER BY orden')->fetchAll();

    $tableroMatricula = $pdo->query('SELECT facultad, estudiantes FROM matricula_facultad ORDER BY orden')->fetchAll();
    $matriculaMax = $tableroMatricula ? max(array_column($tableroMatricula, 'estudiantes')) : 0;
    foreach ($tableroMatricula as &$fila) {
        $fila['h'] = $matriculaMax ? round($fila['estudiantes'] / $matriculaMax * 100) : 0;
    }
    unset($fila);

    $tableroEjecucion = $pdo->query('SELECT label, pct FROM ejecucion_presupuestal ORDER BY orden')->fetchAll();
    $tableroAlertas = $pdo->query('SELECT texto FROM alertas_indicador ORDER BY orden')->fetchAll();
}

// ── Talento Humano ──
// Organigrama/cargos/competencias vienen de tablas reales (ver
// database/migrations/006_talento_humano.sql); se pasan a talento-humano.js
// como constantes ya resueltas (mismo mecanismo que portal-footer.php usa
// para window.BASE_URL) porque esa vista cambia de pestaña sin recargar la
// página, así que el JS necesita los datos en memoria, no otra petición.
// Comités, KPIs, desempeño y bienestar de esa misma pestaña siguen
// quemados en el JS — no tienen tabla propia todavía.
if ($uri === '/talento-humano') {
    $thOrganigrama = [];
    foreach ($pdo->query('SELECT id, label FROM organigrama_niveles ORDER BY orden')->fetchAll() as $nivel) {
        $stmt = $pdo->prepare('SELECT label, meta, destacado FROM organigrama_cajas WHERE nivel_id = :id ORDER BY orden');
        $stmt->execute([':id' => $nivel['id']]);
        $cajas = array_map(function ($c) {
            $c['destacado'] = (bool) $c['destacado'];
            return $c;
        }, $stmt->fetchAll());
        $thOrganigrama[] = ['nivel' => $nivel['label'], 'cajas' => $cajas];
    }

    $thCargos = $pdo->query('SELECT cargo, direccion, nivel, codigo FROM cargos ORDER BY orden')->fetchAll();
    $thCompetencias = $pdo->query('SELECT label, pct FROM competencias ORDER BY orden')->fetchAll();
}

// ── Mapa del portal ──
if ($uri === '/mapa-portal') {
    $sitemapModulos = [];
    foreach ($pdo->query('SELECT id, nivel, label, icono FROM sitemap_modulos ORDER BY orden')->fetchAll() as $m) {
        $stmt = $pdo->prepare('SELECT label FROM sitemap_items WHERE modulo_id = :id ORDER BY orden');
        $stmt->execute([':id' => $m['id']]);
        $m['hijos'] = array_column($stmt->fetchAll(), 'label');
        $sitemapModulos[] = $m;
    }
}

// ── Gestión Documental ──
// carpetas_documentales es un árbol de 2 niveles (dirección → área, vía
// parent_id); los archivos cuelgan del nivel de área (hoja), nunca del
// nivel de dirección.
if ($uri === '/gestion-documental') {
    $archivosPorCarpeta = [];
    foreach ($pdo->query('SELECT carpeta_id, nombre, tipo, version, estado, responsable, fecha FROM archivos_documentales ORDER BY fecha DESC')->fetchAll() as $a) {
        $archivosPorCarpeta[$a['carpeta_id']][] = $a;
    }

    $areasPorDireccion = [];
    foreach ($pdo->query('SELECT id, parent_id, label FROM carpetas_documentales WHERE parent_id IS NOT NULL ORDER BY orden')->fetchAll() as $area) {
        if (empty($archivosPorCarpeta[$area['id']])) continue; // sin archivos reales: no se inventa la carpeta vacía
        $areasPorDireccion[$area['parent_id']][] = $area;
    }

    $direccionesDoc = [];
    foreach ($pdo->query('SELECT id, label FROM carpetas_documentales WHERE parent_id IS NULL ORDER BY orden')->fetchAll() as $dir) {
        if (empty($areasPorDireccion[$dir['id']])) continue;
        $direccionesDoc[] = $dir;
    }
}

require ROOT_PATH . '/app/Views/Portal/' . $modulo['vista'];
