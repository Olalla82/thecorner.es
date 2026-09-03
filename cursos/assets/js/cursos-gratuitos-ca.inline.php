// ===== TARGETES CLICABLES AMB DETECCIÓ TAP vs SCROLL =====
// Fa tota la targeta clicable però evita obrir el curs durant scroll a mòbil
(function() {
  const clickableCards = document.querySelectorAll('.course-tile--clickable');
  
  clickableCards.forEach(card => {
    let startX = 0;
    let startY = 0;
    let startTime = 0;
    let hasMoved = false;
    
    // Detectar inici del toc
    card.addEventListener('touchstart', function(e) {
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
      startTime = Date.now();
      hasMoved = false;
    }, { passive: true });
    
    // Detectar moviment durant el toc
    card.addEventListener('touchmove', function(e) {
      const moveX = e.touches[0].clientX;
      const moveY = e.touches[0].clientY;
      const diffX = Math.abs(moveX - startX);
      const diffY = Math.abs(moveY - startY);
      
      // Si es va moure més de 10px en qualsevol direcció, és un scroll
      if (diffX > 10 || diffY > 10) {
        hasMoved = true;
      }
    }, { passive: true });
    
    // Detectar fi del toc
    card.addEventListener('touchend', function(e) {
      const duration = Date.now() - startTime;
      
      // Si no es va moure I la duració va ser curta (< 500ms), és un tap intencional
      if (!hasMoved && duration < 500) {
        const href = card.dataset.href;
        if (href) {
          // Prevenir el clic de l'enllaç de la imatge (evitar doble navegació)
          e.preventDefault();
          window.location.href = href;
        }
      }
    }, { passive: false });
    
    // Per a desktop, clic simple a qualsevol part excepte enllaços
    card.addEventListener('click', function(e) {
      // Només a desktop (quan no hi ha touchstart)
      if (!('ontouchstart' in window)) {
        // Si NO es va fer clic en un enllaç, navegar
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

// Filtrat automàtic de cursos (només per contingut visible a les targetes)
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('.filters');
  const selects = form.querySelectorAll('select');
  const cards = document.querySelectorAll('.course-tile');
  
  // Funció per filtrar targetes per contingut visible
  function filtrarCursos() {
    const mesSeleccionat = document.getElementById('filter-mes')?.value || '';
    const modalitatSeleccionada = document.getElementById('filter-modalidad')?.value || '';
    const horariSeleccionat = document.getElementById('filter-franja')?.value || '';
    const professorSeleccionat = document.getElementById('filter-profesor')?.value || '';
    const areaSeleccionada = document.getElementById('filter-area')?.value || '';
    
    let cursosVisibles = 0;
    
    cards.forEach(card => {
      const mesCurs = card.dataset.mes || '';
      const modalitatCurs = card.dataset.modalidad || '';
      const horariCurs = card.dataset.horario || '';
      const professorCurs = card.dataset.profesor || '';
      const areaCurs = card.dataset.area || '';
      
      let mostrar = true;
      
      // Filtrar per mes (només pel mes d'inici visible a la targeta)
      if (mesSeleccionat && mesCurs !== mesSeleccionat) {
        mostrar = false;
      }
      
      // Filtrar per modalitat
      if (modalitatSeleccionada && modalitatCurs !== modalitatSeleccionada) {
        mostrar = false;
      }
      
      // Filtrar per horari
      if (horariSeleccionat && horariCurs !== horariSeleccionat) {
        mostrar = false;
      }
      
      // Filtrar per professor
      if (professorSeleccionat && professorCurs !== professorSeleccionat) {
        mostrar = false;
      }
      
      // Filtrar per àrea
      if (areaSeleccionada && areaCurs !== areaSeleccionada) {
        mostrar = false;
      }
      
      card.style.display = mostrar ? '' : 'none';
      if (mostrar) cursosVisibles++;
    });
    
    // Mostrar missatge si no hi ha cursos
    const grid = document.querySelector('.courses-grid');
    let missatge = document.getElementById('no-cursos-missatge');
    
    if (cursosVisibles === 0) {
      if (!missatge) {
        missatge = document.createElement('p');
        missatge.id = 'no-cursos-missatge';
        missatge.style.gridColumn = '1 / -1';
        missatge.style.textAlign = 'center';
        missatge.style.padding = '2rem';
        missatge.style.color = '#666';
        missatge.textContent = 'No s\'han trobat cursos amb els filtres seleccionats';
        grid.appendChild(missatge);
      }
    } else if (missatge) {
      missatge.remove();
    }
  }
  
  // Quan canviï qualsevol select, filtrar sense recarregar
  selects.forEach(select => {
    select.addEventListener('change', filtrarCursos);
  });
  
  // Botó de reiniciar filtres
  const resetBtn = document.getElementById('reset-filters');
  if (resetBtn) {
    resetBtn.addEventListener('click', function() {
      // Reiniciar tots els filtres a valor buit
      document.getElementById('filter-mes').value = '';
      document.getElementById('filter-modalidad').value = '';
      document.getElementById('filter-franja').value = '';
      document.getElementById('filter-profesor').value = '';
      document.getElementById('filter-area').value = '';
      
      // Tornar a filtrar (mostrarà tots els cursos)
      filtrarCursos();
    });
  }
  
  // Aplicar filtres en carregar si hi ha paràmetres GET (per mantenir compatibilitat)
  filtrarCursos();
});

// Dades de cursos disponibles (des de PHP)
const cursosDisponibles = <?= json_encode(array_map(function($curso) {
  return [
    'nombre' => $curso['nom_curs'] ?? '',
    'slug' => $curso['slug'] ?? ''
  ];
}, $cursos)) ?>;

// ===== CERCADOR RÀPID AMB AUTOCOMPLETAT =====
(function() {
  const searchInput = document.getElementById('quickSearchInput');
  const suggestionsContainer = document.getElementById('quickSearchSuggestions');
  
  // Diccionari de traduccions CA-ES per a cerques
  const translations = {
    // Idiomes
    'angles': 'ingles',
    'anglès': 'inglés',
    'frances': 'frances',
    'francès': 'francés',
    'alemany': 'aleman',
    'italia': 'italiano',
    'portugues': 'portugues',
    'português': 'portugués',
    // Informàtica
    'excel': 'excel',
    'word': 'word',
    'powerpoint': 'powerpoint',
    'access': 'access',
    'xarxes': 'redes',
    'programacio': 'programacion',
    'programació': 'programación',
    'dades': 'datos',
    'nuvol': 'nube',
    // Administració
    'administracio': 'administracion',
    'administració': 'administración',
    'recursos humans': 'recursos humanos',
    'comptabilitat': 'contabilidad',
    'gestio': 'gestion',
    'gestió': 'gestión',
    // Habilitats
    'comunicacio': 'comunicacion',
    'comunicació': 'comunicación',
    'treball equip': 'trabajo equipo',
    'lideratge': 'liderazgo',
    'atencio client': 'atencion cliente',
    'atenció client': 'atención cliente',
    // Màrqueting
    'marqueting': 'marketing',
    'comerc': 'comercio',
    'vendes': 'ventas',
    'digital': 'digital',
    'xarxes socials': 'redes sociales'
  };
  
  // Base de dades d'opcions de filtres
  const filterOptions = [
    // Temàtiques
    {type: 'area', value: 'administracio', label: 'Administració', keywords: 'administracion gestion'},
    {type: 'area', value: 'idiomes', label: 'Idiomes', keywords: 'idiomas lenguas languages'},
    {type: 'area', value: 'informatica', label: 'Informàtica', keywords: 'informatica computacion ordenadores'},
    {type: 'area', value: 'interpersonals', label: 'Habilitats interpersonals', keywords: 'interpersonales habilidades soft skills'},
    {type: 'area', value: 'marketing', label: 'Màrqueting', keywords: 'marketing publicidad publicitat'},
    {type: 'area', value: 'comerc', label: 'Comerç', keywords: 'comercio ventas vendes'},
    
    // Modalitats
    {type: 'modalidad', value: 'presencial', label: 'Presencial', keywords: 'presencial face face-to-face'},
    {type: 'modalidad', value: 'online', label: 'Online', keywords: 'online linea distancia virtual'},
    {type: 'modalidad', value: 'mixta', label: 'Mixta', keywords: 'mixta blended hibrida'},
    
    // Franges horàries
    {type: 'franja', value: 'mati', label: 'Matí', keywords: 'manana morning matinale'},
    {type: 'franja', value: 'tarda', label: 'Tarda', keywords: 'tarde afternoon vespre'},
    {type: 'franja', value: 'dissabte', label: 'Dissabte', keywords: 'sabado saturday weekend'},
    
    // Mesos
    {type: 'mes', value: 'gener', label: 'Gener', keywords: 'enero january'},
    {type: 'mes', value: 'febrer', label: 'Febrer', keywords: 'febrero february'},
    {type: 'mes', value: 'març', label: 'Març', keywords: 'marzo march'},
    {type: 'mes', value: 'abril', label: 'Abril', keywords: 'abril april'},
    {type: 'mes', value: 'maig', label: 'Maig', keywords: 'mayo may'},
    {type: 'mes', value: 'juny', label: 'Juny', keywords: 'junio june'},
    {type: 'mes', value: 'juliol', label: 'Juliol', keywords: 'julio july'},
    {type: 'mes', value: 'agost', label: 'Agost', keywords: 'agosto august'},
    {type: 'mes', value: 'setembre', label: 'Setembre', keywords: 'septiembre september'},
    {type: 'mes', value: 'octubre', label: 'Octubre', keywords: 'octubre october'},
    {type: 'mes', value: 'novembre', label: 'Novembre', keywords: 'noviembre november'},
    {type: 'mes', value: 'desembre', label: 'Desembre', keywords: 'diciembre december'}
  ];
  
  // Afegir cursos disponibles a les opcions amb keywords multiidioma
  cursosDisponibles.forEach(curso => {
    if (curso.nombre && curso.slug) {
      // Crear keywords amb el nom original més variants traduïdes
      let keywords = curso.nombre.toLowerCase();
      
      // Afegir traduccions comunes
      Object.keys(translations).forEach(key => {
        const value = translations[key];
        if (keywords.includes(key)) {
          keywords += ' ' + value;
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
    'area': 'Temàtica',
    'modalidad': 'Modalitat',
    'franja': 'Horari',
    'mes': 'Mes',
    'curso': 'Curs'
  };
  
  if (!searchInput || !suggestionsContainer) return;
  
  // Normalitzar text per a comparació (sense accents)
  function normalize(text) {
    return text.toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }
  
  // Funció per filtrar opcions segons cerca
  function filterSuggestions(query) {
    if (!query.trim()) return [];
    
    const normalizedQuery = normalize(query);
    return filterOptions.filter(option => 
      normalize(option.label).includes(normalizedQuery) ||
      normalize(option.value).includes(normalizedQuery) ||
      (option.keywords && normalize(option.keywords).includes(normalizedQuery))
    );
  }
  
  // Funció per renderitzar suggeriments
  function renderSuggestions(suggestions) {
    if (suggestions.length === 0) {
      suggestionsContainer.innerHTML = '<div class="no-suggestions">No s\'han trobat resultats</div>';
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
    
    // Afegir esdeveniments click a cada suggeriment
    suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
      item.addEventListener('click', function() {
        const type = this.dataset.type;
        const value = this.dataset.value;
        
        // Si és un curs, redirigir directament
        if (type === 'curso') {
          window.location.href = '/cursos/' + value;
          return;
        }
        
        // Establir el valor en el select corresponent
        const select = document.getElementById('filter-' + type);
        if (select) {
          select.value = value;
          
          // Disparar l'esdeveniment change per activar el filtrat
          select.dispatchEvent(new Event('change'));
        }
        
        // Netejar cerca
        searchInput.value = '';
        suggestionsContainer.classList.remove('active');
      });
    });
  }
  
  // Event listener per al input
  searchInput.addEventListener('input', function() {
    const query = this.value;
    
    if (query.trim().length === 0) {
      suggestionsContainer.classList.remove('active');
      return;
    }
    
    const suggestions = filterSuggestions(query);
    renderSuggestions(suggestions);
  });
  
  // Tancar suggeriments en fer clic fora
  document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
      suggestionsContainer.classList.remove('active');
    }
  });
  
  // Gestionar Enter per seleccionar primer suggeriment
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

// Navegació de testimonis
const testimonios = <?= json_encode(array_map(function($review) {
  return [
    'nombre' => $review['author_name'] ?? 'Usuari',
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
    <span class="testimonio__more">Llegir més</span>
  `;
}

// Preservar paràmetre origin en enllaços a cursos
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
      console.log('✅ Enllaços actualitzats amb origin=' + originParam);
    });
  }
})();

// Preservar paràmetres GET en canviar d'idioma
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

