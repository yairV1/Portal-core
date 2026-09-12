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
    '/cuadro-mando-integral'      => ['titulo' => 'Cuadro de Mando Integral',    'vista' => 'Tablero_Estrategicos/Tablero.php'],
    // Perspectivas del CMI: rutas propias y vacías (sin fila en
    // direccion_kpis/áreas/documentos todavía), no las páginas de
    // dirección reales — reusan el mismo "módulo genérico" de abajo
    // (por el 'slug') con datos nuevos, ver migration 026.
    '/cuadro-mando-integral/finanzas'                => ['titulo' => 'Finanzas',                  'vista' => 'Cuadro_Mando_Integral/Perspectiva.php', 'slug' => 'cmi-finanzas'],
    '/cuadro-mando-integral/planeacion'               => ['titulo' => 'Planeación',                'vista' => 'Cuadro_Mando_Integral/Perspectiva.php', 'slug' => 'cmi-planeacion'],
    '/cuadro-mando-integral/vicerrectoria-academica'  => ['titulo' => 'Vicerrectoría Académica',    'vista' => 'Cuadro_Mando_Integral/Perspectiva.php', 'slug' => 'cmi-vicerrectoria-academica'],
    '/cuadro-mando-integral/investigacion'            => ['titulo' => 'Dirección de Investigación', 'vista' => 'Cuadro_Mando_Integral/Perspectiva.php', 'slug' => 'cmi-investigacion'],
    '/mapa-portal'                => ['titulo' => 'Mapa del portal',             'vista' => 'Mapa_Portal/Mapa.php'],
    '/gestion-institucional'      => ['titulo' => 'Gestión Institucional',       'vista' => 'Gestion_Institucional/Gestion_Ins.php',        'slug' => 'institucional'],
    '/sgi'                        => ['titulo' => 'Sistema de Gestión Integral', 'vista' => 'Sistema_Gestion_Integral/Sistema_Integral.php', 'slug' => 'sgi'],
    '/vicerrectoria-academica'    => ['titulo' => 'Vicerrectoría Académica',     'vista' => 'Vicerrectoria_Academica/Vicerrectoria.php',     'slug' => 'academica'],
    '/administrativa-financiera'  => ['titulo' => 'Administrativa y Financiera', 'vista' => 'Administrativa_Financiera/Financiera.php',      'slug' => 'financiera'],
    '/talento-humano'             => ['titulo' => 'Talento Humano',              'vista' => 'Talento_Humano/Tal_Humano.php', 'slug' => 'talento-humano'],
    '/investigacion-innovacion'   => ['titulo' => 'Investigación e Innovación',  'vista' => 'Investigacion_Innovacion/Investigacion.php',     'slug' => 'investigacion'],
    '/gestion-documental'         => ['titulo' => 'Gestión Documental',          'vista' => 'Gestion_Documental/Documental.php'],
    '/normatividad'               => ['titulo' => 'Normatividad',                'vista' => 'Normatividad/Normatividad.php'],
    '/novedades'                  => ['titulo' => 'Novedades',                   'vista' => 'Novedades/Novedades.php',                       'slug' => 'novedades'],
    '/aplicaciones'               => ['titulo' => 'Aplicaciones',                'vista' => 'Aplicaciones/Aplicaciones.php'],
    '/directorio'                 => ['titulo' => 'Directorio',                  'vista' => 'Directorio/Directorio.php'],
    '/calendario'                 => ['titulo' => 'Calendario',                  'vista' => 'Calendario/Calendario.php'],
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

    // Pestañas Administración/Finanzas (ver migración 032) — hoy solo las
    // usa el centro documental de Financiera, pero vive acá porque
    // direccion_documentos es de este bloque genérico compartido. El
    // resto de los módulos nunca manda ?area=, así que siempre les queda
    // en 'administracion' (que es también el valor por defecto de sus
    // filas ya existentes) — no les cambia nada.
    $areaActiva = ($_GET['area'] ?? '') === 'finanzas' ? 'finanzas' : 'administracion';

    $moduloKpis = [];
    $moduloAreas = [];
    $moduloDocumentos = [];
    $moduloFormatos = [];
    $moduloResponsables = [];
    $moduloSoftware = [];
    if ($direccion) {
        $stmt = $pdo->prepare('SELECT label, valor FROM direccion_kpis WHERE direccion_id = :id ORDER BY orden');
        $stmt->execute([':id' => $direccion['id']]);
        $moduloKpis = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT label, meta FROM direccion_areas WHERE direccion_id = :id ORDER BY orden');
        $stmt->execute([':id' => $direccion['id']]);
        $moduloAreas = $stmt->fetchAll();

        // Responsables/software: tablas reales que ya existían pero cada
        // *.js de módulo seguía usando nombres inventados quemados en vez
        // de consultarlas — ver administrativa-financiera.js (y los otros
        // 5 módulos genéricos) antes de este cambio.
        $stmt = $pdo->prepare('SELECT nombre, cargo, foto FROM direccion_responsables WHERE direccion_id = :id ORDER BY orden');
        $stmt->execute([':id' => $direccion['id']]);
        $moduloResponsables = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT nombre FROM direccion_software WHERE direccion_id = :id ORDER BY orden');
        $stmt->execute([':id' => $direccion['id']]);
        $moduloSoftware = $stmt->fetchAll();

        // "Documentación destacada" y "Formatos" (plantillas en blanco para
        // descargar y diligenciar) viven en la misma tabla — categoria
        // (migración 031) separa una de otra, area (migración 032) las
        // separa por pestaña Administración/Finanzas.
        $meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        $stmt = $pdo->prepare('SELECT id, nombre, tipo, version, archivo, fecha FROM direccion_documentos WHERE direccion_id = :id AND categoria = :cat AND area = :area ORDER BY orden');
        $stmt->execute([':id' => $direccion['id'], ':cat' => 'documento', ':area' => $areaActiva]);
        foreach ($stmt->fetchAll() as $d) {
            $fecha = new DateTime($d['fecha']);
            $moduloDocumentos[] = [
                'id'      => $d['id'],
                'nombre'  => $d['nombre'],
                'tipo'    => $d['tipo'],
                'version' => $d['version'],
                'archivo' => $d['archivo'],
                'fecha'   => $fecha->format('d') . ' ' . $meses[(int) $fecha->format('n') - 1] . ' ' . $fecha->format('Y'),
                'fecha_raw' => $d['fecha'],
            ];
        }

        $stmt->execute([':id' => $direccion['id'], ':cat' => 'formato', ':area' => $areaActiva]);
        foreach ($stmt->fetchAll() as $d) {
            $fecha = new DateTime($d['fecha']);
            $moduloFormatos[] = [
                'id'      => $d['id'],
                'nombre'  => $d['nombre'],
                'tipo'    => $d['tipo'],
                'version' => $d['version'],
                'archivo' => $d['archivo'],
                'fecha'   => $fecha->format('d') . ' ' . $meses[(int) $fecha->format('n') - 1] . ' ' . $fecha->format('Y'),
                'fecha_raw' => $d['fecha'],
            ];
        }
    }
}

// ── Centro documental (Financiera y Talento Humano comparten esta misma
//    lógica) ── Ver CarpetaController.php (crear/subir/descargar/eliminar)
// y las vistas de cada módulo (Financiera.php/Tal_Humano.php, ambas usan
// _explorador.php). ?carpeta=<id> es la carpeta abierta; sin ese parámetro
// se muestra el panel principal (categorías + recientes + búsqueda).
// Financiera además tiene pestañas Administración/Finanzas (area) — Talento
// Humano no las usa (siempre 'administracion'), pero comparte la columna.
$MODULOS_CON_CENTRO_DOCUMENTAL = ['financiera', 'talento-humano'];
if (!empty($modulo['slug']) && in_array($modulo['slug'], $MODULOS_CON_CENTRO_DOCUMENTAL, true) && $direccion) {
    $carpetaIdPedida = isset($_GET['carpeta']) ? (int) $_GET['carpeta'] : null;

    $carpetaActual = null;
    if ($carpetaIdPedida) {
        $stmt = $pdo->prepare('SELECT id, parent_id, nombre, area FROM direccion_carpetas WHERE id = :id AND direccion_id = :did');
        $stmt->execute([':id' => $carpetaIdPedida, ':did' => $direccion['id']]);
        $carpetaActual = $stmt->fetch() ?: null;
    }
    $carpetaIdActual = $carpetaActual ? (int) $carpetaActual['id'] : null;

    // Dentro de una carpeta, la carpeta misma ya dice a qué pestaña
    // pertenece — más confiable que confiar en el ?area= de la URL (que
    // ya cumplió su función al armar el link para llegar hasta acá).
    if ($carpetaActual) {
        $areaActiva = $carpetaActual['area'];
    }

    $rutaCarpetas = [];
    $cursor = $carpetaActual;
    while ($cursor) {
        array_unshift($rutaCarpetas, $cursor);
        if (!$cursor['parent_id']) break;
        $stmt = $pdo->prepare('SELECT id, parent_id, nombre, area FROM direccion_carpetas WHERE id = :id');
        $stmt->execute([':id' => $cursor['parent_id']]);
        $cursor = $stmt->fetch() ?: null;
    }

    if ($carpetaIdActual) {
        $stmt = $pdo->prepare('SELECT id, nombre FROM direccion_carpetas WHERE direccion_id = :did AND parent_id = :pid ORDER BY nombre');
        $stmt->execute([':did' => $direccion['id'], ':pid' => $carpetaIdActual]);
        $subcarpetas = $stmt->fetchAll();
    } else {
        // Raíz: estas filas son las "Categorías" del panel principal, con
        // conteo de archivos propio — no recursivo (solo lo que cuelga
        // directo de la categoría), igual de simple que el resto del portal.
        $stmt = $pdo->prepare('
            SELECT c.id, c.nombre, COUNT(a.id) AS total_archivos
            FROM direccion_carpetas c
            LEFT JOIN direccion_carpeta_archivos a ON a.carpeta_id = c.id
            WHERE c.direccion_id = :did AND c.parent_id IS NULL AND c.area = :area
            GROUP BY c.id, c.nombre
            ORDER BY c.nombre
        ');
        $stmt->execute([':did' => $direccion['id'], ':area' => $areaActiva]);
        $subcarpetas = $stmt->fetchAll();
    }

    $archivosCarpeta = [];
    if ($carpetaIdActual) {
        $stmt = $pdo->prepare('SELECT id, nombre, tipo, archivo, peso_bytes, subido_en FROM direccion_carpeta_archivos WHERE carpeta_id = :cid ORDER BY nombre');
        $stmt->execute([':cid' => $carpetaIdActual]);
        $archivosCarpeta = $stmt->fetchAll();
    }

    // "Archivos recientes" del panel principal: junta archivos sueltos
    // dentro de cualquier carpeta de esta pestaña + Documentos/Formatos de
    // la misma — una sola lista ordenada por fecha, más nuevo primero.
    $archivosRecientes = [];
    if (!$carpetaIdActual) {
        $stmt = $pdo->prepare('
            SELECT a.id, a.nombre, a.tipo, a.archivo, a.subido_en AS fecha_raw, c.id AS carpeta_id
            FROM direccion_carpeta_archivos a
            JOIN direccion_carpetas c ON c.id = a.carpeta_id
            WHERE c.direccion_id = :did AND c.area = :area AND a.archivo <> ""
            ORDER BY a.subido_en DESC
            LIMIT 12
        ');
        $stmt->execute([':did' => $direccion['id'], ':area' => $areaActiva]);
        foreach ($stmt->fetchAll() as $a) {
            $archivosRecientes[] = [
                'id' => $a['id'], 'nombre' => $a['nombre'], 'tipo' => $a['tipo'], 'archivo' => $a['archivo'],
                'fecha_raw' => $a['fecha_raw'], 'carpeta_id' => (int) $a['carpeta_id'], 'origen' => 'carpeta',
            ];
        }
        foreach ($moduloDocumentos as $d) {
            if ($d['archivo']) $archivosRecientes[] = $d + ['carpeta_id' => null, 'origen' => 'documento'];
        }
        foreach ($moduloFormatos as $f) {
            if ($f['archivo']) $archivosRecientes[] = $f + ['carpeta_id' => null, 'origen' => 'formato'];
        }
        usort($archivosRecientes, fn ($a, $b) => strtotime($b['fecha_raw']) <=> strtotime($a['fecha_raw']));
        $archivosRecientes = array_slice($archivosRecientes, 0, 12);
    }

    // Búsqueda real (GET, sin JS de mentira): filtra por nombre entre los
    // archivos de carpetas y los Documentos/Formatos de esta misma pestaña.
    $terminoBusqueda = trim($_GET['buscar'] ?? '');
    $resultadosBusqueda = null;
    if ($terminoBusqueda !== '') {
        $like = '%' . $terminoBusqueda . '%';
        $resultadosBusqueda = [];
        $stmt = $pdo->prepare('
            SELECT a.id, a.nombre, a.tipo, a.archivo, a.subido_en AS fecha_raw, c.id AS carpeta_id, c.nombre AS carpeta_nombre
            FROM direccion_carpeta_archivos a
            JOIN direccion_carpetas c ON c.id = a.carpeta_id
            WHERE c.direccion_id = :did AND c.area = :area AND a.nombre LIKE :like AND a.archivo <> ""
            ORDER BY a.subido_en DESC
        ');
        $stmt->execute([':did' => $direccion['id'], ':area' => $areaActiva, ':like' => $like]);
        foreach ($stmt->fetchAll() as $a) {
            $resultadosBusqueda[] = [
                'id' => $a['id'], 'nombre' => $a['nombre'], 'tipo' => $a['tipo'], 'archivo' => $a['archivo'],
                'fecha_raw' => $a['fecha_raw'], 'carpeta_id' => (int) $a['carpeta_id'],
                'carpeta_nombre' => $a['carpeta_nombre'], 'origen' => 'carpeta',
            ];
        }
        foreach ($moduloDocumentos as $d) {
            if ($d['archivo'] && mb_stripos($d['nombre'], $terminoBusqueda) !== false) {
                $resultadosBusqueda[] = $d + ['carpeta_id' => null, 'carpeta_nombre' => null, 'origen' => 'documento'];
            }
        }
        foreach ($moduloFormatos as $f) {
            if ($f['archivo'] && mb_stripos($f['nombre'], $terminoBusqueda) !== false) {
                $resultadosBusqueda[] = $f + ['carpeta_id' => null, 'carpeta_nombre' => null, 'origen' => 'formato'];
            }
        }
        usort($resultadosBusqueda, fn ($a, $b) => strtotime($b['fecha_raw']) <=> strtotime($a['fecha_raw']));
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
    $stmtEventosNovedades = $pdo->prepare("SELECT titulo, fecha, hora_lugar FROM eventos
        WHERE visibilidad = 'publico' OR usuario_id = :usuario_id ORDER BY fecha");
    $stmtEventosNovedades->execute([':usuario_id' => $_SESSION['usuario_id']]);
    foreach ($stmtEventosNovedades->fetchAll() as $r) {
        $fecha = new DateTime($r['fecha']);
        $moduloEventos[] = [
            'titulo'     => $r['titulo'],
            'fecha'      => $fecha->format('d') . ' ' . $mesesNovedades[(int) $fecha->format('n') - 1] . ' ' . $fecha->format('Y'),
            'hora_lugar' => $r['hora_lugar'],
        ];
    }
}

// ── Cuadro de Mando Integral (antes "Tableros Estratégicos") ──
if ($uri === '/cuadro-mando-integral') {
    $tableroKpis = $pdo->query('SELECT label, valor, meta, pct FROM kpis_tablero ORDER BY orden')->fetchAll();

    $tableroMatricula = $pdo->query('SELECT facultad, estudiantes FROM matricula_facultad ORDER BY orden')->fetchAll();
    $matriculaMax = $tableroMatricula ? max(array_column($tableroMatricula, 'estudiantes')) : 0;
    foreach ($tableroMatricula as &$fila) {
        $fila['h'] = $matriculaMax ? round($fila['estudiantes'] / $matriculaMax * 100) : 0;
    }
    unset($fila);

    $tableroEjecucion = $pdo->query('SELECT label, pct FROM ejecucion_presupuestal ORDER BY orden')->fetchAll();
    $tableroAlertas = $pdo->query('SELECT texto FROM alertas_indicador ORDER BY orden')->fetchAll();

    // Enlaces a los 4 submódulos (Finanzas/Planeación/Vicerrectoría
    // Académica/Investigación): misma fuente que ya arma el submenú del
    // sidebar (nav_items.parent_id — ver migration 025), así que la
    // página y el menú nunca quedan desincronizados.
    $stmt = $pdo->prepare("SELECT ni.label, ni.ruta, ni.icono
        FROM nav_items ni
        JOIN nav_items padre ON padre.id = ni.parent_id
        WHERE padre.slug = 'cuadro-mando-integral'
        ORDER BY ni.orden");
    $stmt->execute();
    $tableroSubmodulos = $stmt->fetchAll();
}

// ── Directorio (fase 4) ──
// Mismos cargos reales que ya usa Talento Humano (ver
// database/migrations/009_directorio.sql) — agrupados por nivel jerárquico
// para la vista. "nombre" queda NULL en los cargos donde no hay una
// persona real registrada todavía (ver comentario de la migración).
if ($uri === '/directorio') {
    $directorioPorNivel = [];
    foreach ($pdo->query('SELECT nombre, cargo, direccion, nivel, codigo FROM cargos ORDER BY orden')->fetchAll() as $c) {
        // Iniciales para el círculo de cada tarjeta — mismo criterio que ya
        // usa HomeController.php para cumpleaños. Sin nombre real, se usan
        // las iniciales del cargo para no dejar el círculo vacío.
        $base = $c['nombre'] ?: $c['cargo'];
        $partes = preg_split('/\s+/', trim($base));
        $c['ini'] = strtoupper(mb_substr($partes[0], 0, 1) . mb_substr(end($partes), 0, 1));
        $directorioPorNivel[$c['nivel']][] = $c;
    }
}

// ── Calendario (fase 4) ──
// Reusa "eventos" (ver eventos.php en Inicio) — no hay tabla nueva. El mes
// visible viene de ?mes=YYYY-MM (navegación con recarga completa, este
// portal no es una SPA — ver sidebar.php); sin ese parámetro o si viene
// mal formado, se usa el mes actual.
if ($uri === '/calendario') {
    $MESES_LARGO = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $DIAS_SEMANA = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

    $mesParam = $_GET['mes'] ?? '';
    $primerDia = preg_match('/^\d{4}-\d{2}$/', $mesParam)
        ? DateTime::createFromFormat('Y-m-d', $mesParam . '-01')
        : false;
    if (!$primerDia) {
        $primerDia = new DateTime('first day of this month');
    }

    $calMesActual = $primerDia->format('Y-m');
    $calMesAnterior = (clone $primerDia)->modify('-1 month')->format('Y-m');
    $calMesSiguiente = (clone $primerDia)->modify('+1 month')->format('Y-m');
    $calTituloMes = $MESES_LARGO[(int) $primerDia->format('n') - 1] . ' ' . $primerDia->format('Y');

    $eventosPorDia = [];
    // Un evento privado solo lo ve quien lo creó — el resto ("publico",
    // o filas viejas sin usuario_id) se ve igual que siempre.
    $stmt = $pdo->prepare("SELECT id, usuario_id, titulo, hora_lugar, fecha, visibilidad FROM eventos
        WHERE fecha >= :inicio AND fecha <= :fin
        AND (visibilidad = 'publico' OR usuario_id = :usuario_id)
        ORDER BY fecha");
    $stmt->execute([
        ':inicio'     => $primerDia->format('Y-m-01'),
        ':fin'        => $primerDia->format('Y-m-t'),
        ':usuario_id' => $_SESSION['usuario_id'],
    ]);
    foreach ($stmt->fetchAll() as $ev) {
        $eventosPorDia[(int) (new DateTime($ev['fecha']))->format('j')][] = $ev;
    }

    // Grid de semanas: relleno con celdas vacías antes del día 1 (lunes=1)
    // y después del último día, para que las semanas siempre den 7 celdas.
    $diasEnMes = (int) $primerDia->format('t');
    $primerDiaSemanaISO = (int) $primerDia->format('N'); // 1=lunes … 7=domingo
    $celdas = array_fill(0, $primerDiaSemanaISO - 1, null);
    for ($d = 1; $d <= $diasEnMes; $d++) {
        $celdas[] = $d;
    }
    while (count($celdas) % 7 !== 0) {
        $celdas[] = null;
    }
    $calSemanas = array_chunk($celdas, 7);
    $calHoy = ((new DateTime('today'))->format('Y-m') === $calMesActual) ? (int) (new DateTime('today'))->format('j') : null;
}

// ── Mapa del portal ──
if ($uri === '/mapa-portal') {
    // 'Inicio' no está en $modulos (lo sirve HomeController en '/'); el resto
    // de labels sí coincide 1:1 con el 'titulo' de $modulos, así que se
    // reusa esa misma tabla de rutas en vez de duplicarlas acá.
    $rutasPorTitulo = ['Inicio' => '/'];
    foreach ($modulos as $ruta => $info) {
        $rutasPorTitulo[$info['titulo']] = $ruta;
    }

    $sitemapModulos = [];
    foreach ($pdo->query('SELECT id, nivel, label, icono, descripcion FROM sitemap_modulos ORDER BY orden')->fetchAll() as $m) {
        $stmt = $pdo->prepare('SELECT label FROM sitemap_items WHERE modulo_id = :id ORDER BY orden');
        $stmt->execute([':id' => $m['id']]);
        $m['hijos'] = array_column($stmt->fetchAll(), 'label');
        $m['ruta'] = $rutasPorTitulo[$m['label']] ?? null;
        $sitemapModulos[] = $m;
    }
}

// ── Gestión Documental ──
// carpetas_documentales es un árbol de 2 niveles (dirección → área, vía
// parent_id); los archivos cuelgan del nivel de área (hoja), nunca del
// nivel de dirección.
if ($uri === '/gestion-documental') {
    $archivosPorCarpeta = [];
    foreach ($pdo->query('SELECT id, carpeta_id, nombre, tipo, version, estado, responsable, archivo, fecha FROM archivos_documentales ORDER BY fecha DESC')->fetchAll() as $a) {
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
