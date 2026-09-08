<?php

require_once __DIR__ . '/inc/common.php';

require_once __DIR__ . '/inc/cache.php';

$catalog_css_path = __DIR__ . '/assets/css/cursos-gratuitos.css';
$catalog_css_version = is_file($catalog_css_path) ? filemtime($catalog_css_path) : '1';



// Headers para evitar cache

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Cache-Control: post-check=0, pre-check=0", false);

header("Pragma: no-cache");



$enlace = conectar_bbdd();



// ==========================================

// FILTROS SEPARADOS PARA CONSORCI Y FOAP

// ==========================================



// CONSORCI: Solo muestra grupos con mostrar_web = 1 (ignora el estado del curso padre)

$where_conditions_consorci = ["g.mostrar_web = 1"];

$where_conditions_consorci[] = "g.data_final >= CURDATE()";



// FOAP: Muestra si curso O grupo están visibles (permite NULL en grupo)

$where_conditions_foap = ["g.mostrar_web = 1"];

$where_conditions_foap[] = "(g.data_final >= CURDATE() OR g.data_final IS NULL)";



// Filtros comunes aplicables a ambos

if (!empty($_GET['modalidad'])) {

    $modalidad = mysqli_real_escape_string($enlace, $_GET['modalidad']);

    if ($modalidad == 'mixta') {

        $modalidad = 'blended';

    }

    $filter_modalidad = "LOWER(g.modalitat) = '$modalidad'";

    $where_conditions_consorci[] = $filter_modalidad;

    $where_conditions_foap[] = $filter_modalidad;

}



if (!empty($_GET['mes'])) {

    $meses = [

        'enero' => '01', 'febrero' => '02', 'marzo' => '03',

        'abril' => '04', 'mayo' => '05', 'junio' => '06',

        'julio' => '07', 'agosto' => '08', 'septiembre' => '09',

        'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12'

    ];

    $mes_num = $meses[$_GET['mes']] ?? '';

    if ($mes_num) {

        $filter_mes = "SUBSTRING(g.data_inici, 6, 2) = '$mes_num'";

        $where_conditions_consorci[] = $filter_mes;

        $where_conditions_foap[] = $filter_mes;

    }

}



if (!empty($_GET['franja'])) {

    $franja = mysqli_real_escape_string($enlace, $_GET['franja']);

    $filter_franja = '';

    if ($franja == 'manana') {

        $filter_franja = "EXISTS (

            SELECT 1 FROM gen_grups_horaris h

            WHERE h.grup = g.id 

            AND HOUR(h.hora_inici) < 13

        )";

    } else if ($franja == 'tarde') {

        $filter_franja = "EXISTS (

            SELECT 1 FROM gen_grups_horaris h

            WHERE h.grup = g.id 

            AND HOUR(h.hora_inici) >= 16

        )";

    } else if ($franja == 'sabado') {

        $filter_franja = "EXISTS (

            SELECT 1 FROM gen_grups_horaris h

            WHERE h.grup = g.id 

            AND h.dies LIKE '%Ds%'

        )";

    }

    if ($filter_franja) {

        $where_conditions_consorci[] = $filter_franja;

        $where_conditions_foap[] = $filter_franja;

    }

}



// Filtro de área (solo CONSORCI)

$area_filter_consorci = '';

$area_filter_foap = ''; // FOAP no tiene filtro de área actualmente

if (!empty($_GET['area'])) {

    $areas = [

        'administracion' => 'ADMINISTRACIÓ',

        'idiomas' => 'IDIOMES',

        'informatica' => 'INFORMÀTICA',

        'interpersonales' => 'HABILITATS INTERPERSONALS',

        'marketing' => 'Marketing',

        'comercio' => 'COMERÇ',

        'educacion' => 'EDUCACIÓ'

    ];

    $area_buscar = $areas[$_GET['area']] ?? '';

    if ($area_buscar) {

        $area_filter_consorci = "AND EXISTS (

            SELECT 1 FROM gen_materies_interes_subvencionada m

            WHERE m.id = g.area_interes_soc

            AND m.materia LIKE '%$area_buscar%'

        )";

    }

}



// ==========================================

// SISTEMA DUAL: CONSORCI (grupos) + FOAP (grupos)

// ==========================================



// 1. CONSORCI: Cargar por GRUPOS (cada grupo = una tarjeta)

$query_grupos_consorci = "

    SELECT 

        g.id as id_grup,

        c.id as id_curs, 

        c.nom_comercial as curso_nom_comercial,

        g.nom_comercial,

        g.nom,

        g.hores_modul as grupo_hores,

        g.data_final,

        g.data_inici,

        TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,

        c.tipus_subvencionada,

        c.especialitat_formativa,

        g.modalitat,

        g.professor,

        CONCAT(p.nom, ' ', p.cognoms) as professor_nom,

        m.materia as area_interes_nombre,

        'grupo' as tipo_entrada

    FROM gen_cursos c

    INNER JOIN gen_grups g ON g.curs = c.id

    LEFT JOIN gen_personal p ON p.id = g.professor

    LEFT JOIN gen_materies_interes_subvencionada m ON m.id = g.area_interes_soc

    WHERE c.tipus_subvencionada = 'CONSORCI'

    AND " . implode(' AND ', $where_conditions_consorci) . "

    $area_filter_consorci

    ORDER BY g.data_inici ASC

";



$result_grupos = cache()->sql($query_grupos_consorci, function() use ($enlace, $query_grupos_consorci) {

    return mysqli_query($enlace, $query_grupos_consorci);

});

$items = [];

$slug_count = []; // Para rastrear slugs duplicados globalmente (CONSORCI + FOAP)

$filtro_mes_activo = !empty($_GET['mes']); // Detectar si hay filtro de mes



// Nueva lógica: primera aparición de un slug = limpio, siguientes = añadir mes

// EXCEPCIÓN: Si hay filtro de mes, SIEMPRE añadir mes (incluso en primera aparición)

while ($row = fetch_result($result_grupos)) {

  // Obtener imagen y duración para cada grupo CONSORCI

  // Prioridad horas: 1) g.hores_modul (del grupo) 2) af.hores (acción formativa)

  $q_af = "SELECT af.imatge, af.hores as total_hores

           FROM gen_grups g

           LEFT JOIN soc_accions_formatives af ON af.id = g.accio_formativa

           WHERE g.id = '".mysqli_real_escape_string($enlace, $row['id_grup'])."'

           LIMIT 1";

  $result_af = cache()->sql($q_af, function() use ($enlace, $q_af) {

      return mysqli_query($enlace, $q_af);

  });

  if ($result_af && $af_data = fetch_result($result_af)) {

    $row['imatge'] = $af_data['imatge'];

    // Prioridad: hores_modul del grupo, si no hay usar horas de acción formativa

    if (!empty($row['grupo_hores']) && $row['grupo_hores'] > 0) {

      $row['total_hores'] = $row['grupo_hores'];

    } else {

      $row['total_hores'] = $af_data['total_hores'];

    }

  } else {

    $row['total_hores'] = $row['grupo_hores'];

  }

  

  // Obtener horarios del grupo

  if (!empty($row['id_grup'])) {

    $query_horarios = "

      SELECT h.hora_inici, h.hora_final, h.dies

      FROM gen_grups_horaris h

      WHERE h.grup = '".mysqli_real_escape_string($enlace, $row['id_grup'])."'

      LIMIT 1

    ";

    $result_horarios = cache()->sql($query_horarios, function() use ($enlace, $query_horarios) {

        return mysqli_query($enlace, $query_horarios);

    });

    $horarios = fetch_result($result_horarios);

    $row['hora_inici'] = substr($horarios['hora_inici'] ?? '', 0, 5);

    $row['hora_final'] = substr($horarios['hora_final'] ?? '', 0, 5);

    $row['dies'] = $horarios['dies'] ?? '';

  }

  

  // Determinar el nombre a mostrar: prioridad g.nom_comercial > g.nom > extraído > curso

  if (!empty($row['nom_comercial']) && trim($row['nom_comercial']) != '') {

    $row['nom_curs'] = trim($row['nom_comercial']);

  } else if (!empty($row['nom']) && trim($row['nom']) != '') {

    $row['nom_curs'] = trim($row['nom']);

  } else if (empty($row['nom_curs']) || trim($row['nom_curs']) == '') {

    $row['nom_curs'] = $row['curso_nom_comercial'];

  }

  

  $slug_base = slugify($row['nom_curs']);

  

  // Si hay filtro de mes activo, FORZAR añadir mes al slug

  if ($filtro_mes_activo) {

    $meses_ca = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

    $mes_num = (int)date('n', strtotime($row['data_inici']));

    $mes_ca = $meses_ca[$mes_num];

    $row['slug'] = $slug_base . '-' . $mes_ca;

    $slug_count[$slug_base] = ($slug_count[$slug_base] ?? 0) + 1;

  } else {

    // Sistema unificado: primera vez = slug limpio, repetidos = añadir mes

    if (!isset($slug_count[$slug_base])) {

      $slug_count[$slug_base] = 1;

      $row['slug'] = $slug_base;

    } else {

      $slug_count[$slug_base]++;

      $meses_ca = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

      $mes_num = (int)date('n', strtotime($row['data_inici']));

      $mes_ca = $meses_ca[$mes_num];

      $row['slug'] = $slug_base . '-' . $mes_ca;

    }

  }

  $items[] = $row;

}



// 2. FOAP: Cargar por GRUPOS (cada grupo = una tarjeta)

// Muestra si el curso O el grupo tienen mostrar_web = 1



$where_foap = [];

$where_foap[] = "c.tipus_subvencionada = 'FOAP'";

$where_foap[] = "g.mostrar_web = 1";

$where_foap[] = "(g.data_final >= CURDATE() OR g.data_final IS NULL)";



// Filtros opcionales

if (!empty($_GET['modalidad'])) {

    $modalidad = mysqli_real_escape_string($enlace, $_GET['modalidad']);

    if ($modalidad == 'mixta') {

        $modalidad = 'blended';

    }

    $where_foap[] = "LOWER(g.modalitat) = '$modalidad'";

}



if (!empty($_GET['mes'])) {

    $meses = [

        'enero' => '01', 'febrero' => '02', 'marzo' => '03',

        'abril' => '04', 'mayo' => '05', 'junio' => '06',

        'julio' => '07', 'agosto' => '08', 'septiembre' => '09',

        'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12'

    ];

    $mes_num = $meses[$_GET['mes']] ?? '';

    if ($mes_num) {

        $where_foap[] = "SUBSTRING(g.data_inici, 6, 2) = '$mes_num'";

    }

}



if (!empty($_GET['franja'])) {

    $franja = mysqli_real_escape_string($enlace, $_GET['franja']);

    if ($franja == 'manana') {

        $where_foap[] = "EXISTS (

            SELECT 1 FROM gen_grups_horaris h

            WHERE h.grup = g.id 

            AND HOUR(h.hora_inici) < 13

        )";

    } else if ($franja == 'tarde') {

        $where_foap[] = "EXISTS (

            SELECT 1 FROM gen_grups_horaris h

            WHERE h.grup = g.id 

            AND HOUR(h.hora_inici) >= 16

        )";

    } else if ($franja == 'sabado') {

        $where_foap[] = "EXISTS (

            SELECT 1 FROM gen_grups_horaris h

            WHERE h.grup = g.id 

            AND h.dies LIKE '%Ds%'

        )";

    }

}



$query_grupos_foap = "

  SELECT 

    g.id as id_grup,

    c.id as id_curs, 

    c.nom_comercial as curso_nom_comercial,

    g.nom_comercial,

    g.nom,

    g.nom_soc,

    g.hores_modul as grupo_hores,

    c.data_inici as curso_data_inici,

    c.data_fi as curso_data_fi,

    TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,

    COALESCE(g.data_final, c.data_fi) as data_final, 

    COALESCE(g.data_inici, c.data_inici) as data_inici,

    c.tipus_subvencionada,

    c.especialitat_formativa,

    g.modalitat,

    g.professor,

    CONCAT(p.nom, ' ', p.cognoms) as professor_nom,

    m.modul as nombre_modulo,

    ma.materia as area_interes_nombre,

    'grupo' as tipo_entrada

  FROM gen_cursos c

  INNER JOIN gen_grups g ON g.curs = c.id

  LEFT JOIN soc_moduls m ON m.id = g.nom_soc

  LEFT JOIN gen_personal p ON p.id = g.professor

  LEFT JOIN gen_materies_interes_subvencionada ma ON ma.id = g.area_interes_soc

  WHERE " . implode(' AND ', $where_foap) . "

  ORDER BY COALESCE(g.data_inici, c.data_inici) ASC

";



$result_grupos_foap = cache()->sql($query_grupos_foap, function() use ($enlace, $query_grupos_foap) {

    return mysqli_query($enlace, $query_grupos_foap);

});



// Procesar cada grupo FOAP

while ($row = fetch_result($result_grupos_foap)) {

  // Prioridad horas: 1) g.hores_modul (del grupo) 2) soc_especialitats_formatives (especialidad)

  // Para FOAP: datos de soc_especialitats_formatives (solo si no hay datos del grupo)

  $q_ef = "SELECT imatge, COALESCE(hores_cen, 0) + COALESCE(hores_practiques, 0) as total_hores 

       FROM soc_especialitats_formatives 

       WHERE id = '".mysqli_real_escape_string($enlace, $row['especialitat_formativa'])."'";

  $result_ef = cache()->sql($q_ef, function() use ($enlace, $q_ef) {

      return mysqli_query($enlace, $q_ef);

  });

  if ($result_ef && $ef_data = fetch_result($result_ef)) {

    $row['imatge'] = $ef_data['imatge'];

    // Prioridad: hores_modul del grupo, si no hay usar horas de especialidad formativa

    if (!empty($row['grupo_hores']) && $row['grupo_hores'] > 0) {

      $row['total_hores'] = $row['grupo_hores'];

    } else {

      $row['total_hores'] = $ef_data['total_hores'];

    }

  } else {

    $row['total_hores'] = $row['grupo_hores'];

  }

  

  // Determinar el nombre a mostrar para FOAP con módulos:

  // Prioridad: nombre_modulo (de soc_moduls) > g.nom_comercial > g.nom > nom_curs extraído > curso

  if (!empty($row['nombre_modulo']) && trim($row['nombre_modulo']) != '') {

    // Limpiar códigos FOAP del nombre del módulo

    $nombre_limpio = preg_replace('/\d+\/FOAP\/\d+\/\d+\/\d+/', '', $row['nombre_modulo']);

    $row['nom_curs'] = trim($nombre_limpio);

  } else if (!empty($row['nom_comercial']) && trim($row['nom_comercial']) != '') {

    $row['nom_curs'] = trim($row['nom_comercial']);

  } else if (!empty($row['nom']) && trim($row['nom']) != '') {

    $row['nom_curs'] = trim($row['nom']);

  } else if (empty($row['nom_curs']) || trim($row['nom_curs']) == '') {

    $row['nom_curs'] = $row['curso_nom_comercial'];

  }

  

  // Obtener horarios del grupo (solo si existe grupo)

  if (!empty($row['id_grup'])) {

    $query_horarios = "

      SELECT h.hora_inici, h.hora_final, h.dies

      FROM gen_grups_horaris h

      WHERE h.grup = '".mysqli_real_escape_string($enlace, $row['id_grup'])."'

      LIMIT 1

    ";

    $result_horarios = cache()->sql($query_horarios, function() use ($enlace, $query_horarios) {

        return mysqli_query($enlace, $query_horarios);

    });

    $horarios = fetch_result($result_horarios);

    $row['hora_inici'] = substr($horarios['hora_inici'] ?? '', 0, 5);

    $row['hora_final'] = substr($horarios['hora_final'] ?? '', 0, 5);

    $row['dies'] = $horarios['dies'] ?? '';

  } else {

    // Si no hay grupo, no hay horarios específicos

    $row['hora_inici'] = '';

    $row['hora_final'] = '';

    $row['dies'] = '';

  }

  

  // Generar slug: si ya existe, agregar mes en catalán

  $slug_base = slugify($row['nom_curs']);

  

  // Si hay filtro de mes activo, FORZAR añadir mes al slug

  if ($filtro_mes_activo) {

    $meses_ca = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

    $mes_num = !empty($row['data_inici']) ? (int)date('n', strtotime($row['data_inici'])) : 1;

    $mes_ca = $meses_ca[$mes_num];

    $row['slug'] = $slug_base . '-' . $mes_ca;

    $slug_count[$slug_base] = ($slug_count[$slug_base] ?? 0) + 1;

  } else {

    if (!isset($slug_count[$slug_base])) {

      // Primera vez que aparece este slug: mantener limpio

      $slug_count[$slug_base] = 1;

      $row['slug'] = $slug_base;

    } else {

      // Ya existe: agregar mes en catalán de la fecha de inicio

      $slug_count[$slug_base]++;

      $meses_ca = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

      $mes_num = !empty($row['data_inici']) ? (int)date('n', strtotime($row['data_inici'])) : 1;

      $mes_ca = $meses_ca[$mes_num];

      $row['slug'] = $slug_base . '-' . $mes_ca;

    }

  }

  $items[] = $row;

}



// ==========================================

// 3. CURSOS COMPLETOS (además de los grupos)

// ==========================================



// 3a. CONSORCI: Cargar CURSOS como entrada adicional (solo si NO tienen grupos activos)

$query_cursos_consorci = "

    SELECT 

        NULL as id_grup,

        c.id as id_curs, 

        c.nom_comercial as curso_nom_comercial,

        NULL as nom_comercial,

        NULL as nom,

        NULL as grupo_hores,

        TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,

        c.data_fi as data_final, 

        c.data_inici,

        c.tipus_subvencionada,

        c.especialitat_formativa,

        NULL as modalitat,

        'curso' as tipo_entrada

    FROM gen_cursos c

    WHERE c.tipus_subvencionada = 'CONSORCI'

    AND c.mostrar_web = 1

    AND c.data_fi >= CURDATE()

    AND NOT EXISTS (

        SELECT 1 FROM gen_grups g 

        WHERE g.curs = c.id 

        AND g.data_final >= CURDATE()

        AND g.mostrar_web = 1

    )

    $area_filter_consorci

    ORDER BY c.data_inici ASC

";



$result_cursos = cache()->sql($query_cursos_consorci, function() use ($enlace, $query_cursos_consorci) {

    return mysqli_query($enlace, $query_cursos_consorci);

});



while ($row = fetch_result($result_cursos)) {

  // Obtener imagen y horas de la acción formativa (a través de un grupo del curso)

  $q_af = "SELECT af.imatge, af.hores as total_hores

           FROM gen_grups g

           LEFT JOIN soc_accions_formatives af ON af.id = g.accio_formativa

           WHERE g.curs = '".mysqli_real_escape_string($enlace, $row['id_curs'])."'

           LIMIT 1";

  $result_af = cache()->sql($q_af, function() use ($enlace, $q_af) {

      return mysqli_query($enlace, $q_af);

  });

  if ($result_af && $af_data = fetch_result($result_af)) {

    $row['imatge'] = $af_data['imatge'];

    $row['total_hores'] = $af_data['total_hores'];

  }

  

  $row['nom_curs'] = trim($row['nom_curs']);

  $row['hora_inici'] = '';

  $row['hora_final'] = '';

  

  // Generar slug

  $slug_base = slugify($row['nom_curs']);

  if ($filtro_mes_activo) {

    $meses_ca = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

    $mes_num = !empty($row['data_inici']) ? (int)date('n', strtotime($row['data_inici'])) : 1;

    $mes_ca = $meses_ca[$mes_num];

    $row['slug'] = $slug_base . '-' . $mes_ca;

    $slug_count[$slug_base] = ($slug_count[$slug_base] ?? 0) + 1;

  } else {

    if (!isset($slug_count[$slug_base])) {

      $slug_count[$slug_base] = 1;

      $row['slug'] = $slug_base;

    } else {

      $slug_count[$slug_base]++;

      $meses_ca = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

      $mes_num = !empty($row['data_inici']) ? (int)date('n', strtotime($row['data_inici'])) : 1;

      $mes_ca = $meses_ca[$mes_num];

      $row['slug'] = $slug_base . '-' . $mes_ca;

    }

  }

  $items[] = $row;

}



// 3b. FOAP: Cargar CURSOS padre (aparecen aunque tengan grupos activos)

$query_cursos_foap = "

  SELECT 

    NULL as id_grup,

    c.id as id_curs, 

    c.nom_comercial as curso_nom_comercial,

    NULL as nom_comercial,

    NULL as nom,

    NULL as grupo_hores,

    c.data_inici as curso_data_inici,

    c.data_fi as curso_data_fi,

    TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,

    c.data_fi as data_final, 

    c.data_inici,

    c.tipus_subvencionada,

    c.especialitat_formativa,

    NULL as modalitat,

    'curso' as tipo_entrada

  FROM gen_cursos c

  WHERE c.tipus_subvencionada = 'FOAP'

  AND c.mostrar_web = 1

  AND (c.data_fi >= CURDATE() OR c.data_fi IS NULL)

  AND NOT EXISTS (
    SELECT 1 FROM gen_grups g
    WHERE g.curs = c.id
      AND g.mostrar_web = 1
      AND (g.data_final >= CURDATE() OR g.data_final IS NULL)
  )

  $area_filter_foap

  ORDER BY c.data_inici ASC

";



$result_cursos_foap = cache()->sql($query_cursos_foap, function() use ($enlace, $query_cursos_foap) {

    return mysqli_query($enlace, $query_cursos_foap);

});



while ($row = fetch_result($result_cursos_foap)) {

  // Obtener imagen y horas de especialidad formativa

  $q_ef = "SELECT imatge, COALESCE(hores_cen, 0) + COALESCE(hores_practiques, 0) as total_hores 

       FROM soc_especialitats_formatives 

       WHERE id = '".mysqli_real_escape_string($enlace, $row['especialitat_formativa'])."'";

  $result_ef = cache()->sql($q_ef, function() use ($enlace, $q_ef) {

      return mysqli_query($enlace, $q_ef);

  });

  if ($result_ef && $ef_data = fetch_result($result_ef)) {

    $row['imatge'] = $ef_data['imatge'];

    $row['total_hores'] = $ef_data['total_hores'];

  }

  

  $row['nom_curs'] = trim($row['nom_curs']) . ' - Curs complet';

  $row['hora_inici'] = '';

  $row['hora_final'] = '';

  

  // Generar slug

  $slug_base = slugify($row['nom_curs']);

  if ($filtro_mes_activo) {

    $meses_ca = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

    $mes_num = !empty($row['data_inici']) ? (int)date('n', strtotime($row['data_inici'])) : 1;

    $mes_ca = $meses_ca[$mes_num];

    $row['slug'] = $slug_base . '-' . $mes_ca;

    $slug_count[$slug_base] = ($slug_count[$slug_base] ?? 0) + 1;

  } else {

    if (!isset($slug_count[$slug_base])) {

      $slug_count[$slug_base] = 1;

      $row['slug'] = $slug_base;

    } else {

      $slug_count[$slug_base]++;

      $meses_ca = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

      $mes_num = !empty($row['data_inici']) ? (int)date('n', strtotime($row['data_inici'])) : 1;

      $mes_ca = $meses_ca[$mes_num];

      $row['slug'] = $slug_base . '-' . $mes_ca;

    }

  }

  $items[] = $row;

}



// Ordenar todos los items por fecha de inicio (cursos sin fecha al final)

// Si tienen la misma fecha, cursos padre antes que módulos

usort($items, function($a, $b) {

    // Si ambos tienen fecha, comparar normalmente

    if (!empty($a['data_inici']) && !empty($b['data_inici'])) {

        $fecha_compare = strcmp($a['data_inici'], $b['data_inici']);

        // Si tienen la misma fecha, priorizar cursos completos antes que módulos

        if ($fecha_compare === 0) {

            // tipo_entrada = 'curso' va antes que 'grupo'

            if ($a['tipo_entrada'] === 'curso' && $b['tipo_entrada'] === 'grupo') {

                return -1; // a va antes

            }

            if ($a['tipo_entrada'] === 'grupo' && $b['tipo_entrada'] === 'curso') {

                return 1; // b va antes

            }

        }

        return $fecha_compare;

    }

    // Si solo 'a' tiene fecha, 'a' va primero

    if (!empty($a['data_inici'])) {

        return -1;

    }

    // Si solo 'b' tiene fecha, 'b' va primero

    if (!empty($b['data_inici'])) {

        return 1;

    }

    // Si ninguno tiene fecha, mantener orden

    return 0;

});



$cursos = $items;



// Obtener reviews de Google

$google_reviews_data = obtener_google_reviews('es');



?>

<!doctype html>

<html lang="es">

<head>

<meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Cookiebot - DEBE IR AL INICIO DEL HEAD -->
<script id="Cookiebot" src="https://consent.cookiebot.com/uc.js" 
data-cbid="81b5e0c8-3469-405a-bbbb-6a74eaa4fee0" 
data-blockingmode="auto" 
type="text/javascript"></script>
<!-- End Cookiebot -->

<link rel="icon" type="image/jpeg" sizes="32x32" href="https://thecorner.es/wp-content/uploads/2024/05/cropped-THE-CORNER-2022-Online-Perfiles-32x32.jpg">

<link rel="shortcut icon" href="https://thecorner.es/wp-content/uploads/2024/05/cropped-THE-CORNER-2022-Online-Perfiles-32x32.jpg">

<title>Formación Subvencionada - Cursos gratuitos | The Corner</title>

<meta name="description" content="Descubre nuestros cursos gratuitos subvencionados en Santa Coloma de Gramenet. Formación en idiomas, informática, marketing, administración y más. ¡Inscríbete ahora!">



<link rel="canonical" href="https://thecorner.es/cursos/cursos-gratuitos">

<link rel="alternate" hreflang="es" href="https://thecorner.es/cursos/cursos-gratuitos">

<link rel="alternate" hreflang="ca" href="https://thecorner.es/cursos/cursos-gratuitos-ca">

<link rel="alternate" hreflang="x-default" href="https://thecorner.es/cursos/cursos-gratuitos">



<!-- Open Graph -->

<meta property="og:title" content="Formación Subvencionada - Cursos gratuitos | The Corner">

<meta property="og:description" content="Descubre nuestros cursos gratuitos subvencionados en Santa Coloma de Gramenet. Formación en idiomas, informática, marketing, administración y más.">

<meta property="og:type" content="website">

<meta property="og:url" content="https://thecorner.es/cursos/cursos-gratuitos">

<meta property="og:locale" content="es_ES">

<meta property="og:locale:alternate" content="ca_ES">

<meta property="og:site_name" content="The Corner">

<meta property="og:image" content="https://thecorner.es/wp-content/uploads/elementor/thumbs/cropped-01-THE-CORNER-2022-Logo-Positivo-qnmjwwdfta003wl6myxgilfaqn9r3cxbdk815xm49o.png">

<meta property="og:image:alt" content="The Corner - Cursos gratuitos subvencionados">



<!-- Twitter Card -->

<meta name="twitter:card" content="summary_large_image">

<meta name="twitter:title" content="Formación Subvencionada - Cursos gratuitos | The Corner">

<meta name="twitter:description" content="Descubre nuestros cursos gratuitos subvencionados en Santa Coloma de Gramenet.">

<meta name="twitter:image" content="https://thecorner.es/wp-content/uploads/elementor/thumbs/cropped-01-THE-CORNER-2022-Logo-Positivo-qnmjwwdfta003wl6myxgilfaqn9r3cxbdk815xm49o.png">





<!-- Structured Data -->

<script type="application/ld+json">

{

  "@context": "https://schema.org",

  "@type": "EducationalOrganization",

  "name": "The Corner - Centro de Formación",

  "url": "https://thecorner.es",

  "logo": "https://thecorner.es/wp-content/uploads/elementor/thumbs/cropped-01-THE-CORNER-2022-Logo-Positivo-qnmjwwdfta003wl6myxgilfaqn9r3cxbdk815xm49o.png",

  "description": "Centro de formación en Santa Coloma de Gramenet especializado en cursos gratuitos subvencionados",

  "address": {

    "@type": "PostalAddress",

    "addressLocality": "Santa Coloma de Gramenet",

    "addressCountry": "ES"

  }

}

</script>



<!-- Preload de fuentes críticas (Roboto Regular y Bold) -->

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link rel="preload" as="font" type="font/woff2" href="https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu4mxK.woff2" crossorigin>

<link rel="preload" as="font" type="font/woff2" href="https://fonts.gstatic.com/s/roboto/v30/KFOlCnqEu92Fr1MmWUlfBBc4.woff2" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">



<!-- Preload imagen Hero (LCP) -->

<link rel="preload" as="image" href="https://thecorner.es/wp-content/uploads/2025/11/THE-CORNER-Subvencionados-2026-Landing-Elementos2.jpg" fetchpriority="high">



<!-- CSS crítico - carga bloqueante para evitar FOUC -->

<link rel="stylesheet" href="/cursos/assets/css/cursos-gratuitos.css?v=<?= $catalog_css_version ?>">


<!-- Google Tag Manager (noscript) -->
<noscript>
<iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PD4C2ZNK"
height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->


<!-- Meta Pixel Code -->
<script data-cookieconsent="marketing" type="text/plain">
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1755236761371264');
fbq('track', 'PageView');
</script>

<noscript><img height="1" width="1" alt="fb-pixel" style="display:none"

src="https://www.facebook.com/tr?id=1755236761371264&ev=PageView&noscript=1"

/></noscript>

<!-- End Meta Pixel Code -->

<!-- Tiktok Pixel Code -->
<script data-cookieconsent="marketing" type="text/plain">
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;
  var ttq=w[t]=w[t]||[];
  ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'],
  ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
  for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
  ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},
  ttq.load=function(e,n){var i='https://analytics.tiktok.com/i18n/pixel/events.js';
    ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,
    ttq._o=ttq._o||{},ttq._o[e]=n||{};
    var o=document.createElement('script');
    o.type='text/javascript',o.async=!0,o.src=i+'?sdkid='+e+'&lib='+t;
    var a=document.getElementsByTagName('script')[0];
    a.parentNode.insertBefore(o,a)
  };
  ttq.load('CT2QBORC77U9L9BMHG70');
  ttq.page();
}(window, document, 'ttq');
</script>
<script data-cookieconsent="marketing" type="text/plain">ttq.track('Browse')</script>
<!-- End Tiktok Pixel Code -->

<!-- Serviceform Embed  -->

<script>

  var tD = (new Date).toISOString().slice(0,10);

  window.sf3pid = "gHgWrIFrCPlUWNr8AExN";

  var u = "https://dash.serviceform.com/embed/sf-pixel.js?" + tD,

      t = document.createElement("script");

  t.setAttribute("type","text/javascript");

  t.setAttribute("src", u);

  t.async = true;

  (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(t);

</script>

<!--  End Serviceform Embed -->



</head>



<body>



<header class="site-header">

  <div class="container header-inner">

    <a class="brand" href="/">

      <img src="https://thecorner.es/wp-content/uploads/2025/09/logo.png" alt="Centre d'Estudis The Corner - Santa Coloma de Gramenet" width="200" height="50" loading="lazy" style="height:50px;width:auto">

    </a>

    

    <nav class="nav-menu">

      <div class="nav-menu__item nav-menu__item--dropdown">

        Servicios

        <div class="nav-dropdown">

          <a href="https://thecorner.es/orientacion-profesional/" class="nav-dropdown__item">Orientación Laboral</a>

          <a href="https://thecorner.es/bolsa-de-trabajo/" class="nav-dropdown__item">Bolsa de Empleo</a>

          <a href="https://thecorner.es/formacion-para-empresas/" class="nav-dropdown__item">Formación para Empresas</a>

          <a href="https://thecorner.es/english-language-school/" class="nav-dropdown__item">The Corner Idiomas</a>

        </div>

      </div>

      <a href="https://leveltest.thecorner.es/" class="nav-menu__item">Comprueba tu Nivel de Inglés</a>

      <a href="https://thecorner.es/contacto/" class="nav-menu__item">Contacto</a>

      <a href="https://thecorner.es/quienes-somos/" class="nav-menu__item">Quienes somos</a>

      <div class="nav-menu__item nav-menu__item--dropdown lang-selector-menu">

        ES ▼

        <div class="nav-dropdown">

          <a href="/cursos/cursos-gratuitos" class="nav-dropdown__item active-lang" id="link-es">Español</a>

          <a href="/cursos/cursos-gratuitos-ca" class="nav-dropdown__item" id="link-ca">Català</a>

        </div>

      </div>

    </nav>

    

    <div class="mobile-menu-toggle">

      <span></span>

      <span></span>

      <span></span>

    </div>

  </div>

</header>



<main>



<section class="hero">

  <div class="container">

    <div class="hero-content">

      <h2 class="hero-title">

        <span class="hero-title__fake">FAKE NEWS</span>

        <span class="hero-title__good">GOOD NEWS</span>

      </h2>

      <h1 class="hero-subtitle">

        CURSOS SUBVENCIONADOS<br>

        EN SANTA COLOMA DE GRAMENET

      </h1>

      <p class="hero-tags">

        ADMINISTRATIVO • INGLÉS • MARKETING • HABILIDADES • INFORMÁTICA

      </p>

      <a class="hero-btn" href="#buscador">Ver cursos</a>

    </div>

  </div>

</section>



<?php

// ===== FUNCIÓN IMAGEN SEGURA =====

function curso_img($curso){

  $img = $curso['imatge'] ?? '';

  // Si la imagen está vacía o es nula, usar placeholder

  if (!$img || trim($img) === '') {

    return '/img/curso-placeholder.jpg';

  }

  // Si la imagen es relativa, añadir dominio

  if (strpos($img, 'http') !== 0) {

    $img = 'https://thecorner.es/' . ltrim($img,'/');

  }

  return $img;

}

?>



<?php

if (!function_exists('detect_course_featured_column')) {
  function detect_course_featured_column(mysqli $db): ?string
  {
    $candidate_fields = ['curs_destacat', 'destacat', 'destacado', 'curso_destacado'];

    foreach ($candidate_fields as $field) {
      $field_safe = mysqli_real_escape_string($db, $field);
      $result = mysqli_query($db, "SHOW COLUMNS FROM gen_cursos LIKE '{$field_safe}'");
      if ($result && mysqli_num_rows($result) > 0) {
        return $field;
      }
    }

    return null;
  }
}

$curso_destacado_field = detect_course_featured_column($enlace);

// Cursos destacados: usar misma lógica que listado normal (CONSORCI solo g.mostrar_web, FOAP c.mostrar_web O g.mostrar_web)
$query_destacados = "
  SELECT DISTINCT
    c.id as id_curs,
    c.nom_comercial as curso_nom_comercial,
    g.nom_comercial,
    g.nom,
    g.nom_soc,
    g.hores_modul as grupo_hores,
    TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,
    g.curs_destacat AS destacat,
    g.id as id_grup,
    g.data_inici,
    g.data_final,
    c.tipus_subvencionada,
    c.especialitat_formativa,
    g.modalitat,
    m.modul as nombre_modulo,
    'grupo' as tipo_entrada
  FROM gen_cursos c
  INNER JOIN gen_grups g ON g.curs = c.id
  LEFT JOIN soc_moduls m ON m.id = g.nom_soc
  WHERE g.curs_destacat = 1
    AND g.data_final >= CURDATE()
    AND (
      (c.tipus_subvencionada = 'CONSORCI' AND g.mostrar_web = 1) OR
      (c.tipus_subvencionada != 'CONSORCI' AND g.mostrar_web = 1)
    )
";

if ($curso_destacado_field) {
  $query_destacados .= "
  UNION ALL
  SELECT DISTINCT
    c.id as id_curs,
    c.nom_comercial as curso_nom_comercial,
    NULL as nom_comercial,
    NULL as nom,
    NULL as nom_soc,
    NULL as grupo_hores,
    TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,
    c.`{$curso_destacado_field}` AS destacat,
    NULL as id_grup,
    c.data_inici,
    c.data_fi as data_final,
    c.tipus_subvencionada,
    c.especialitat_formativa,
    NULL as modalitat,
    NULL as nombre_modulo,
    'curso' as tipo_entrada
  FROM gen_cursos c
  WHERE c.tipus_subvencionada = 'FOAP'
    AND c.`{$curso_destacado_field}` = 1
    AND c.mostrar_web = 1
    AND (c.data_fi >= CURDATE() OR c.data_fi IS NULL)
    AND NOT EXISTS (
      SELECT 1 FROM gen_grups g
      WHERE g.curs = c.id
        AND g.mostrar_web = 1
        AND (g.data_final >= CURDATE() OR g.data_final IS NULL)
    )
  ";
}

$query_destacados .= "
  ORDER BY data_inici ASC
  LIMIT 3
";
$result_destacados = cache()->sql($query_destacados, function() use ($enlace, $query_destacados) {

    return mysqli_query($enlace, $query_destacados);

});

$destacados = [];

while ($row = fetch_result($result_destacados)) {

  // Obtener imagen y duración según tipo de curso

  // Lógica unificada para imagen y duración

  if ($row['tipus_subvencionada'] == 'FOAP' && $row['especialitat_formativa']) {

    $q_ef = "SELECT imatge, COALESCE(hores_cen, 0) + COALESCE(hores_practiques, 0) as total_hores 

             FROM soc_especialitats_formatives WHERE id = '".mysqli_real_escape_string($enlace, $row['especialitat_formativa'])."'";

    $result_ef = cache()->sql($q_ef, function() use ($enlace, $q_ef) {

        return mysqli_query($enlace, $q_ef);

    });

    if ($result_ef && $ef_data = fetch_result($result_ef)) {

      $row['imatge'] = $ef_data['imatge'] ?? '';

      $row['total_hores'] = $ef_data['total_hores'] ?? '';

    }

  }

  // Siempre buscar imagen y duración en grupo (como en cards)

  if (isset($row['id_grup']) && $row['id_grup']) {

    $q_af = "SELECT af.imatge, af.hores as total_hores

             FROM gen_grups g

             LEFT JOIN soc_accions_formatives af ON af.id = g.accio_formativa

             WHERE g.id = '".mysqli_real_escape_string($enlace, $row['id_grup'])."'

             LIMIT 1";

    $result_af = cache()->sql($q_af, function() use ($enlace, $q_af) {

        return mysqli_query($enlace, $q_af);

    });

    if ($result_af && $af_data = fetch_result($result_af)) {

      $row['imatge'] = $af_data['imatge'] ?? $row['imatge'] ?? '';

      if (!empty($row['grupo_hores']) && $row['grupo_hores'] > 0) {
        $row['total_hores'] = $row['grupo_hores'];
      } else {
        $row['total_hores'] = $af_data['total_hores'] ?? $row['total_hores'] ?? '';
      }

    }

  }

  // Fallback si sigue vacío

  if (!isset($row['imatge']) || !$row['imatge']) {

    $row['imatge'] = '';

  }

  if (!isset($row['total_hores']) || !$row['total_hores']) {

    $row['total_hores'] = '';

  }

  if ($row['tipus_subvencionada'] == 'FOAP' && !empty($row['nombre_modulo']) && trim($row['nombre_modulo']) != '') {



    $nombre_limpio = preg_replace('/\d+\/FOAP\/\d+\/\d+\/\d+/', '', $row['nombre_modulo']);



    $row['nom_curs'] = trim($nombre_limpio);



  } else if (!empty($row['nom_comercial']) && trim($row['nom_comercial']) != '') {



    $row['nom_curs'] = trim($row['nom_comercial']);



  } else if (!empty($row['nom']) && trim($row['nom']) != '') {



    $row['nom_curs'] = trim($row['nom']);



  } else if (empty($row['nom_curs']) || trim($row['nom_curs']) == '') {

    $row['nom_curs'] = $row['curso_nom_comercial'] ?? '';

  }

  // Obtener horarios del grupo destacado

  if (isset($row['id_grup']) && $row['id_grup']) {

    $query_horarios = "

      SELECT h.dies, h.hora_inici, h.hora_final

      FROM gen_grups_horaris h

      WHERE h.grup = '".mysqli_real_escape_string($enlace, $row['id_grup'])."'

      LIMIT 1

    ";

    $result_horarios = cache()->sql($query_horarios, function() use ($enlace, $query_horarios) {

        return mysqli_query($enlace, $query_horarios);

    });

    $horarios = fetch_result($result_horarios);

    $row['dies'] = $horarios['dies'] ?? '';

    $row['hora_inici'] = substr($horarios['hora_inici'] ?? '', 0, 5);

    $row['hora_final'] = substr($horarios['hora_final'] ?? '', 0, 5);

  } else {

    $row['dies'] = '';

    $row['hora_inici'] = '';

    $row['hora_final'] = '';

  }

  // Generar slug del grupo destacado, igual que en el listado normal.

  $slug_base = slugify($row['nom_curs']);



  if (!empty($row['id_grup']) && !empty($row['data_inici'])) {
    $meses_ca = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];
    $mes_num = (int)date('n', strtotime($row['data_inici']));
    $mes_ca = $meses_ca[$mes_num] ?? '';
    $row['slug'] = $mes_ca ? $slug_base . '-' . $mes_ca : $slug_base;
  } else {
    $row['slug'] = $slug_base;
  }

  $destacados[] = $row;

}

?>
<!-- DESTACADOS -->

<section class="section section--tint">

  <div class="container">

    <h2 class="h2">Cursos destacados</h2>

    <div class="featured-grid">

      <?php foreach ($destacados as $curso): 

        // Saltar cursos sin título

        if (empty($curso['nom_curs'])) continue;

      ?>

      <article class="featured-card">

        <div class="featured-card__img">

          <img src="<?= curso_img($curso) ?: '/img/curso-placeholder.jpg' ?>" alt="<?= htmlspecialchars($curso['nom_curs']) ?>" width="280" height="280" loading="lazy" decoding="async">

        </div>

        <div class="featured-card__content">

          <div class="featured-card__meta">

            <?php if (!empty($curso['data_inici'])): ?>

            <div class="featured-card__date">

              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">

                <circle cx="12" cy="12" r="10" stroke-width="2"/>

                <path d="M12 6v6l4 2" stroke-width="2" stroke-linecap="round"/>

              </svg>

              <?= date_from_bdd($curso['data_inici']) ?>

            </div>

            <?php endif; ?>

            <div class="featured-card__rating">

              <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>

            </div>

          </div>

          <h3 class="featured-card__title">

            <?= htmlspecialchars($curso['nom_curs']) ?>

          </h3>

          <div class="featured-card__info">

            <div class="featured-card__info-item">

              <span class="featured-card__info-label">Modalidad:</span>

              <span><?= ucfirst($curso['modalitat'] ?? 'Presencial') == 'Blended' ? 'Mixta' : ucfirst($curso['modalitat'] ?? 'Presencial') ?></span>

            </div>

            <div class="featured-card__info-item">

              <span class="featured-card__info-label">| Duración:</span>

              <span><?= ($curso['total_hores'] && is_numeric($curso['total_hores'])) ? $curso['total_hores'].'h.' : 'No especificada.' ?></span>

            </div>

            <?php if($curso['hora_inici'] && $curso['hora_final']): ?>

            <div class="featured-card__info-item">

              <span><?= $curso['dies'] ?? '' ?> de <?= $curso['hora_inici'] ?>h. a <?= $curso['hora_final'] ?>h.</span>

            </div>

            <?php endif; ?>

          </div>

          <div class="featured-card__actions">

            <a href="/cursos/<?= $curso['slug'] ?>#reservar" class="btn--reserve">Reservar plaza</a>

            <a href="/cursos/<?= $curso['slug'] ?>" class="btn--info">Más información</a>

          </div>

        </div>

      </article>

      <?php endforeach; ?>

    </div>

  </div>

</section>



<!-- BUSCADOR -->

<section id="buscador" class="section">

  <div class="container">

    <h2 class="h2">Buscador de cursos</h2>

    

    <!-- Campo de búsqueda rápida con autocompletado -->

    <div class="quick-search-wrapper">

      <div class="quick-search">

        <input 

          type="text" 

          id="quickSearchInput" 

          placeholder="Buscar por temática, modalidad u horario..." 

          autocomplete="off"

        >

        <svg class="quick-search__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">

          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />

        </svg>

      </div>

      <div id="quickSearchSuggestions" class="quick-search-suggestions"></div>

    </div>

    

    <form class="filters" method="get">

      <div class="filter-field">

        <label>Temática</label>

        <select name="area" id="filter-area">

          <option value="">Todos</option>

          <option value="administracion" <?= isset($_GET['area']) && $_GET['area'] == 'administracion' ? 'selected' : '' ?>>Administración</option>

          <option value="idiomas" <?= isset($_GET['area']) && $_GET['area'] == 'idiomas' ? 'selected' : '' ?>>Idiomas</option>

          <option value="informatica" <?= isset($_GET['area']) && $_GET['area'] == 'informatica' ? 'selected' : '' ?>>Informática</option>

          <option value="interpersonales" <?= isset($_GET['area']) && $_GET['area'] == 'interpersonales' ? 'selected' : '' ?>>Habilidades interpersonales</option>

          <option value="marketing" <?= isset($_GET['area']) && $_GET['area'] == 'marketing' ? 'selected' : '' ?>>Marketing</option>

          <option value="comercio" <?= isset($_GET['area']) && $_GET['area'] == 'comercio' ? 'selected' : '' ?>>Comercio</option>

          <option value="educacion" <?= isset($_GET['area']) && $_GET['area'] == 'educacion' ? 'selected' : '' ?>>Educación</option>

        </select>

      </div>

      <div class="filter-field">

        <label>Mes</label>

        <select name="mes" id="filter-mes">

          <option value="">Todos</option>

          <option value="enero" <?= isset($_GET['mes']) && $_GET['mes'] == 'enero' ? 'selected' : '' ?>>Enero</option>

          <option value="febrero" <?= isset($_GET['mes']) && $_GET['mes'] == 'febrero' ? 'selected' : '' ?>>Febrero</option>

          <option value="marzo" <?= isset($_GET['mes']) && $_GET['mes'] == 'marzo' ? 'selected' : '' ?>>Marzo</option>

          <option value="abril" <?= isset($_GET['mes']) && $_GET['mes'] == 'abril' ? 'selected' : '' ?>>Abril</option>

          <option value="mayo" <?= isset($_GET['mes']) && $_GET['mes'] == 'mayo' ? 'selected' : '' ?>>Mayo</option>

          <option value="junio" <?= isset($_GET['mes']) && $_GET['mes'] == 'junio' ? 'selected' : '' ?>>Junio</option>

          <option value="julio" <?= isset($_GET['mes']) && $_GET['mes'] == 'julio' ? 'selected' : '' ?>>Julio</option>

          <option value="agosto" <?= isset($_GET['mes']) && $_GET['mes'] == 'agosto' ? 'selected' : '' ?>>Agosto</option>

          <option value="septiembre" <?= isset($_GET['mes']) && $_GET['mes'] == 'septiembre' ? 'selected' : '' ?>>Septiembre</option>

          <option value="octubre" <?= isset($_GET['mes']) && $_GET['mes'] == 'octubre' ? 'selected' : '' ?>>Octubre</option>

          <option value="noviembre" <?= isset($_GET['mes']) && $_GET['mes'] == 'noviembre' ? 'selected' : '' ?>>Noviembre</option>

          <option value="diciembre" <?= isset($_GET['mes']) && $_GET['mes'] == 'diciembre' ? 'selected' : '' ?>>Diciembre</option>

        </select>

      </div>

      <div class="filter-field">

        <label>Horario</label>

        <select name="franja" id="filter-franja">

          <option value="">Todos</option>

          <option value="manana" <?= isset($_GET['franja']) && $_GET['franja'] == 'manana' ? 'selected' : '' ?>>Mañana</option>

          <option value="tarde" <?= isset($_GET['franja']) && $_GET['franja'] == 'tarde' ? 'selected' : '' ?>>Tarde</option>

          <option value="sabado" <?= isset($_GET['franja']) && $_GET['franja'] == 'sabado' ? 'selected' : '' ?>>Sábado</option>

        </select>

      </div>

      <div class="filter-field">

        <label>Modalidad</label>

        <select name="modalidad" id="filter-modalidad">

          <option value="">Todos</option>

          <option value="presencial" <?= isset($_GET['modalidad']) && $_GET['modalidad'] == 'presencial' ? 'selected' : '' ?>>Presencial</option>

          <option value="online" <?= isset($_GET['modalidad']) && $_GET['modalidad'] == 'online' ? 'selected' : '' ?>>Online</option>

          <option value="mixta" <?= isset($_GET['modalidad']) && $_GET['modalidad'] == 'mixta' ? 'selected' : '' ?>>Mixta</option>

        </select>

      </div>

      <div class="filter-field">

        <label>Profesor</label>

        <select name="profesor" id="filter-profesor">

          <option value="">Todos</option>

          <?php

          // Obtener lista de profesores únicos de los cursos actuales

          $profesores_unicos = [];

          foreach ($cursos as $c) {

            $prof_nombre = trim($c['professor_nom'] ?? '');

            if ($prof_nombre) {

              // Normalizar a Primera Mayúscula

              $prof_nombre = ucwords(strtolower($prof_nombre));

              if (!in_array($prof_nombre, $profesores_unicos)) {

                $profesores_unicos[] = $prof_nombre;

              }

            }

          }

          sort($profesores_unicos);

          foreach ($profesores_unicos as $prof):

          ?>

          <option value="<?= htmlspecialchars($prof) ?>" <?= isset($_GET['profesor']) && $_GET['profesor'] == $prof ? 'selected' : '' ?>><?= htmlspecialchars($prof) ?></option>

          <?php endforeach; ?>

        </select>

      </div>

      <button type="button" id="reset-filters" class="btn--reset" title="Resetear filtros">

        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">

          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />

        </svg>

      </button>

      <button type="button" class="btn--help">¿Necesitas ayuda?</button>

    </form>

  </div>

</section>



<!-- LISTADO -->

<section class="section section--tint">

  <div class="container">

    <h2 class="h2">Cursos disponibles</h2>

    <div class="courses-grid">

      <?php foreach ($cursos as $curso): 

        $nombre_mostrar = $curso['nom_curs'] ?? $curso['nom_comercial'] ?? 'Curso sin nombre';

        $horas = $curso['total_hores'] ?? 'No especificada';

        

        // Extraer mes de la fecha de inicio para filtrado

        $mes_inicio = '';

        if (!empty($curso['data_inici'])) {

          $meses_nombres = [

            '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',

            '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',

            '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'

          ];

          $mes_num = date('m', strtotime($curso['data_inici']));

          $mes_inicio = $meses_nombres[$mes_num] ?? '';

        }

        

        $modalidad_limpia = strtolower($curso['modalitat'] ?? 'presencial');

        if ($modalidad_limpia == 'blended') $modalidad_limpia = 'mixta';

        

        // Nombre del profesor para filtrado (normalizado a Primera Mayúscula)

        $profesor_nombre = trim($curso['professor_nom'] ?? '');

        if ($profesor_nombre) {

          $profesor_nombre = ucwords(strtolower($profesor_nombre));

        }

        

        // Calcular franja horaria para filtrado

        $franja_horaria = '';

        if (!empty($curso['hora_inici'])) {

          $hora = (int)substr($curso['hora_inici'], 0, 2);

          if ($hora < 13) {

            $franja_horaria = 'manana';

          } elseif ($hora >= 16) {

            $franja_horaria = 'tarde';

          }

        }

        if (!empty($curso['dies']) && stripos($curso['dies'], 'Ds') !== false) {

          $franja_horaria = 'sabado';

        }

      ?>

      <?php

        // Mapeo de área para filtrado

        $area_nombre = $curso['area_interes_nombre'] ?? '';

        $area_filtro = '';

        $area_upper = mb_strtoupper($area_nombre); // Convertir a mayúsculas para comparación

        if (stripos($area_upper, 'ADMINISTRACI') !== false) $area_filtro = 'administracion';

        elseif (stripos($area_upper, 'IDIOME') !== false) $area_filtro = 'idiomas';

        elseif (stripos($area_upper, 'INFORM') !== false) $area_filtro = 'informatica';

        elseif (stripos($area_upper, 'INTERPERSONAL') !== false) $area_filtro = 'interpersonales';

        elseif (stripos($area_upper, 'RQUET') !== false) $area_filtro = 'marketing'; // MÀRQUETING

        elseif (stripos($area_upper, 'COMER') !== false) $area_filtro = 'comercio'; // COMERÇ

        elseif (stripos($area_upper, 'EDUCACI') !== false) $area_filtro = 'educacion';

      ?>

      <article class="course-tile course-tile--clickable" 

        data-mes="<?= $mes_inicio ?>" 

        data-modalidad="<?= $modalidad_limpia ?>"

        data-horario="<?= $franja_horaria ?>"

        data-profesor="<?= htmlspecialchars($profesor_nombre) ?>"

        data-area="<?= $area_filtro ?>"

        data-href="/cursos/<?= $curso['slug'] ?>">

        <a class="course-tile__img" href="/cursos/<?= $curso['slug'] ?>">

          <img src="<?= curso_img($curso) ?>" alt="<?= htmlspecialchars($nombre_mostrar) ?>" width="400" height="300" loading="lazy" decoding="async">

        </a>

        <div class="course-tile__body">

          <h3 class="course-tile__title"><?= htmlspecialchars($nombre_mostrar) ?></h3>

          

          <div class="course-tile__info">

            <div class="course-tile__row"><span class="course-tile__label">Modalidad:</span><?= ucfirst($curso['modalitat'] ?? 'Presencial') == 'Blended' ? 'Mixta' : ucfirst($curso['modalitat'] ?? 'Presencial') ?></div>

            <div class="course-tile__row"><span class="course-tile__label">Duración:</span><?= $horas ?>h</div>

            <div class="course-tile__row"><span class="course-tile__label">Inicio:</span><?= !empty($curso['data_inici']) ? date_from_bdd($curso['data_inici']) : 'A consultar' ?></div>

            <div class="course-tile__row"><span class="course-tile__label">Finalización:</span><?= !empty($curso['data_final']) ? date_from_bdd($curso['data_final']) : 'A consultar' ?></div>

          </div>

        </div>

      </article>

      <?php endforeach; ?>

    </div>

  </div>

</section>



</main>



<!-- =============================

 FORMULARIO Y TESTIMONIOS

============================= -->



<section id="reservar">

  <div class="seccion-final">

    

    <!-- FORMULARIO RESERVAR PLAZA -->

    <div class="box-final">

      <h2 class="box-final__title">Reservar plaza</h2>

      

      <form class="form-reserva" method="POST" action="https://thecorner.es/gracias-cursos/<?= isset($_GET['origin']) && $_GET['origin'] !== '' ? '?origin=' . htmlspecialchars($_GET['origin']) : '?origin=8' ?>">

        <input type="hidden" name="origin" id="origin" value="<?php 

          // Capturar origin directamente desde PHP, por defecto 8

          echo isset($_GET['origin']) && $_GET['origin'] !== '' ? htmlspecialchars($_GET['origin']) : '8'; 

        ?>">

        

        <input type="text" name="your-name" placeholder="Nombre *" class="form-reserva__input" required>

        

        <input type="text" name="your-surname" placeholder="Apellidos*" class="form-reserva__input" required>

        

        <input type="email" name="your-email" placeholder="E-mail *" class="form-reserva__input" required>

        

        <input type="tel" name="telefono" placeholder="Teléfono *" class="form-reserva__input" required>

        

        <input type="text" name="dni" placeholder="DNI (opcional)" class="form-reserva__input">

        

        <label class="form-reserva__label">Situación laboral: *</label>

        <select name="menu-702" class="form-reserva__select" required>

          <option value="">Selecciona...</option>

          <option value="Desempleado/a">Desempleado/a</option>

          <option value="Trabajador/a o Autónomo/a">Trabajador/a o Autónomo/a</option>

        </select>

        

        <!-- <textarea name="your-message" placeholder="Mensaje (opcional)" class="form-reserva__input" rows="3" style="resize: vertical; min-height: 60px;"></textarea> -->

        

        <div class="form-reserva__checkbox">

          <input type="checkbox" id="acceptance-729" name="acceptance-729" value="1" required>

          <label for="acceptance-729">

            IDIOMES, S.L. como responsable del tratamiento tratará tus datos con la finalidad de dar respuesta a tu consulta o petición. 

            Puedes acceder, rectificar y suprimir tus datos, así como ejercer otros derechos consultando la información adicional y detallada sobre protección de datos en nuestra 

            <a href="https://thecorner.es/politica-de-privacitat/" target="_blank">Política de Privacidad</a>. 

            He leído y acepto las condiciones contenidas en la política de privacidad sobre el tratamiento de mis datos para gestionar mi consulta o petición

          </label>

        </div>

        

        <div class="form-reserva__checkbox">

          <input type="checkbox" id="checkbox-243" name="checkbox-243" value="1">

          <label for="checkbox-243">

            Nos gustaría que nos prestaras tu consentimiento para: Enviarte información comercial sobre los productos, servicios, novedades de IDIOMES, S.L.

          </label>

        </div>

        

        <button type="submit" class="form-reserva__submit">INSCRÍBETE</button>

      </form>

    </div>

    

    <!-- TESTIMONIOS -->

    <div class="box-final">

      <h2 class="box-final__title">¿Qué dicen de nosotros?</h2>

      

      <div class="testimonios">

        <?php if (!empty($google_reviews_data['reviews'])): ?>

          <div class="testimonio" id="testimonio-container">

            <?php 

            $first_review = $google_reviews_data['reviews'][0];

            $author_name = $first_review['author_name'] ?? 'Usuario';

            $rating = $first_review['rating'] ?? 5;

            $text = $first_review['text'] ?? '';

            $time = $first_review['time'] ?? time();

            $fecha_formateada = date('d F Y', $time);

            $inicial = mb_substr($author_name, 0, 1);

            ?>

            

            <div class="testimonio__header">

              <div class="testimonio__avatar">

                <span class="testimonio__avatar-letter"><?= htmlspecialchars($inicial) ?></span>

                <span class="testimonio__avatar-badge">

                  <svg width="16" height="16" viewBox="0 0 16 16" fill="none">

                    <path d="M8 0L9.8 5.6H16L11 9L12.8 14.4L8 11L3.2 14.4L5 9L0 5.6H6.2L8 0Z" fill="#4285F4"/>

                  </svg>

                </span>

              </div>

              <div class="testimonio__info">

                <h3 class="testimonio__name"><?= htmlspecialchars($author_name) ?></h3>

                <p class="testimonio__date"><?= htmlspecialchars($fecha_formateada) ?></p>

              </div>

            </div>

            

            <div class="testimonio__stars">

              <?php for ($i = 0; $i < 5; $i++): ?>

                <span class="testimonio__star"><?= $i < $rating ? '★' : '☆' ?></span>

              <?php endfor; ?>

              <span class="testimonio__verified">✔</span>

            </div>

            

            <p class="testimonio__text"><?= htmlspecialchars($text) ?></p>

            <span class="testimonio__more">Leer más</span>

          </div>

        <?php else: ?>

          <p style="text-align:center;color:#6b7280;">No hay reseñas disponibles en este momento.</p>

        <?php endif; ?>

        

        <?php if (count($google_reviews_data['reviews']) > 1): ?>

        <div class="testimonio__navigation">

          <button class="testimonio__nav-btn" onclick="prevTestimonio()">‹</button>

          <button class="testimonio__nav-btn" onclick="nextTestimonio()">›</button>

        </div>

        <?php endif; ?>

        

        <div class="testimonio__footer">

          <p class="testimonio__rating">

            La evaluación general en <strong>Google</strong> es <strong><?= number_format($google_reviews_data['rating'], 1) ?></strong> de 5, en base a <strong><?= $google_reviews_data['user_ratings_total'] ?> reseñas</strong>

          </p>

          <span class="testimonio__badge">Verificado por: Google Reviews ⓘ</span>

        </div>

      </div>

    </div>

    

  </div>

</section>



<footer class="site-footer">

  <div class="footer-container">

    <div class="footer-subsidy-logos" aria-label="Entidades colaboradoras y financiadoras">
      <img src="/cursos/assets/img/logos-subvencionada-2026.jpg" alt="Logotipos de SOC, Generalitat de Catalunya, Consorci per a la Formacio Continua de Catalunya, Ministerio de Educacion, SEPE e idiomasSL." width="1843" height="253" loading="lazy" decoding="async">
    </div>

    <p class="footer-text">© <?= date('Y') ?> Centro de Estudios The Corner | Powered by Centro de Estudios The Corner</p>

    

    <div class="footer-links">

      <a href="https://thecorner.es/aviso-legal/">Aviso Legal</a>

      <span class="footer-separator">|</span>

      <a href="https://thecorner.es/politica-de-cookies/">Política de cookies</a>

      <span class="footer-separator">|</span>

      <a href="https://thecorner.es/politica-de-privacidad/">Política de privacidad</a>

      <span class="footer-separator">|</span>

      <a href="https://thecorner.es/privacidad-en-redes-sociales/">Privacidad en redes sociales</a>

      <span class="footer-separator">|</span>

      <a href="https://thecorner.es/canal-de-denuncias/">Canal de denuncias</a>

      <span class="footer-separator">|</span>

      <a href="/cursos/memoria-resultats-2025.html">Memòria de Resultats 2025</a>

    </div>

    

    <div class="footer-social">

      <a href="https://www.facebook.com/thecornerformacion" target="_blank" rel="noopener" aria-label="Facebook">

        <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>

      </a>

      <a href="https://www.youtube.com/channel/UCxxxxxx" target="_blank" rel="noopener" aria-label="YouTube">

        <svg viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>

      </a>

      <a href="https://www.instagram.com/thecornerformacion" target="_blank" rel="noopener" aria-label="Instagram">

        <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>

      </a>

      <a href="https://twitter.com/thecorner_es" target="_blank" rel="noopener" aria-label="Twitter">

        <svg viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>

      </a>

      <a href="https://www.linkedin.com/school/thecorner" target="_blank" rel="noopener" aria-label="LinkedIn">

        <svg viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>

      </a>

    </div>

  </div>

</footer>



<script>

<?php include __DIR__ . '/assets/js/cursos-gratuitos.inline.php'; ?>

</script>



</body>

</html>









