// ===== TARJETAS CLICABLES CON DETECCIÓN TAP vs SCROLL =====
// Hace toda la tarjeta clicable pero evita abrir el curso durante scroll en móvil
(function() {
  const clickableCards = document.querySelectorAll('.course-tile--clickable');
  
  clickableCards.forEach(card => {
    let startX = 0;
    let startY = 0;
    let startTime = 0;
    let hasMoved = false;
    
    // Detectar inicio del toque
    card.addEventListener('touchstart', function(e) {
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
      startTime = Date.now();
      hasMoved = false;
    }, { passive: true });
    
    // Detectar movimiento durante el toque
    card.addEventListener('touchmove', function(e) {
      const moveX = e.touches[0].clientX;
      const moveY = e.touches[0].clientY;
      const diffX = Math.abs(moveX - startX);
      const diffY = Math.abs(moveY - startY);
      
      // Si se movió más de 10px en cualquier dirección, es un scroll
      if (diffX > 10 || diffY > 10) {
        hasMoved = true;
      }
    }, { passive: true });
    
    // Detectar fin del toque
    card.addEventListener('touchend', function(e) {
      const duration = Date.now() - startTime;
      
      // Si no se movió Y la duración fue corta (< 500ms), es un tap intencional
      if (!hasMoved && duration < 500) {
        const href = card.dataset.href;
        if (href) {
          // Prevenir el clic del enlace de la imagen (evitar doble navegación)
          e.preventDefault();
          window.location.href = href;
        }
      }
    }, { passive: false });
    
    // Para desktop, clic simple en cualquier parte excepto enlaces
    card.addEventListener('click', function(e) {
      // Solo en desktop (cuando no hay touchstart)
      if (!('ontouchstart' in window)) {
        // Si NO se hizo clic en un enlace, navegar
        if (e.target.tagName !== 'A' && !e.target.closest('a')) {
          const href = card.dataset.href;
          if (href) {
            window.location.href = href;
          }
        }
      }
    });
  });
})();

// Filtrado automático de cursos (solo por contenido visible en cards)
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('.filters');
  const selects = form.querySelectorAll('select');
  const cards = document.querySelectorAll('.course-tile');
  
  // Función para filtrar cards por contenido visible
  function filtrarCursos() {
    const mesSeleccionado = document.getElementById('filter-mes')?.value || '';
    const modalidadSeleccionada = document.getElementById('filter-modalidad')?.value || '';
    const horarioSeleccionado = document.getElementById('filter-franja')?.value || '';
    const profesorSeleccionado = document.getElementById('filter-profesor')?.value || '';
    const areaSeleccionada = document.getElementById('filter-area')?.value || '';
    
    let cursosVisibles = 0;
    
    cards.forEach(card => {
      const mesCurso = card.dataset.mes || '';
      const modalidadCurso = card.dataset.modalidad || '';
      const horarioCurso = card.dataset.horario || '';
      const profesorCurso = card.dataset.profesor || '';
      const areaCurso = card.dataset.area || '';
      
      let mostrar = true;
      
      // Filtrar por mes (solo por el mes de inicio visible en la card)
      if (mesSeleccionado && mesCurso !== mesSeleccionado) {
        mostrar = false;
      }
      
      // Filtrar por modalidad
      if (modalidadSeleccionada && modalidadCurso !== modalidadSeleccionada) {
        mostrar = false;
      }
      
      // Filtrar por horario
      if (horarioSeleccionado && horarioCurso !== horarioSeleccionado) {
        mostrar = false;
      }
      
      // Filtrar por profesor
      if (profesorSeleccionado && profesorCurso !== profesorSeleccionado) {
        mostrar = false;
      }
      
      // Filtrar por área
      if (areaSeleccionada && areaCurso !== areaSeleccionada) {
        mostrar = false;
      }
      
      card.style.display = mostrar ? '' : 'none';
      if (mostrar) cursosVisibles++;
    });
    
    // Mostrar mensaje si no hay cursos
    const grid = document.querySelector('.courses-grid');
    let mensaje = document.getElementById('no-cursos-mensaje');
    
    if (cursosVisibles === 0) {
      if (!mensaje) {
        mensaje = document.createElement('p');
        mensaje.id = 'no-cursos-mensaje';
        mensaje.style.gridColumn = '1 / -1';
        mensaje.style.textAlign = 'center';
        mensaje.style.padding = '2rem';
        mensaje.style.color = '#666';
        mensaje.textContent = 'No se encontraron cursos con los filtros seleccionados';
        grid.appendChild(mensaje);
      }
    } else if (mensaje) {
      mensaje.remove();
    }
  }
  
  // Cuando cambie cualquier select, filtrar sin recargar
  selects.forEach(select => {
    select.addEventListener('change', filtrarCursos);
  });
  
  // Botón de reset filtros
  const resetBtn = document.getElementById('reset-filters');
  if (resetBtn) {
    resetBtn.addEventListener('click', function() {
      // Resetear todos los filtros a valor vacío
      document.getElementById('filter-mes').value = '';
      document.getElementById('filter-modalidad').value = '';
      document.getElementById('filter-franja').value = '';
      document.getElementById('filter-profesor').value = '';      document.getElementById('filter-area').value = '';      
      // Volver a filtrar (mostrará todos los cursos)
      filtrarCursos();
    });
  }
  
  // Aplicar filtros al cargar si hay parámetros GET (para mantener compatibilidad)
  filtrarCursos();
});

// Navegación de testimonios
const testimonios = <?= json_encode(array_map(function($review) {
  return [
    'nombre' => $review['author_name'] ?? 'Usuario',
    'fecha' => date('d F Y', $review['time'] ?? time()),
    'inicial' => mb_substr($review['author_name'] ?? 'U', 0, 1),
    'estrellas' => $review['rating'] ?? 5,
    'texto' => $review['text'] ?? ''
  ];
}, $google_reviews_data['reviews'])) ?>;

let testimonioActual = 0;

function nextTestimonio() {
  if (testimonios.length <= 1) return;
  testimonioActual = (testimonioActual + 1) % testimonios.length;
  actualizarTestimonio();
}

function prevTestimonio() {
  if (testimonios.length <= 1) return;
  testimonioActual = (testimonioActual - 1 + testimonios.length) % testimonios.length;
  actualizarTestimonio();
}

function actualizarTestimonio() {
  const t = testimonios[testimonioActual];
  const container = document.querySelector('#testimonio-container');
  if (!container) return;
  
  container.innerHTML = `
    <div class="testimonio__header">
      <div class="testimonio__avatar">
        <span class="testimonio__avatar-letter">${t.inicial}</span>
        <span class="testimonio__avatar-badge">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M8 0L9.8 5.6H16L11 9L12.8 14.4L8 11L3.2 14.4L5 9L0 5.6H6.2L8 0Z" fill="#4285F4"/>
          </svg>
        </span>
      </div>
      <div class="testimonio__info">
        <h3 class="testimonio__name">${t.nombre}</h3>
        <p class="testimonio__date">${t.fecha}</p>
      </div>
    </div>
    
    <div class="testimonio__stars">
      ${'★'.repeat(t.estrellas)}${'☆'.repeat(5 - t.estrellas)}
      <span class="testimonio__verified">✔</span>
    </div>
    
    <p class="testimonio__text">${t.texto}</p>
    <span class="testimonio__more">Leer más</span>
  `;
}

// Preservar parámetro origin en enlaces a cursos
(function() {
  var originParam = '<?= isset($_GET["origin"]) ? htmlspecialchars($_GET["origin"]) : "" ?>';
  
  if (originParam) {
    
    window.addEventListener('load', function() {
      document.querySelectorAll('a[href]').forEach(function(link) {
        var href = link.getAttribute('href');
        if (href && !href.startsWith('http') && !href.startsWith('#') && 
            !href.includes('.') && !href.includes('?')) {
          link.href = href + '?origin=' + encodeURIComponent(originParam);
        }
      });
      console.log('✅ Enlaces actualizados con origin=' + originParam);
    });
  }
})();

// Preservar parámetros GET al cambiar de idioma
(function() {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  
  function init() {
    var linkES = document.getElementById('link-es');
    var linkCA = document.getElementById('link-ca');
    var currentParams = window.location.search;
   
    if (linkES) linkES.href = '/cursos/cursos-gratuitos' + currentParams;
    if (linkCA) linkCA.href = '/cursos/cursos-gratuitos-ca' + currentParams;
  }
})();

// Datos de cursos disponibles (desde PHP)
const cursosDisponibles = <?= json_encode(array_map(function($curso) {
  return [
    'nombre' => $curso['nom_curs'] ?? '',
    'slug' => $curso['slug'] ?? ''
  ];
}, $cursos)) ?>;

// ===== BUSCADOR RÁPIDO CON AUTOCOMPLETADO =====
(function() {
  const searchInput = document.getElementById('quickSearchInput');
  const suggestionsContainer = document.getElementById('quickSearchSuggestions');
  
  // Diccionario de traducciones ES-CA para búsquedas
  const translations = {
    // Idiomas
    'ingles': 'angles',
    'inglés': 'anglès',
    'frances': 'frances',
    'francés': 'francès',
    'aleman': 'alemany',
    'alemán': 'alemany',
    'italiano': 'italia',
    'portugues': 'portugues',
    'portugués': 'português',
    // Informática
    'excel': 'excel',
    'word': 'word',
    'powerpoint': 'powerpoint',
    'access': 'access',
    'redes': 'xarxes',
    'programacion': 'programacio',
    'programación': 'programació',
    'datos': 'dades',
    'nube': 'nuvol',
    // Administración
    'administracion': 'administracio',
    'administración': 'administració',
    'recursos humanos': 'recursos humans',
    'contabilidad': 'comptabilitat',
    'gestion': 'gestio',
    'gestión': 'gestió',
    // Habilidades
    'comunicacion': 'comunicacio',
    'comunicación': 'comunicació',
    'trabajo equipo': 'treball equip',
    'liderazgo': 'lideratge',
    'atencion cliente': 'atencio client',
    'atención cliente': 'atenció client',
    // Marketing
    'marketing': 'marqueting',
    'comercio': 'comerc',
    'ventas': 'vendes',
    'digital': 'digital',
    'redes sociales': 'xarxes socials'
  };
  
  // Base de datos de opciones de filtros
  const filterOptions = [
    // Temáticas
    {type: 'area', value: 'administracion', label: 'Administración', keywords: 'administracio'},
    {type: 'area', value: 'idiomas', label: 'Idiomas', keywords: 'idiomes llengues languages'},
    {type: 'area', value: 'informatica', label: 'Informática', keywords: 'informatica computacion ordenadores'},
    {type: 'area', value: 'interpersonales', label: 'Habilidades interpersonales', keywords: 'interpersonals habilitats soft skills'},
    {type: 'area', value: 'marketing', label: 'Marketing', keywords: 'marqueting publicidad publicitat'},
    {type: 'area', value: 'comercio', label: 'Comercio', keywords: 'comerc ventas vendes'},
    
    // Modalidades
    {type: 'modalidad', value: 'presencial', label: 'Presencial', keywords: 'presencial face face-to-face'},
    {type: 'modalidad', value: 'online', label: 'Online', keywords: 'online linea distancia virtual'},
    {type: 'modalidad', value: 'mixta', label: 'Mixta', keywords: 'mixta blended hibrida'},
    
    // Franjas horarias
    {type: 'franja', value: 'manana', label: 'Mañana', keywords: 'mati morning matinale'},
    {type: 'franja', value: 'tarde', label: 'Tarde', keywords: 'tarda afternoon vespre'},
    {type: 'franja', value: 'sabado', label: 'Sábado', keywords: 'dissabte saturday weekend'},
    
    // Meses
    {type: 'mes', value: 'enero', label: 'Enero', keywords: 'gener january'},
    {type: 'mes', value: 'febrero', label: 'Febrero', keywords: 'febrer february'},
    {type: 'mes', value: 'marzo', label: 'Marzo', keywords: 'marc march'},
    {type: 'mes', value: 'abril', label: 'Abril', keywords: 'abril april'},
    {type: 'mes', value: 'mayo', label: 'Mayo', keywords: 'maig may'},
    {type: 'mes', value: 'junio', label: 'Junio', keywords: 'juny june'},
    {type: 'mes', value: 'julio', label: 'Julio', keywords: 'juliol july'},
    {type: 'mes', value: 'agosto', label: 'Agosto', keywords: 'agost august'},
    {type: 'mes', value: 'septiembre', label: 'Septiembre', keywords: 'setembre september'},
    {type: 'mes', value: 'octubre', label: 'Octubre', keywords: 'octubre october'},
    {type: 'mes', value: 'noviembre', label: 'Noviembre', keywords: 'novembre november'},
    {type: 'mes', value: 'diciembre', label: 'Diciembre', keywords: 'desembre december'}
  ];
  
  // Agregar cursos disponibles a las opciones con keywords multiidioma
  cursosDisponibles.forEach(curso => {
    if (curso.nombre && curso.slug) {
      // Crear keywords con el nombre original más variantes traducidas
      let keywords = curso.nombre.toLowerCase();
      
      // Agregar traducciones comunes
      Object.keys(translations).forEach(key => {
        const value = translations[key];
        if (keywords.includes(value)) {
          keywords += ' ' + key;
        }
      });
      
      filterOptions.push({
        type: 'curso',
        value: curso.slug,
        label: curso.nombre,
        keywords: keywords
      });
    }
  });
  
  const badgeLabels = {
    'area': 'Temática',
    'modalidad': 'Modalidad',
    'franja': 'Horario',
    'mes': 'Mes',
    'curso': 'Curso'
  };
  
  if (!searchInput || !suggestionsContainer) return;
  
  // Normalizar texto para comparación (sin acentos)
  function normalize(text) {
    return text.toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }
  
  // Función para filtrar opciones según búsqueda
  function filterSuggestions(query) {
    if (!query.trim()) return [];
    
    const normalizedQuery = normalize(query);
    return filterOptions.filter(option => 
      normalize(option.label).includes(normalizedQuery) ||
      normalize(option.value).includes(normalizedQuery) ||
      (option.keywords && normalize(option.keywords).includes(normalizedQuery))
    );
  }
  
  // Función para renderizar sugerencias
  function renderSuggestions(suggestions) {
    if (suggestions.length === 0) {
      suggestionsContainer.innerHTML = '<div class="no-suggestions">No se encontraron resultados</div>';
      suggestionsContainer.classList.add('active');
      return;
    }
    
    const html = suggestions.map(suggestion => `
      <div class="suggestion-item" data-type="${suggestion.type}" data-value="${suggestion.value}">
        <span class="suggestion-badge ${suggestion.type}">${badgeLabels[suggestion.type]}</span>
        <span class="suggestion-text">${suggestion.label}</span>
      </div>
    `).join('');
    
    suggestionsContainer.innerHTML = html;
    suggestionsContainer.classList.add('active');
    
    // Añadir eventos click a cada sugerencia
    suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
      item.addEventListener('click', function() {
        const type = this.dataset.type;
        const value = this.dataset.value;
        
        // Si es un curso, redirigir directamente
        if (type === 'curso') {
          window.location.href = '/cursos/' + value;
          return;
        }
        
        // Establecer el valor en el select correspondiente
        const select = document.getElementById('filter-' + type);
        if (select) {
          select.value = value;
          
          // Disparar el evento change para activar el filtrado
          select.dispatchEvent(new Event('change'));
        }
        
        // Limpiar búsqueda
        searchInput.value = '';
        suggestionsContainer.classList.remove('active');
      });
    });
  }
  
  // Event listener para el input
  searchInput.addEventListener('input', function() {
    const query = this.value;
    
    if (query.trim().length === 0) {
      suggestionsContainer.classList.remove('active');
      return;
    }
    
    const suggestions = filterSuggestions(query);
    renderSuggestions(suggestions);
  });
  
  // Cerrar sugerencias al hacer click fuera
  document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
      suggestionsContainer.classList.remove('active');
    }
  });
  
  // Manejar Enter para seleccionar primera sugerencia
  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const firstSuggestion = suggestionsContainer.querySelector('.suggestion-item');
      if (firstSuggestion) {
        firstSuggestion.click();
      }
    }
  });
})();

