<?php
$jsLocale = $courseJsLocale ?? 'es';
$moreLabel = $jsLocale === 'ca' ? 'Llegir més' : 'Leer más';
$originLogPrefix = $jsLocale === 'ca'
    ? '✅ Enllaços actualitzats amb origin='
    : '✅ Enlaces actualizados con origin=';
?>
// Función para toggle de acordeones
function toggleAccordion(button) {
  const item = button.closest('.accordion__item');
  const wasActive = item.classList.contains('active');

  document.querySelectorAll('.accordion__item').forEach(i => {
    i.classList.remove('active');
  });

  if (!wasActive) {
    item.classList.add('active');
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const firstAccordion = document.querySelector('.accordion__item');
  if (firstAccordion) {
    firstAccordion.classList.add('active');
  }
});

const testimonios = <?= json_encode(array_map(function($review) {
  return [
    'nombre' => $review['author_name'] ?? 'Usuari',
    'fecha' => date('d F Y', $review['time'] ?? time()),
    'inicial' => mb_substr($review['author_name'] ?? 'U', 0, 1),
    'estrellas' => $review['rating'] ?? 5,
    'texto' => $review['text'] ?? ''
  ];
}, $google_reviews_data['reviews'])) ?>;

const moreLabel = <?= json_encode($moreLabel) ?>;
const originLogPrefix = <?= json_encode($originLogPrefix) ?>;
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
    <span class="testimonio__more">${moreLabel}</span>
  `;
}

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
      console.log(originLogPrefix + originParam);
    });
  }
})();
