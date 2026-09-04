<?php

require_once __DIR__ . '/inc/common.php';
require_once __DIR__ . '/inc/cache.php';



// Headers para evitar cache y asegurar datos frescos

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Cache-Control: post-check=0, pre-check=0", false);

header("Pragma: no-cache");



/*************************************************

 * ENTRADA URL

 *************************************************/



// Slug viene del rewrite

$slug = mysqli_real_escape_string($db ?? conectar_bbdd(), $_GET['slug'] ?? '');



if(empty($slug)){

    header("HTTP/1.0 404 Not Found");

    exit;

}



/*************************************************

 * CONEXIÓN

 *************************************************/



if(!isset($db)){

    $db = conectar_bbdd();

}



/*************************************************

 * QUERY PRINCIPAL

 *************************************************/



$query = "

SELECT 

    c.id,

    c.nom_comercial,

    TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,

    c.data_inici,

    c.data_fi,

    c.tipus_subvencionada,

    c.especialitat_formativa,

    c.familia_formativa,

    c.mostrar_web,

    (SELECT g2.data_inici FROM gen_grups g2 

     WHERE g2.curs = c.id 

     AND g2.mostrar_web = 1

     ORDER BY g2.data_inici ASC LIMIT 1) as grup_data_inici,

    (SELECT COUNT(*) FROM gen_grups g3 

     WHERE g3.curs = c.id) as total_grups_existents,

    (SELECT COUNT(*) FROM gen_grups g4 

     WHERE g4.curs = c.id 

     AND g4.mostrar_web = 1) as total_grups_activos

FROM gen_cursos c

WHERE EXISTS (

    SELECT 1 FROM gen_grups g

    WHERE g.curs = c.id

)

ORDER BY c.id

";



$res = cache()->sql($query, function() use ($db, $query) {

    return mysqli_query($db, $query);

});

$curso = null;

$cursos_coincidentes = [];



// Detectar si el slug tiene sufijo de mes o "curs-complet"

$slug_base = $slug;

$mes_suffix = null;

$es_curso_completo = false;



// Primero detectar si termina en "-curs-complet"

if (preg_match('/^(.+)-curs-complet$/', $slug, $matches)) {

    $slug_base = $matches[1];

    $es_curso_completo = true;

}

// Si no, detectar sufijo de mes en catalán (ej: gestion-emociones-juny)

else {

    $meses_ca = ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

    $patron_meses = implode('|', $meses_ca);

    

    if (preg_match('/^(.+)-(' . $patron_meses . ')$/', $slug, $matches)) {

        $slug_base = $matches[1];

        $mes_suffix = $matches[2];

    }

}



// Buscar cursos que coincidan con el slug generado

while ($row = fetch_result($res)) {

    $nombre_curso = trim((string) (!empty($row['nom_curs']) ? $row['nom_curs'] : ($row['nom_comercial'] ?? '')));

    $slug_generado = slugify($nombre_curso);

    

    // Buscar por slug generado (exacto o base si tiene mes)

    if ($slug_generado === $slug || $slug_generado === $slug_base) {

        $cursos_coincidentes[] = $row;

    }

}



// Si no se encontró por slug de curso, buscar por slug de GRUPOS (CONSORCI y FOAP)

$grupo_foap_encontrado = null;

if (count($cursos_coincidentes) === 0) {

    // Primero buscar por grupos CONSORCI con nom_comercial o nom

    $q_grupos_consorci = "SELECT g.id as grupo_id, g.curs, g.nom_soc, g.mostrar_web as grupo_mostrar_web,

                      g.nom_comercial as grupo_nom_comercial, g.nom as grupo_nom,

                      c.id as curso_id, c.nom_comercial, c.tipus_subvencionada, c.mostrar_web as curso_mostrar_web,

                      '' as nombre_modulo

                      FROM gen_grups g

                      JOIN gen_cursos c ON c.id = g.curs

                      WHERE c.tipus_subvencionada = 'CONSORCI'

                      AND g.mostrar_web = 1";

    $res_grupos_consorci = cache()->sql($q_grupos_consorci, function() use ($db, $q_grupos_consorci) {

        return mysqli_query($db, $q_grupos_consorci);

    });

    

    while ($grupo_row = fetch_result($res_grupos_consorci)) {

        // Aplicar misma lógica de prioridad que cursos-gratuitos.php

        $nombre_grupo = '';

        if (!empty($grupo_row['grupo_nom_comercial']) && trim($grupo_row['grupo_nom_comercial']) != '') {

            $nombre_grupo = trim($grupo_row['grupo_nom_comercial']);

        } else if (!empty($grupo_row['grupo_nom']) && trim($grupo_row['grupo_nom']) != '') {

            $nombre_grupo = trim($grupo_row['grupo_nom']);

        }

        

        if (!empty($nombre_grupo)) {

            $slug_grupo = slugify($nombre_grupo);

            

            if ($slug_grupo === $slug || $slug_grupo === $slug_base) {

                $grupo_foap_encontrado = $grupo_row;

                // Cargar el curso asociado

                data_seek_result($res, 0);

                while ($row = fetch_result($res)) {

                    if ($row['id'] == $grupo_row['curso_id']) {

                        $cursos_coincidentes[] = $row;

                        break;

                    }

                }

                break;

            }

        }

    }

    

    // Si no se encontró en CONSORCI, buscar por grupos FOAP/módulos

    if (count($cursos_coincidentes) === 0) {

        $q_grupos_foap = "SELECT g.id as grupo_id, g.curs, g.nom_soc, g.mostrar_web as grupo_mostrar_web,

                          '' as grupo_nom_comercial, '' as grupo_nom,

                          c.id as curso_id, c.nom_comercial, c.tipus_subvencionada, c.mostrar_web as curso_mostrar_web,

                          m.modul as nombre_modulo

                          FROM gen_grups g

                          JOIN gen_cursos c ON c.id = g.curs

                          LEFT JOIN soc_moduls m ON m.id = g.nom_soc

                          WHERE c.tipus_subvencionada = 'FOAP'

                          AND (c.mostrar_web = 1 OR g.mostrar_web = 1)";

        $res_grupos_foap = cache()->sql($q_grupos_foap, function() use ($db, $q_grupos_foap) {

            return mysqli_query($db, $q_grupos_foap);

        });

        

        while ($grupo_row = fetch_result($res_grupos_foap)) {

            // Intentar generar slug desde el nombre del módulo

            $nombre_modulo = $grupo_row['nombre_modulo'] ?: $grupo_row['nom_comercial'] ?: '';

            if (!empty($nombre_modulo)) {

                // Limpiar códigos FOAP del nombre

                $nombre_limpio = preg_replace('/\d+\/FOAP\/\d+\/\d+\/\d+/', '', $nombre_modulo);

                $nombre_limpio = trim($nombre_limpio);

                $slug_modulo = slugify($nombre_limpio);

                

                if ($slug_modulo === $slug || $slug_modulo === $slug_base) {

                    $grupo_foap_encontrado = $grupo_row;

                    // Cargar el curso asociado

                    data_seek_result($res, 0);

                    while ($row = fetch_result($res)) {

                        if ($row['id'] == $grupo_row['curso_id']) {

                            $cursos_coincidentes[] = $row;

                            break;

                        }

                    }

                    break;

                }

            }

        }

    }

}



// Si hay múltiples coincidencias, elegir el mejor

if (count($cursos_coincidentes) > 0) {

    if (count($cursos_coincidentes) === 1) {

        $curso = $cursos_coincidentes[0];

    } else {

        // Múltiples cursos con mismo slug

        // Si la URL tiene sufijo de mes, buscar el curso que tenga grupos en ese mes

        if ($mes_suffix) {

          $meses_ca_num = [

            'gener' => 1, 'febrer' => 2, 'març' => 3, 'abril' => 4,

            'maig' => 5, 'juny' => 6, 'juliol' => 7, 'agost' => 8,

            'setembre' => 9, 'octubre' => 10, 'novembre' => 11, 'desembre' => 12

          ];

          $mes_num = $meses_ca_num[$mes_suffix];

          foreach ($cursos_coincidentes as $c) {

            $q_check_mes = "SELECT COUNT(*) as tiene_grupo_mes FROM gen_grups 

                    WHERE curs = '".mysqli_real_escape_string($db, $c['id'])."' 

                    AND MONTH(data_inici) = $mes_num

                    AND mostrar_web = 1";

            $res_check = cache()->sql($q_check_mes, function() use ($db, $q_check_mes) {

                return mysqli_query($db, $q_check_mes);

            });

            $check = fetch_result($res_check);

            if ($check['tiene_grupo_mes'] > 0) {

              $curso = $c;

              break;

            }

          }

          // Si no se encontró curso con ese mes, usar el primero

          if (!$curso) {

            $curso = $cursos_coincidentes[0];

          }

        } else {

            // Sin sufijo de mes: Priorizar: 1) Con grupos activos, 2) Con fecha más próxima, 3) ID más reciente

            usort($cursos_coincidentes, function($a, $b) {

                // Primero: Priorizar cursos con grupos activos

                if ($a['total_grups_activos'] != $b['total_grups_activos']) {

                    return $b['total_grups_activos'] - $a['total_grups_activos'];

                }

                // Segundo: Priorizar cursos con fecha de grupo más próxima

                if ($a['grup_data_inici'] && $b['grup_data_inici']) {

                    return strcmp($a['grup_data_inici'], $b['grup_data_inici']);

                }

                if ($a['grup_data_inici']) return -1;

                if ($b['grup_data_inici']) return 1;

                // Tercero: Priorizar ID más reciente (curso más nuevo)

                return $b['id'] - $a['id'];

            });

            $curso = $cursos_coincidentes[0];

        }

    }

}



// Verificar que el curso existe y tiene grupos (aunque estén despublicados)

if(!$curso || $curso['total_grups_existents'] == 0){

    header("HTTP/1.0 404 Not Found");

    exit;

}



header("HTTP/1.1 200 OK");



// Obtener nombre y slug

$nombre = trim((string) (!empty($curso['nom_curs']) ? $curso['nom_curs'] : ($curso['nom_comercial'] ?? '')));

// Generar slug desde nombre

$slug_curso = slugify($nombre);

$url_real = SITE_URL.'/cursos/'.$slug_curso;



// Verificar si el curso tiene grupos con mostrar_web = 1

$q_check_activo = "SELECT COUNT(*) as tiene_grupos_activos FROM gen_grups 

                   WHERE curs = '".mysqli_real_escape_string($db, $curso['id'])."' 

                   AND mostrar_web = 1";

$result_check = cache()->sql($q_check_activo, function() use ($db, $q_check_activo) {

    return mysqli_query($db, $q_check_activo);

});

$check = fetch_result($result_check);



// Un curso FOAP con mostrar_web = 1 se considera activo aunque no tenga grupos publicados

$es_foap_activo = ($curso['tipus_subvencionada'] == 'FOAP' && $curso['mostrar_web'] == 1);

$curso_inactivo = ($check['tiene_grupos_activos'] == 0 && !$es_foap_activo);



// Obtener el grupo activo del curso

$primer_grup = null;



// Si se encontró un grupo específico por slug (CONSORCI o FOAP), usar ese grupo

if ($grupo_foap_encontrado) {

  $q_primer_grup = "SELECT id, nom_soc, accio_formativa, data_inici, data_final, professor, modalitat, area_interes_soc FROM gen_grups 

            WHERE id = '".mysqli_real_escape_string($db, $grupo_foap_encontrado['grupo_id'])."' 

            LIMIT 1";

  $result_grup = cache()->sql($q_primer_grup, function() use ($db, $q_primer_grup) {

      return mysqli_query($db, $q_primer_grup);

  });

  $primer_grup = fetch_result($result_grup);

  

  // Actualizar el nombre y slug según el tipo de grupo encontrado

  if ($primer_grup) {

    // Para grupos CONSORCI: usar nom_comercial o nom del grupo

    if (!empty($grupo_foap_encontrado['grupo_nom_comercial']) && trim($grupo_foap_encontrado['grupo_nom_comercial']) != '') {

      $nombre = trim($grupo_foap_encontrado['grupo_nom_comercial']);

      $slug_curso = slugify($nombre);

      $url_real = SITE_URL.'/cursos/'.$slug_curso;

    } else if (!empty($grupo_foap_encontrado['grupo_nom']) && trim($grupo_foap_encontrado['grupo_nom']) != '') {

      $nombre = trim($grupo_foap_encontrado['grupo_nom']);

      $slug_curso = slugify($nombre);

      $url_real = SITE_URL.'/cursos/'.$slug_curso;

    }

    // Para módulos FOAP: usar el nombre del módulo

    else if (!empty($grupo_foap_encontrado['nombre_modulo'])) {

      $nombre_limpio = preg_replace('/\d+\/FOAP\/\d+\/\d+\/\d+/', '', $grupo_foap_encontrado['nombre_modulo']);

      $nombre_limpio = trim($nombre_limpio);

      $nombre = $nombre_limpio;

      $slug_curso = slugify($nombre_limpio);

      $url_real = SITE_URL.'/cursos/'.$slug_curso;

    }

    

    // Usar fechas del grupo encontrado

    $curso['data_inici'] = $primer_grup['data_inici'];

    $curso['data_fi'] = $primer_grup['data_final'];

  }

} 

// Si es un curso completo (sufijo -curs-complet), mantener fechas del curso padre

elseif ($es_curso_completo) {

  // Las fechas ya están en $curso['data_inici'] y $curso['data_fi'] desde la query principal

  // Cargar datos del primer grupo para formulario, modalidad y horarios

  

  // Obtener el primer grupo del curso con todos los campos necesarios para el formulario

  $q_primer_grupo_completo = "SELECT id, nom_soc, accio_formativa, data_inici, data_final, professor, modalitat, area_interes_soc

                              FROM gen_grups

                              WHERE curs = '".mysqli_real_escape_string($db, $curso['id'])."'

                              ORDER BY data_inici ASC

                              LIMIT 1";

  $result_primer_grupo = cache()->sql($q_primer_grupo_completo, function() use ($db, $q_primer_grupo_completo) {

      return mysqli_query($db, $q_primer_grupo_completo);

  });

  $primer_grupo_data = fetch_result($result_primer_grupo);

  

  // Asignar a $primer_grup para que los campos del formulario se poblen

  $primer_grup = $primer_grupo_data;

  

  

  if ($primer_grup) {

    $curso['modalitat'] = $primer_grup['modalitat'];

    

    // Obtener horarios del primer grupo

    $q_horaris = "SELECT dies, hora_inici, hora_final FROM gen_grups_horaris 

                  WHERE grup = '".mysqli_real_escape_string($db, $primer_grup['id'])."'

                  LIMIT 1";

    $result_horaris = cache()->sql($q_horaris, function() use ($db, $q_horaris) {

        return mysqli_query($db, $q_horaris);

    });

    $horari_data = fetch_result($result_horaris);

    

    $curso['dies'] = $horari_data['dies'] ?? '';

    $curso['hora_inici'] = $horari_data['hora_inici'] ?? '';

    $curso['hora_final'] = $horari_data['hora_final'] ?? '';

  } else {

    // Si no hay grupos, valores por defecto

    $curso['dies'] = '';

    $curso['hora_inici'] = '';

    $curso['hora_final'] = '';

    $curso['modalitat'] = 'Presencial';

  }

} 

// Si no es un grupo FOAP específico, cargar el primer grupo como siempre

elseif (!$curso_inactivo) {

  // Para FOAP: no filtrar por mostrar_web del grupo (si el curso es visible, todos sus grupos lo son)

  // Para CONSORCI: sí filtrar por mostrar_web = 1

  $where_grup_mostrar_web = ($curso['tipus_subvencionada'] == 'FOAP') ? '' : 'AND mostrar_web = 1';

  

  // Si la URL tiene sufijo de mes, buscar el grupo de ese mes (para CONSORCI y FOAP)

  if ($mes_suffix) {

    $meses_ca_num = [

      'gener' => 1, 'febrer' => 2, 'març' => 3, 'abril' => 4,

      'maig' => 5, 'juny' => 6, 'juliol' => 7, 'agost' => 8,

      'setembre' => 9, 'octubre' => 10, 'novembre' => 11, 'desembre' => 12

    ];

    $mes_num = $meses_ca_num[$mes_suffix];

    $q_primer_grup = "SELECT id, nom_soc, accio_formativa, data_inici, data_final, professor, modalitat, area_interes_soc FROM gen_grups 

              WHERE curs = '".mysqli_real_escape_string($db, $curso['id'])."' 

              $where_grup_mostrar_web

              AND MONTH(data_inici) = $mes_num

              ORDER BY data_inici ASC, id ASC

              LIMIT 1";

  } else {

    // Sin sufijo de mes: cargar el primer grupo

    $q_primer_grup = "SELECT id, nom_soc, accio_formativa, data_inici, data_final, professor, modalitat, area_interes_soc FROM gen_grups 

              WHERE curs = '".mysqli_real_escape_string($db, $curso['id'])."' 

              $where_grup_mostrar_web

              ORDER BY data_inici ASC, id ASC

              LIMIT 1";

  }

  $result_grup = cache()->sql($q_primer_grup, function() use ($db, $q_primer_grup) {

      return mysqli_query($db, $q_primer_grup);

  });

  $primer_grup = fetch_result($result_grup);

  if ($primer_grup) {

    $curso['data_inici'] = $primer_grup['data_inici'];

    $curso['data_fi'] = $primer_grup['data_final'];

  }

  // Si no hay grupo pero es FOAP activo, las fechas del curso se mantienen desde la query principal

  // Inicializar valores por defecto para campos que vienen del grupo

  if (!$primer_grup && $es_foap_activo) {

    $curso['dies'] = '';

    $curso['hora_inici'] = '';

    $curso['hora_final'] = '';

    $curso['modalitat'] = 'Presencial'; // Valor por defecto

  }

}



// Obtener datos del profesor del primer grupo

if ($primer_grup && $primer_grup['professor']) {

    $q_profe = "SELECT nom, perfil_docent, linkedin, imatge FROM gen_personal 

                WHERE id = '".mysqli_real_escape_string($db, $primer_grup['professor'])."'";

    $result_profe = cache()->sql($q_profe, function() use ($db, $q_profe) {

        return mysqli_query($db, $q_profe);

    });

    $profe_data = fetch_result($result_profe);

    

    $curso['nom_formador'] = $profe_data['nom'] ?? '';

    $curso['perfil_docent'] = $profe_data['perfil_docent'] ?? '';

    $curso['foto_formador'] = $profe_data['imatge'] ?? '';

    $curso['linkedin_formador'] = $profe_data['linkedin'] ?? '';

} else {

    $curso['nom_formador'] = '';

    $curso['perfil_docent'] = '';

    $curso['foto_formador'] = '';

    $curso['linkedin_formador'] = '';

}



// Guardar modalidad del primer grupo

$curso['modalitat'] = $primer_grup['modalitat'] ?? 'Presencial';



// Obtener horarios del primer grupo

if ($primer_grup) {

    $q_horaris = "SELECT dies, hora_inici, hora_final FROM gen_grups_horaris 

                  WHERE grup = '".mysqli_real_escape_string($db, $primer_grup['id'])."'

                  LIMIT 1";

    $result_horaris = cache()->sql($q_horaris, function() use ($db, $q_horaris) {

        return mysqli_query($db, $q_horaris);

    });

    $horari_data = fetch_result($result_horaris);

    

    $curso['dies'] = $horari_data['dies'] ?? '';

    $curso['hora_inici'] = $horari_data['hora_inici'] ?? '';

    $curso['hora_final'] = $horari_data['hora_final'] ?? '';

}



if (!function_exists('cargar_contenido_padre_foap')) {
    function cargar_contenido_padre_foap(mysqli $db, array $curso): array {
        $objectius = '';
        $continguts = '';

        $q_obj = "SELECT objectiu FROM soc_especialitats_formatives_objectius 
                  WHERE especialitat_formativa = '".mysqli_real_escape_string($db, $curso['especialitat_formativa'])."' 
                  ORDER BY numero ASC";
        $result_obj = cache()->sql($q_obj, function() use ($db, $q_obj) {
            return mysqli_query($db, $q_obj);
        });

        $objectius_list = [];
        while ($obj_data = fetch_result($result_obj)) {
            $objectius_list[] = $obj_data['objectiu'];
        }

        if (!empty($objectius_list)) {
            $objectius = '<ul>' . implode('', array_map(function($obj) {
                return '<li>' . $obj . '</li>';
            }, $objectius_list)) . '</ul>';
        }

        $q_cont = "SELECT g.id, g.nom_soc, m.modul 
                   FROM gen_grups g 
                   LEFT JOIN soc_moduls m ON m.id = g.nom_soc 
                   WHERE g.curs = '".mysqli_real_escape_string($db, $curso['id'])."' 
                   ORDER BY g.id ASC";
        $result_cont = cache()->sql($q_cont, function() use ($db, $q_cont) {
            return mysqli_query($db, $q_cont);
        });

        $continguts_list = [];
        while ($cont_data = fetch_result($result_cont)) {
            if (!empty($cont_data['modul'])) {
                $continguts_list[] = $cont_data['modul'];
            }
        }

        if (!empty($continguts_list)) {
            $continguts = implode("\n\n", array_map(function($modul, $idx) {
                return '<h3>Mòdul ' . ($idx + 1) . '</h3><p>' . $modul . '</p>';
            }, $continguts_list, array_keys($continguts_list)));
        }

        return [
            'objectius' => $objectius,
            'continguts' => $continguts,
        ];
    }
}

if (!function_exists('cargar_contenido_modulo_foap')) {
    function cargar_contenido_modulo_foap(mysqli $db, string $moduloId): array {
        $objectius = '';
        $continguts = '';
        $moduloIdEsc = mysqli_real_escape_string($db, $moduloId);

        $q_obj = "SELECT objectiu
                  FROM soc_moduls_objectius
                  WHERE modul = '".$moduloIdEsc."'
                    AND TRIM(COALESCE(objectiu, '')) != ''
                  ORDER BY numero ASC";
        $result_obj = cache()->sql($q_obj, function() use ($db, $q_obj) {
            return mysqli_query($db, $q_obj);
        });

        $objectius_list = [];
        while ($obj_data = fetch_result($result_obj)) {
            $objectius_list[] = $obj_data['objectiu'];
        }

        if (!empty($objectius_list)) {
            $objectius = '<ul>' . implode('', array_map(function($obj) {
                return '<li>' . $obj . '</li>';
            }, $objectius_list)) . '</ul>';
        }

        $q_cont = "SELECT titol, contingut
                   FROM soc_moduls_continguts
                   WHERE modul = '".$moduloIdEsc."'
                     AND (TRIM(COALESCE(titol, '')) != '' OR TRIM(COALESCE(contingut, '')) != '')
                   ORDER BY numero ASC";
        $result_cont = cache()->sql($q_cont, function() use ($db, $q_cont) {
            return mysqli_query($db, $q_cont);
        });

        $continguts_parts = [];
        while ($cont_data = fetch_result($result_cont)) {
            $continguts_parts[] = [
                'titol' => $cont_data['titol'],
                'contingut' => $cont_data['contingut'],
            ];
        }

        if (!empty($continguts_parts)) {
            $continguts = implode("\n\n", array_map(function($part) {
                $html = '';
                if (!empty($part['titol'])) {
                    $html .= '<h3>' . $part['titol'] . '</h3>';
                }
                if (!empty($part['contingut'])) {
                    $html .= '<div>' . $part['contingut'] . '</div>';
                }
                return $html;
            }, $continguts_parts));
        }

        return [
            'objectius' => $objectius,
            'continguts' => $continguts,
        ];
    }
}

// ===== CARGAR DATOS DETALLADOS =====

if ($curso['tipus_subvencionada'] == 'FOAP') {

    // Si es un grupo FOAP específico (módulo), obtener datos del módulo

    if ($grupo_foap_encontrado && !empty($primer_grup['nom_soc'])) {

        $contenido_modulo = cargar_contenido_modulo_foap($db, (string) $primer_grup['nom_soc']);

        // Obtener horas, objetivos, contenidos del módulo específico

        $q_mod = "SELECT hores, objectius, continguts, sortides_professionals

                  FROM soc_moduls

                  WHERE id = '".mysqli_real_escape_string($db, $primer_grup['nom_soc'])."'";

        $result_mod = cache()->sql($q_mod, function() use ($db, $q_mod) {

            return mysqli_query($db, $q_mod);

        });

        if ($result_mod && $mod_data = fetch_result($result_mod)) {

            $curso['hores'] = $mod_data['hores'];

            $curso['objectius'] = !empty($contenido_modulo['objectius'])
                ? $contenido_modulo['objectius']
                : (!empty($mod_data['objectius']) ? '<div>' . nl2br($mod_data['objectius']) . '</div>' : '');

            $curso['continguts'] = !empty($contenido_modulo['continguts'])
                ? $contenido_modulo['continguts']
                : (!empty($mod_data['continguts']) ? nl2br($mod_data['continguts']) : '');

            $curso['sortides_professionals'] = $mod_data['sortides_professionals'];

        }

        

        // Obtener la imagen de la especialitat formativa padre

        $q_ef_img = "SELECT imatge FROM soc_especialitats_formatives 

                     WHERE id = '".mysqli_real_escape_string($db, $curso['especialitat_formativa'])."'";

        $result_ef_img = cache()->sql($q_ef_img, function() use ($db, $q_ef_img) {

            return mysqli_query($db, $q_ef_img);

        });

        if ($result_ef_img && $ef_img_data = fetch_result($result_ef_img)) {

            $curso['imatge'] = $ef_img_data['imatge'];

        }

        if (empty($curso['objectius']) || empty($curso['continguts'])) {
            $contenido_padre = cargar_contenido_padre_foap($db, $curso);

            if (empty($curso['objectius']) && !empty($contenido_padre['objectius'])) {
                $curso['objectius'] = $contenido_padre['objectius'];
            }

            if (empty($curso['continguts']) && !empty($contenido_padre['continguts'])) {
                $curso['continguts'] = $contenido_padre['continguts'];
            }
        }

    } else {

        // Para FOAP curso general: obtener imagen, horas y sortides de soc_especialitats_formatives

        $q_ef = "SELECT imatge, sortides_professinals as sortides_professionals,

                 COALESCE(hores_cen, 0) + COALESCE(hores_practiques, 0) as hores 

                 FROM soc_especialitats_formatives 

                 WHERE id = '".mysqli_real_escape_string($db, $curso['especialitat_formativa'])."'";

        $result_ef = cache()->sql($q_ef, function() use ($db, $q_ef) {

            return mysqli_query($db, $q_ef);

        });

        if ($result_ef && $ef_data = fetch_result($result_ef)) {

            $curso['imatge'] = $ef_data['imatge'];

            $curso['hores'] = $ef_data['hores'];

            $curso['sortides_professionals'] = $ef_data['sortides_professionals'];

        }

        

        // Para FOAP CURSOS: obtener objectius de soc_especialitats_formatives_objectius

        $q_obj = "SELECT objectiu FROM soc_especialitats_formatives_objectius 

                  WHERE especialitat_formativa = '".mysqli_real_escape_string($db, $curso['especialitat_formativa'])."' 

                  ORDER BY numero ASC";

        $result_obj = cache()->sql($q_obj, function() use ($db, $q_obj) {

            return mysqli_query($db, $q_obj);

        });

        $objectius_list = [];

        while ($obj_data = fetch_result($result_obj)) {

            $objectius_list[] = $obj_data['objectiu'];

        }

        if (!empty($objectius_list)) {

            $curso['objectius'] = '<ul>' . implode('', array_map(function($obj) {

                return '<li>' . $obj . '</li>';

            }, $objectius_list)) . '</ul>';

        } else {

            $curso['objectius'] = '';

        }

        

        // Para FOAP CURSOS: obtener continguts listando los módulos (gen_grups + soc_moduls)

        $q_cont = "SELECT g.id, g.nom_soc, m.modul 

                   FROM gen_grups g 

                   LEFT JOIN soc_moduls m ON m.id = g.nom_soc 

                   WHERE g.curs = '".mysqli_real_escape_string($db, $curso['id'])."' 

                   ORDER BY g.id ASC";

        $result_cont = cache()->sql($q_cont, function() use ($db, $q_cont) {

            return mysqli_query($db, $q_cont);

        });

        $continguts_list = [];

        while ($cont_data = fetch_result($result_cont)) {

            if (!empty($cont_data['modul'])) {

                $continguts_list[] = $cont_data['modul'];

            }

        }

        if (!empty($continguts_list)) {

            $curso['continguts'] = implode("\n\n", array_map(function($modul, $idx) {

                return '<h3>Mòdul ' . ($idx + 1) . '</h3><p>' . $modul . '</p>';

            }, $continguts_list, array_keys($continguts_list)));

        } else {

            $curso['continguts'] = '';

        }

    }

    

} else {

    // Para CONSORCI/MIXTA: obtener imagen, horas y sortides de soc_accions_formatives

    // Si no hay primer_grup (curso inactivo), buscar cualquier grupo para obtener datos básicos

    $accio_formativa_id = null;

    if (!empty($primer_grup['accio_formativa'])) {

        $accio_formativa_id = $primer_grup['accio_formativa'];

    } else {

        // Buscar cualquier grupo (incluso inactivo) que tenga accio_formativa

        $q_any_grup = "SELECT accio_formativa FROM gen_grups 

                       WHERE curs = '".mysqli_real_escape_string($db, $curso['id'])."'

                       AND accio_formativa IS NOT NULL AND accio_formativa != ''

                       LIMIT 1";

        $result_any_grup = cache()->sql($q_any_grup, function() use ($db, $q_any_grup) {

            return mysqli_query($db, $q_any_grup);

        });

        if ($result_any_grup && $any_grup = fetch_result($result_any_grup)) {

            $accio_formativa_id = $any_grup['accio_formativa'];

        }

    }

    

    if ($accio_formativa_id) {

        $q_af = "SELECT imatge, sortides_professionals, hores

                 FROM soc_accions_formatives 

                 WHERE id = '".mysqli_real_escape_string($db, $accio_formativa_id)."'";

        $result_af = cache()->sql($q_af, function() use ($db, $q_af) {

            return mysqli_query($db, $q_af);

        });

        if ($result_af && $af_data = fetch_result($result_af)) {

            $curso['imatge'] = $af_data['imatge'];

            $curso['hores'] = $af_data['hores'];

            $curso['sortides_professionals'] = $af_data['sortides_professionals'];

        }

        

        // Cargar SIEMPRE objectius y continguts (importante para SEO)

        // Para CONSORCI/MIXTA: obtener objectius de soc_accions_formatives_objectius

        $q_obj = "SELECT objectiu FROM soc_accions_formatives_objectius 

                  WHERE accio_formativa = '".mysqli_real_escape_string($db, $accio_formativa_id)."' 

                  ORDER BY numero ASC";

        $result_obj = cache()->sql($q_obj, function() use ($db, $q_obj) {

            return mysqli_query($db, $q_obj);

        });

        $objectius_list = [];

        while ($obj_data = fetch_result($result_obj)) {

            $objectius_list[] = $obj_data['objectiu'];

        }

        if (!empty($objectius_list)) {

            $curso['objectius'] = '<ul>' . implode('', array_map(function($obj) {

                return '<li>' . $obj . '</li>';

            }, $objectius_list)) . '</ul>';

        } else {

            $curso['objectius'] = '';

        }

        

        // Para CONSORCI/MIXTA: obtener continguts de soc_accions_formatives_continguts

        $q_cont = "SELECT titol, contingut FROM soc_accions_formatives_continguts 

                   WHERE accio_formativa = '".mysqli_real_escape_string($db, $accio_formativa_id)."' 

                   ORDER BY numero ASC";

        $result_cont = cache()->sql($q_cont, function() use ($db, $q_cont) {

            return mysqli_query($db, $q_cont);

        });

        $continguts_parts = [];

        while ($cont_data = fetch_result($result_cont)) {

            if (!empty($cont_data['titol']) || !empty($cont_data['contingut'])) {

                $continguts_parts[] = [

                    'titol' => $cont_data['titol'],

                    'contingut' => $cont_data['contingut']

                ];

            }

        }

        if (!empty($continguts_parts)) {

            $curso['continguts'] = implode("\n\n", array_map(function($part) {

                $html = '';

                if (!empty($part['titol'])) {

                    $html .= '<h3>' . $part['titol'] . '</h3>';

                }

                if (!empty($part['contingut'])) {

                    $html .= '<div>' . $part['contingut'] . '</div>';

                }

                return $html;

            }, $continguts_parts));

        } else {

            $curso['continguts'] = '';

        }

    } else {

        // No se encontró ningún grupo con accio_formativa

        $curso['imatge'] = '';

        $curso['hores'] = '';

        $curso['objectius'] = '';

        $curso['continguts'] = '';

        $curso['sortides_professionals'] = '';

    }

}



// Datos derivados

$fecha_ini = $curso['data_inici']

    ? date('d/m/Y',strtotime($curso['data_inici']))

    : '';



$fecha_fin = $curso['data_fi']

    ? date('d/m/Y',strtotime($curso['data_fi']))

    : '';



$modalidad = ucfirst($curso['modalitat'] ?? 'Presencial');

if ($modalidad == 'Blended') {

    $modalidad = 'Mixta';

}



$imagen = $curso['imatge'];



if($imagen && !str_starts_with($imagen,'http')){

    $imagen = SITE_URL.'/'.ltrim($imagen,'/');

}



if(!$imagen){

    $imagen = SITE_URL.'/img/curso-placeholder.jpg';

}



$foto_formador = $curso['foto_formador'] ?? '';



if($foto_formador && !str_starts_with($foto_formador,'http')){

    $foto_formador = SITE_URL.'/'.ltrim($foto_formador,'/');

}



if(!$foto_formador){

    $foto_formador = SITE_URL.'/img/curso-placeholder.jpg';

}



$title = $nombre.' | Curso gratuito - The Corner';

$description = substr(strip_tags($curso['sortides_professionals'] ?? $nombre),0,155);



/*************************************************

 * DATOS COMUNES

 *************************************************/



// Obtener reviews de Google

$google_reviews_data = obtener_google_reviews('es');

$curso_shared_css_path = __DIR__ . '/assets/css/curso-shared.css';
$curso_shared_css_version = is_file($curso_shared_css_path) ? filemtime($curso_shared_css_path) : '1';



?>

<!doctype html>

<html lang="es">

<head>



<meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="icon" type="image/jpeg" sizes="32x32" href="https://thecorner.es/wp-content/uploads/2024/05/cropped-THE-CORNER-2022-Online-Perfiles-32x32.jpg">

<link rel="shortcut icon" href="https://thecorner.es/wp-content/uploads/2024/05/cropped-THE-CORNER-2022-Online-Perfiles-32x32.jpg">



<title><?= htmlspecialchars($title) ?></title>



<meta name="description" content="<?= htmlspecialchars($description) ?>">



<link rel="canonical" href="<?= $url_real ?>">

<link rel="alternate" hreflang="es" href="https://thecorner.es/cursos/<?= htmlspecialchars($slug_curso) ?>">

<link rel="alternate" hreflang="ca" href="https://thecorner.es/cursos/ca/<?= htmlspecialchars($slug_curso) ?>">

<link rel="alternate" hreflang="x-default" href="https://thecorner.es/cursos/<?= htmlspecialchars($slug_curso) ?>">



<!-- Open Graph -->

<meta property="og:title" content="<?= htmlspecialchars($title) ?>">

<meta property="og:description" content="<?= htmlspecialchars($description) ?>">

<meta property="og:image" content="<?= $imagen ?>">

<meta property="og:url" content="<?= $url_real ?>">

<meta property="og:type" content="website">

<meta property="og:locale" content="es_ES">

<meta property="og:locale:alternate" content="ca_ES">

<meta property="og:site_name" content="The Corner">



<!-- Twitter Card -->

<meta name="twitter:card" content="summary_large_image">

<meta name="twitter:title" content="<?= htmlspecialchars($title) ?>">

<meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">

<meta name="twitter:image" content="<?= $imagen ?>">



<!-- Structured Data - Course -->

<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Course',
    'name' => $nombre,
    'description' => $description,
    'provider' => [
        '@type' => 'EducationalOrganization',
        'name' => 'The Corner',
        'url' => 'https://thecorner.es',
    ],
    'image' => $imagen,
    'offers' => [
        '@type' => 'Offer',
        'category' => 'Gratis',
        'price' => '0',
        'priceCurrency' => 'EUR',
    ],
    'hasCourseInstance' => [
        '@type' => 'CourseInstance',
        'courseMode' => $modalidad,
        'startDate' => $curso['data_inici'] ?? null,
        'endDate' => $curso['data_fi'] ?? null,
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>



<!-- Preload de fuentes críticas (Roboto Regular y Bold) -->

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link rel="preload" as="font" type="font/woff2" href="https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu4mxK.woff2" crossorigin>

<link rel="preload" as="font" type="font/woff2" href="https://fonts.gstatic.com/s/roboto/v30/KFOlCnqEu92Fr1MmWUlfBBc4.woff2" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">



<!-- Preload imagen Hero curso (LCP) -->

<link rel="preload" as="image" href="<?= $imagen ?>" fetchpriority="high">



<!-- CSS crítico - carga bloqueante para evitar FOUC -->

<link rel="stylesheet" href="/cursos/assets/css/curso-shared.css?v=<?= $curso_shared_css_version ?>">

<link rel="stylesheet" href="/cursos/assets/css/cursos-gratuitos.css">

<!-- Cookiebot (noscript) -->
<script id="Cookiebot" src="https://consent.cookiebot.com/uc.js" 
data-cbid="81b5e0c8-3469-405a-bbbb-6a74eaa4fee0" 
data-blockingmode="auto" 
type="text/javascript"></script>
<!-- End Cookiebot (noscript) -->

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

      <div class="nav-menu__item nav-menu__item--dropdown lang-selector-menu" style="position:relative">

        ES

        <div class="nav-dropdown" style="min-width:180px">

          <a href="/cursos/cursos-gratuitos" class="nav-dropdown__item">← Listado cursos</a>

          <div style="height:1px;background:#f3f4f6;margin:8px 0"></div>

          <a href="/cursos/<?= htmlspecialchars($slug) ?>" class="nav-dropdown__item active-lang">Español</a>

          <a href="/cursos/ca/<?= htmlspecialchars($slug) ?>" class="nav-dropdown__item">Català</a>

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



<!-- =============================

 HERO CURSO

============================= -->



<section class="hero-curso" style="background-image:url('<?= $imagen ?>')">

  <div class="hero-curso__content">

    <h1 class="hero-curso__title"><?= htmlspecialchars($nombre) ?></h1>

    

    <?php if ($curso_inactivo): ?>

    <!-- Mensaje para cursos inactivos -->

    <a href="#reservar" class="hero-curso__info hero-curso__info--inactive" style="text-decoration: none; color: inherit; cursor: pointer;">

      <div class="hero-curso__item hero-curso__item--full">

        <span class="hero-curso__icon">📅</span>

        <span>

          <span class="hero-curso__label">Próxima convocatoria 2027</span>

          <br>

          <small>Pendiente de aprobación</small>

        </span>

      </div>

      

      <div class="hero-curso__item" style="margin-top: 8px;">

        <span class="hero-curso__icon"></span>

        <span><span class="hero-curso__label">Horas totales:</span> <?= $curso['hores'] ?? 'No especificada' ?> h</span>

      </div>

      

      <div class="hero-curso__item hero-curso__item--full" style="margin-top: 12px; margin-bottom: -8px;">

        <span class="btn-reservar btn-reservar--notify" style="width: 100%; text-align: center; display: block;">Avísame de la próxima convocatoria</span>

      </div>

    </a>

    <?php else: ?>

    <!-- Información normal del curso activo -->

    <div class="hero-curso__info">

      <?php if (!empty($curso['dies'])): ?>

      <div class="hero-curso__item">

        <span class="hero-curso__icon">📅</span>

        <?php 

        $dies_traducidos = traducir_dies($curso['dies'], 'es');

        // Si los días no contienen la modalidad (Presencial/Online), agregarla

        $mostrar_modalidad = !preg_match('/(Presencial|Online)/i', $dies_traducidos);

        ?>

        <span><span class="hero-curso__label">Días:</span> <?= $dies_traducidos ?><?= $mostrar_modalidad ? '. ' . $modalidad : '' ?></span>

      </div>

      <?php else: ?>

      <div class="hero-curso__item">

        <span class="hero-curso__icon">📅</span>

        <span><span class="hero-curso__label">Modalidad:</span> <?= $modalidad ?></span>

      </div>

      <?php endif; ?>

      

      <div class="hero-curso__item">

        <span class="hero-curso__icon">🕑</span>

        <span><span class="hero-curso__label">Inicio:</span> <?= $fecha_ini ?: 'A consultar' ?></span>

      </div>

      

      <div class="hero-curso__item">

        <span class="hero-curso__icon">🕑</span>

        <span><span class="hero-curso__label">Final:</span> <?= $fecha_fin ?: 'A consultar' ?></span>

      </div>

      

      <?php if(!empty($curso['hora_inici']) && !empty($curso['hora_final'])): ?>

      <div class="hero-curso__item">

        <span class="hero-curso__icon">🕑</span>

        <span><span class="hero-curso__label">Horario:</span> <?= substr($curso['hora_inici'], 0, 5) ?> - <?= substr($curso['hora_final'], 0, 5) ?></span>

      </div>

      <?php endif; ?>

      

      <div class="hero-curso__item">

        <span class="hero-curso__icon">👥</span>

        <span><span class="hero-curso__label">Horas totales:</span> <?= $curso['hores'] ?? 'No especificada' ?> h</span>

      </div>

    </div>

    <?php endif; ?>

    

    <?php if (!$curso_inactivo): ?>

    <div class="hero-curso__cta">

      <a href="#reservar" class="btn-reservar">Reservar plaza</a>

    </div>

    <?php endif; ?>

  </div>

</section>



<!-- =============================

 CARACTERÍSTICAS

============================= -->



<section class="caracteristicas">

  <div class="caracteristicas__grid">

    

    <div class="caracteristica">

      <svg class="caracteristica__icon" fill="none" viewBox="0 0 24 24">

        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>

        <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>

        <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke-linecap="round" stroke-linejoin="round"/>

        <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round"/>

      </svg>

      <div class="caracteristica__label">Modalidad</div>

      <div class="caracteristica__value"><?= $curso_inactivo ? 'No disponible' : strtolower($modalidad) ?></div>

    </div>

    

    <div class="caracteristica">

      <svg class="caracteristica__icon" fill="none" viewBox="0 0 24 24">

        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>

        <line x1="16" y1="2" x2="16" y2="6" stroke-linecap="round" stroke-linejoin="round"/>

        <line x1="8" y1="2" x2="8" y2="6" stroke-linecap="round" stroke-linejoin="round"/>

        <line x1="3" y1="10" x2="21" y2="10" stroke-linecap="round" stroke-linejoin="round"/>

        <circle cx="17" cy="17" r="3" stroke-linecap="round" stroke-linejoin="round"/>

        <path d="M16 17l1 1 2-2" stroke-linecap="round" stroke-linejoin="round"/>

      </svg>

      <div class="caracteristica__label">Duración</div>

      <div class="caracteristica__value"><?= $curso['hores'] ?? '0' ?></div>

    </div>

    

    <div class="caracteristica">

      <svg class="caracteristica__icon" fill="none" viewBox="0 0 24 24">

        <path d="M18 20V10M12 20V4M6 20v-6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>

        <path d="M20 8l-2-2-2 2M4 16l2 2 2-2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>

      </svg>

      <div class="caracteristica__label">Nivel</div>

      <div class="caracteristica__value">Certificado Oficial</div>

    </div>

    

    <div class="caracteristica">

      <svg class="caracteristica__icon" fill="none" viewBox="0 0 24 24">

        <line x1="3" y1="21" x2="21" y2="21" stroke-width="2" stroke-linecap="round"/>

        <rect x="5" y="15" width="3" height="6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>

        <rect x="10.5" y="11" width="3" height="10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>

        <rect x="16" y="6" width="3" height="15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>

      </svg>

      <div class="caracteristica__label">Precio</div>

      <div class="caracteristica__value">0 € subvencionado</div>

    </div>

    

  </div>

</section>



<!-- =============================

 NAVEGACIÓN STICKY

============================= -->



<nav class="nav-curso">

  <div class="nav-curso__inner">

    <div class="nav-curso__links">

      <a href="#que-aprendere" class="nav-curso__link">¿Qué aprenderé?</a>

      <a href="#contenidos" class="nav-curso__link">Contenidos</a>

      <a href="#a-quien-va-dirigido" class="nav-curso__link">¿A quién va dirigido?</a>

      <a href="#de-que-podre-trabajar" class="nav-curso__link">¿De qué podré trabajar?</a>

      <a href="#testimonios" class="nav-curso__link">Testimonios</a>

      <a href="#relacionados" class="nav-curso__link">Relacionados</a>

    </div>

    <a href="#reservar" class="nav-curso__btn">Reservar plaza</a>

  </div>

</nav>



<!-- =============================

 QUÉ APRENDERÉ / CON QUIÉN APRENDERÉ

============================= -->



<section id="que-aprendere" class="seccion-curso">

  <div class="seccion-curso__grid">

    

    <!-- QUÉ APRENDERÉ -->

    <div class="card-curso">

      <h2 class="card-curso__title">¿Qué aprenderé?</h2>

      <ul class="card-curso__list">

        <?php 

        // Procesar objectius de manera inteligente

        if (!empty($curso['objectius'])) {

          $objectius_text = $curso['objectius'];

          $objectius = [];

          

          // Si tiene tags <li>, extraerlos

          if (preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $objectius_text, $matches)) {

            $objectius = array_map('strip_tags', $matches[1]);

          }

          // Si tiene tags <p>, extraerlos

          else if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $objectius_text, $matches)) {

            $objectius = array_map('strip_tags', $matches[1]);

          }

          // Si tiene bullets • o - al inicio de línea

          else if (preg_match_all('/^[\s]*[•\-\*]\s*(.+)$/m', strip_tags($objectius_text), $matches)) {

            $objectius = $matches[1];

          }

          // Separar por saltos de línea dobles (párrafos)

          else {

            $text_limpio = strip_tags($objectius_text);

            // Primero intentar separar por párrafos (doble salto)

            $objectius = preg_split('/\n\s*\n/', $text_limpio);

            

            // Si solo hay un elemento, intentar separar por saltos simples

            if (count($objectius) <= 1) {

              $objectius = preg_split('/\r\n|\r|\n/', $text_limpio);

            }

          }

          

          // Limpiar y mostrar

          $count = 0;

          foreach ($objectius as $objectiu) {

            $objectiu = trim($objectiu);

            // Filtrar items vacíos o muy cortos (mínimo 20 caracteres para evitar basura)

            if (!empty($objectiu) && strlen($objectiu) > 20) {

              echo '<li class="card-curso__item">

                <span class="card-curso__check">

                  <svg fill="none" viewBox="0 0 24 24">

                    <polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/>

                  </svg>

                </span>

                <span>' . htmlspecialchars($objectiu) . '</span>

              </li>';

              $count++;

            }

          }

          

          // Si no se encontró nada válido, mostrar el texto completo

          if ($count === 0) {

            echo '<li class="card-curso__item">

              <span class="card-curso__check">

                <svg fill="none" viewBox="0 0 24 24">

                  <polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/>

                </svg>

              </span>

              <span>' . htmlspecialchars(strip_tags($objectius_text)) . '</span>

            </li>';

          }

        } else {

          echo '<li class="card-curso__item">

            <span class="card-curso__check">

              <svg fill="none" viewBox="0 0 24 24">

                <polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/>

              </svg>

            </span>

            <span>Objetivos del curso disponibles próximamente</span>

          </li>';

        }

        ?>

      </ul>

    </div>

    

    <!-- CON QUIÉN APRENDERÉ -->

    <div class="card-curso card-profesor" id="con-quien-aprendere">

      <h2 class="card-curso__title">¿Con quién aprenderé?</h2>

      <?php if (!empty($curso['nom_formador'])): ?>

      <div class="card-profesor__avatar">

        <img src="<?= $foto_formador ?>" alt="<?= htmlspecialchars($curso['nom_formador']) ?>" width="200" height="200">

      </div>

      <h3 class="card-profesor__nombre"><?= htmlspecialchars($curso['nom_formador']) ?></h3>

      <?php if (!empty($curso['perfil_docent'])): ?>

      <p class="card-profesor__descripcion"><?= htmlspecialchars($curso['perfil_docent']) ?></p>

      <?php endif; ?>

      <?php if (!empty($curso['linkedin_formador'])): ?>

      <a href="<?= htmlspecialchars($curso['linkedin_formador']) ?>" target="_blank" class="card-profesor__linkedin" title="LinkedIn">

        <svg viewBox="0 0 24 24">

          <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>

        </svg>

      </a>

      <?php endif; ?>

      <?php else: ?>

      <p class="card-profesor__no-info">Información del formador no disponible</p>

      <?php endif; ?>

    </div>

    

  </div>

</section>



<!-- =============================

 CONTENIDOS

============================= -->



<section id="contenidos" class="continguts">

  <div class="continguts__container">

    <h2 class="continguts__title">Contenidos del Curso</h2>

    

    <div class="continguts__content">

      <?php 

      // Procesar y mostrar continguts de forma estructurada

      if (!empty($curso['continguts'])) {

        $continguts_html = $curso['continguts'];

        $items = [];

        

        // Patrón 1: Detectar "Unitat X", "Unidad X", "Mòdul X", etc.

        if (preg_match_all('/(Unitat|Unidad|Mòdul|Módulo|Tema)\s*\d+[:\.]?\s*([^\n<]+)(.*?)(?=(?:Unitat|Unidad|Mòdul|Módulo|Tema)\s*\d+|$)/is', $continguts_html, $matches, PREG_SET_ORDER)) {

          foreach ($matches as $match) {

            $title = trim(strip_tags($match[1] . ' ' . $match[2]));

            $content = trim($match[3]);

            $content = strip_tags($content, '<p><br><ul><ol><li><strong><em><b><i>');

            

            if (!empty($content)) {

              $items[] = [

                'title' => $title,

                'content' => $content

              ];

            }

          }

        }

        // Patrón 2: Títulos en <strong> o negritas

        else if (preg_match_all('/<strong[^>]*>([^<]+)<\/strong>\s*(.*?)(?=<strong|$)/is', $continguts_html, $matches, PREG_SET_ORDER)) {

          foreach ($matches as $match) {

            $title = trim(strip_tags($match[1]));

            $content = trim($match[2]);

            $content = strip_tags($content, '<p><br><ul><ol><li><strong><em><b><i>');

            

            if (!empty($content) && strlen($content) > 20) {

              $items[] = [

                'title' => $title,

                'content' => $content

              ];

            }

          }

        }

        // Patrón 3: <h3> o <h4>

        else if (preg_match_all('/<h[34][^>]*>([^<]+)<\/h[34]>\s*(.*?)(?=<h[34]|$)/is', $continguts_html, $matches, PREG_SET_ORDER)) {

          foreach ($matches as $match) {

            $title = trim(strip_tags($match[1]));

            $content = trim($match[2]);

            $content = strip_tags($content, '<p><br><ul><ol><li><strong><em><b><i>');

            

            if (!empty($content) && strlen($content) > 20) {

              $items[] = [

                'title' => $title,

                'content' => $content

              ];

            }

          }

        }

        

        // Si se encontraron items estructurados, mostrarlos

        if (!empty($items)) {

          foreach ($items as $index => $item) {

            $isActive = $index === 0 ? 'active' : ''; // Primer item abierto por defecto

            echo '<div class="continguts__module ' . $isActive . '">

              <h3 class="continguts__module-title">' . htmlspecialchars($item['title']) . '</h3>

              <div class="continguts__module-content">' . $item['content'] . '</div>

            </div>';

          }

        } else {

          // Si no se pudo dividir, mostrar el contenido completo con formato

          $content_clean = strip_tags($continguts_html, '<p><br><ul><ol><li><strong><em><b><i><h3><h4>');

          echo '<div class="continguts__full">' . $content_clean . '</div>';

        }

      } else {

        echo '<div class="continguts__full" style="text-align:center;color:#9ca3af;">

          <p>Contenidos del curso disponibles próximamente</p>

        </div>';

      }

      ?>

    </div>

    

  </div>

</section>



<script>

// Funcionalidad acordeón para contenidos

document.addEventListener('DOMContentLoaded', function() {

  const modules = document.querySelectorAll('.continguts__module');

  

  modules.forEach(function(module) {

    const title = module.querySelector('.continguts__module-title');

    

    if (title) {

      title.addEventListener('click', function() {

        // Toggle clase active en el módulo clickeado

        module.classList.toggle('active');

        

        // Opcional: Cerrar otros módulos (quitar comentario si quieres solo uno abierto a la vez)

        /*

        modules.forEach(function(otherModule) {

          if (otherModule !== module) {

            otherModule.classList.remove('active');

          }

        });

        */

      });

    }

  });

});

</script>



<!-- =============================

 A QUIÉN VA DIRIGIDO

============================= -->



<section id="a-quien-va-dirigido" class="seccion-curso seccion-curso--gris">

  <div class="dirigit">

    <div class="dirigit__imagen">

      <img src="https://thecorner.es/wp-content/uploads/2024/05/Grupo-263.png" alt="Dirigido a" width="500" height="500" style="width:100%;height:auto;max-width:500px;">

    </div>

    

    <div class="dirigit__content">

      <h2 class="dirigit__title">¿A quién va dirigido?</h2>

      

      <div class="dirigit__lista">

        <?php if ($curso['tipus_subvencionada'] === 'FOAP'): ?>

        <div class="dirigit__item">

          <span class="dirigit__icon">👤</span>

          <span class="dirigit__text">Para personas desempleadas (colectivo prioritario)</span>

        </div>

        

        <div class="dirigit__item">

          <span class="dirigit__icon">👤</span>

          <span class="dirigit__text">Para personas trabajadoras</span>

        </div>

        

        <div class="dirigit__item">

          <span class="dirigit__icon">👤</span>

          <span class="dirigit__text">Para personas autónomas</span>

        </div>

        <?php else: ?>

        <div class="dirigit__item">

          <span class="dirigit__icon">👤</span>

          <span class="dirigit__text">Para personas desempleadas</span>

        </div>

        

        <div class="dirigit__item">

          <span class="dirigit__icon">👤</span>

          <span class="dirigit__text">Para personas trabajadoras (colectivo prioritario)</span>

        </div>

        

        <div class="dirigit__item">

          <span class="dirigit__icon">👤</span>

          <span class="dirigit__text">Para personas autónomas (colectivo prioritario)</span>

        </div>

        <?php endif; ?>

      </div>

      

      <a href="#reservar" class="dirigit__btn">Reservar plaza</a>

    </div>

  </div>

</section>



<!-- =============================

 DE QUÉ PODRÉ TRABAJAR

============================= -->



<section id="de-que-podre-trabajar" class="seccion-curso">

  <div class="treballar">

    <div class="treballar__card">

      <h2 class="treballar__title">¿De qué podré trabajar?</h2>

      

      <div class="treballar__content">

        <span class="treballar__icon">🎓</span>

        <div class="treballar__text">

          <?php 

          if (!empty($curso['sortides_professionals'])) {

            echo $curso['sortides_professionals'];

          } else {

            echo 'Podrás mejorar tus competencias profesionales con este curso.';

          }

          ?>

        </div>

      </div>

    </div>

  </div>

</section>



<?php

// Obtener cursos relacionados antes de mostrar la sección

$q_grupo_actual = "SELECT id, curs_relacionat_1, curs_relacionat_2, curs_relacionat_3 

                   FROM gen_grups 

                   WHERE curs = '" . mysqli_real_escape_string($db, $curso['id']) . "'

                   ORDER BY data_inici DESC, id DESC

                   LIMIT 1";

$result_grupo_actual = cache()->sql($q_grupo_actual, function() use ($db, $q_grupo_actual) {

    return mysqli_query($db, $q_grupo_actual);

});

$grupo_con_relacionados = fetch_result($result_grupo_actual);



// Array con los IDs de grupos relacionados

$grupos_relacionados_ids = [];

if ($grupo_con_relacionados) {

  $grupos_relacionados_ids = [

    $grupo_con_relacionados['curs_relacionat_1'] ?? null,

    $grupo_con_relacionados['curs_relacionat_2'] ?? null,

    $grupo_con_relacionados['curs_relacionat_3'] ?? null

  ];

}



$cursos_relacionados = [];



foreach ($grupos_relacionados_ids as $grupo_id) {

  if (empty($grupo_id)) continue;

  

  // Obtener el grupo relacionado

  $q_grupo = "SELECT c.*, g.etapa, g.especialitat_formativa, g.accio_formativa 

             FROM gen_grups g

             LEFT JOIN gen_cursos c ON c.id = g.curs

             WHERE g.id = '" . mysqli_real_escape_string($db, $grupo_id) . "'

             LIMIT 1";

  $result_grupo = cache()->sql($q_grupo, function() use ($db, $q_grupo) {

      return mysqli_query($db, $q_grupo);

  });

  

  if ($result_grupo && $grupo_data = fetch_result($result_grupo)) {

    $imagen_related = '';

    

    // Según la etapa, obtener la imagen

    if ($grupo_data['etapa'] == 25) { // FOAP

      $q_img = "SELECT imatge FROM soc_especialitats_formatives 

               WHERE id = '" . mysqli_real_escape_string($db, $grupo_data['especialitat_formativa']) . "'";

      $r_img = cache()->sql($q_img, function() use ($db, $q_img) {

          return mysqli_query($db, $q_img);

      });

      if ($r_img && $img_data = fetch_result($r_img)) {

        $imagen_related = $img_data['imatge'];

      }

    } else if ($grupo_data['etapa'] == 26 || $grupo_data['etapa'] == 31 || $grupo_data['etapa'] == 32) { 

      // CONSORCI o MIXTA

      $q_img = "SELECT imatge FROM soc_accions_formatives 

               WHERE id = '" . mysqli_real_escape_string($db, $grupo_data['accio_formativa']) . "'";

      $r_img = cache()->sql($q_img, function() use ($db, $q_img) {

          return mysqli_query($db, $q_img);

      });

      if ($r_img && $img_data = fetch_result($r_img)) {

        $imagen_related = $img_data['imatge'];

      }

      

      // Si es mixta y no hay imagen, intentar de especialitats_formatives

      if (empty($imagen_related) && ($grupo_data['etapa'] == 31 || $grupo_data['etapa'] == 32)) {

        $q_img = "SELECT imatge FROM soc_especialitats_formatives 

                 WHERE id = '" . mysqli_real_escape_string($db, $grupo_data['especialitat_formativa']) . "'";

        $r_img = cache()->sql($q_img, function() use ($db, $q_img) {

            return mysqli_query($db, $q_img);

        });

        if ($r_img && $img_data = fetch_result($r_img)) {

          $imagen_related = $img_data['imatge'];

        }

      }

    }

    

    // Imagen por defecto si no hay ninguna

    if (empty($imagen_related)) {

      $imagen_related = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect fill="%23b5d833" width="400" height="300"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="24" fill="%23fff"%3ECurs%3C/text%3E%3C/svg%3E';

    }

    

    // Usar nombre comercial del curso

    $nombre_related = !empty($grupo_data['nom_comercial']) ? trim($grupo_data['nom_comercial']) : '';

    

    // Extraer el nombre después del pipe si existe

    if (strpos($nombre_related, '|') !== false) {

      $nombre_related = trim(substr($nombre_related, strrpos($nombre_related, '|') + 1));

    }

    

    if (!empty($nombre_related)) {

      $cursos_relacionados[] = [

        'nombre' => $nombre_related,

        'imagen' => $imagen_related,

        'slug' => slugify($nombre_related)

      ];

    }

  }

}

?>



<?php if (!empty($cursos_relacionados)): ?>

<!-- =============================

 CURSOS RELACIONADOS

============================= -->



<section id="relacionados" class="seccion-curso seccion-curso--gris">

  <div class="relacionats">

    <h2 class="relacionats__title">Cursos relacionados</h2>

    

    <div class="relacionats__grid">

      <?php

      foreach ($cursos_relacionados as $related) {

        echo '<a href="/cursos/' . htmlspecialchars($related['slug']) . '" class="relacionats__card">

          <img src="' . htmlspecialchars($related['imagen']) . '" alt="' . htmlspecialchars($related['nombre']) . '" width="280" height="280" class="relacionats__image">

          <div class="relacionats__overlay">

            <h3 class="relacionats__name">' . htmlspecialchars($related['nombre']) . '</h3>

          </div>

        </a>';

      }

      ?>

    </div>

  </div>

</section>

<?php endif; ?>



<!-- =============================

 FORMULARIO Y TESTIMONIOS

============================= -->



<section id="reservar" class="seccion-curso">

  <div class="seccion-final">

    

    <!-- FORMULARIO RESERVAR PLAZA -->

    <div class="box-final">

      <h2 class="box-final__title"><?php echo $curso_inactivo ? '¡Te avisamos de la próxima edición!' : 'Reservar plaza'; ?></h2>

      

      <form class="form-reserva" method="POST" action="https://thecorner.es/gracias-cursos/?origin=<?= isset($_GET['origin']) && $_GET['origin'] !== '' ? htmlspecialchars($_GET['origin']) : '8' ?>">

        <input type="hidden" name="id_group" id="id_group" value="<?= htmlspecialchars($primer_grup['id'] ?? '') ?>">

        <input type="hidden" name="id_curs" id="id_curs" value="<?= htmlspecialchars($curso['id'] ?? '') ?>">

        <input type="hidden" name="curs_interes" value="<?= htmlspecialchars($nombre ?? '') ?>">

        <input type="hidden" name="grup_interes" value="<?= htmlspecialchars($nombre . (!empty($primer_grup['data_inici']) ? ' - ' . date('d/m/Y', strtotime($primer_grup['data_inici'])) : '')) ?>">

        <input type="hidden" name="area_interes_soc" value="<?= htmlspecialchars($primer_grup['area_interes_soc'] ?? '') ?>">

        <input type="hidden" name="origin" id="origin" value="<?php 

          // Capturar origin directamente desde PHP, por defecto 8

          echo isset($_GET['origin']) && $_GET['origin'] !== '' ? htmlspecialchars($_GET['origin']) : '8'; 

        ?>">

        

        <input type="text" name="your-name" placeholder="Nombre *" class="form-reserva__input" required>

        

        <input type="text" name="your-surname" placeholder="Apellidos *" class="form-reserva__input" required>

        

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

        

        <button type="submit" class="form-reserva__submit"><?php echo $curso_inactivo ? 'QUIERO QUE ME AVISEN' : 'INSCRÍBETE'; ?></button>

      </form>

    </div>

    

    <!-- TESTIMONIOS -->

    <div class="box-final" id="testimonios">

      <h2 class="box-final__title">¿Qué dicen de nosotros?</h2>

      

      <div class="testimonios">

        <?php if (!empty($google_reviews_data['reviews'])): ?>

          <div class="testimonio" id="testimonio-container">

            <?php 

            $first_review = $google_reviews_data['reviews'][0];

            $author_name = $first_review['author_name'] ?? 'Usuari';

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

            La valoración general en <strong>Google</strong> es <strong><?= number_format($google_reviews_data['rating'], 1) ?></strong> de 5, basada en <strong><?= $google_reviews_data['user_ratings_total'] ?> reseñas</strong>

          </p>

          <span class="testimonio__badge">Verificado por: Google Reviews ⓘ</span>

        </div>

      </div>

    </div>

    

  </div>

</section>





<!-- =============================

 FOOTER

============================= -->



<footer class="site-footer">

  <div class="footer-container">

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

<?php

$courseJsLocale = 'es';

include __DIR__ . '/assets/js/curso-shared.inline.php';

?>



// JavaScript básico si se necesita en el futuro

document.addEventListener('DOMContentLoaded', function() {

  // Aquí se puede agregar funcionalidad adicional si es necesaria

});

</script>



</body>

</html>

