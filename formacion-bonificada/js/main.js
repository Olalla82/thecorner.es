// ═══════════════════════════════════════════════
// INIT - Esperar a que el DOM esté listo
// ═══════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {

// ═══════════════════════════════════════════════
// SET ORIGIN FROM URL PARAMETER
// ═══════════════════════════════════════════════
const urlParams = new URLSearchParams(window.location.search);
const originParam = urlParams.get('origin');
const originField = document.getElementById('origin-field');
const originField2 = document.getElementById('origin-field-2');

console.log('🔍 DEBUG - URL actual:', window.location.href);
console.log('🔍 DEBUG - Origin en URL:', originParam);
console.log('🔍 DEBUG - Campo 1 encontrado:', originField ? 'SÍ' : 'NO');
console.log('🔍 DEBUG - Campo 2 encontrado:', originField2 ? 'SÍ' : 'NO');

if (originField && originParam) {
    originField.value = originParam;
    console.log('✅ Origin actualizado en campo 1:', originField.value);
}
if (originField2 && originParam) {
    originField2.value = originParam;
    console.log('✅ Origin actualizado en campo 2:', originField2.value);
}

console.log('ℹ️ Valor final campo 1:', originField ? originField.value : 'NO EXISTE');
console.log('ℹ️ Valor final campo 2:', originField2 ? originField2.value : 'NO EXISTE');
// Si no existe parámetro, mantiene el valor por defecto (15)

// ═══════════════════════════════════════════════
// STICKY FORM CTA - Mostrar al hacer scroll
// ═══════════════════════════════════════════════
const stickyCta = document.querySelector('.sticky-form-cta');
const formSection = document.querySelector('.form-section');

function handleStickyCtaVisibility() {
    if (!stickyCta || !formSection) return; // Salir si no existen los elementos
    
    const scrolled = window.scrollY > 600;
    const formInView = formSection.getBoundingClientRect().top < window.innerHeight;
    
    if (scrolled && !formInView) {
        stickyCta.classList.add('visible');
    } else {
        stickyCta.classList.remove('visible');
    }
}

if (stickyCta && formSection) {
    window.addEventListener('scroll', handleStickyCtaVisibility);
}

// ═══════════════════════════════════════════════
// FORM SUBMISSION - Validación y feedback
// ═══════════════════════════════════════════════
document.querySelectorAll('form').forEach(form => {
    console.log('🔍 DEBUG - Formulario encontrado, añadiendo listener');
    
    form.addEventListener('submit', function(e) {
        console.log('📤 DEBUG - Evento submit disparado');
        console.log('📤 DEBUG - Formulario válido:', this.checkValidity());
        
        // Validar campos antes de enviar
        if (!this.checkValidity()) {
            console.log('❌ DEBUG - Formulario no válido, abortando');
            return; // Deja que el navegador muestre los mensajes de validación
        }

        // Obtener el valor del origin desde el campo hidden
        const originField = this.querySelector('[name="origin"]');
        const originValue = originField ? originField.value : '15';
        
        console.log('📤 DEBUG - Origin field encontrado:', originField ? 'SÍ' : 'NO');
        console.log('📤 DEBUG - Valor de origin:', originValue);
        
        // Actualizar el action para incluir origin en la URL (como en cursos)
        const oldAction = this.action;
        this.action = `https://thecorner.es/thank-you-page/?origin=${originValue}`;
        
        console.log('📤 DEBUG - Action ANTES:', oldAction);
        console.log('📤 DEBUG - Action DESPUÉS:', this.action);

        // Cambiar el botón mientras se envía (feedback visual)
        const btn = this.querySelector('.form-submit');
        console.log('📤 DEBUG - Botón encontrado:', btn ? 'SÍ' : 'NO');
        
        if (!btn) return;
        
        btn.textContent = 'Enviando...';
        btn.disabled = true;
        
        console.log('✅ DEBUG - Formulario se enviará ahora con:');
        console.log('   - URL destino:', this.action);
        console.log('   - Origin value:', originValue);
        
        // El formulario se enviará automáticamente vía POST con:
        // - origin en URL (GET): ?origin=15
        // - origin en body (POST): name="origin" value="15"
    });
});

// ═══════════════════════════════════════════════
// SMOOTH SCROLL - Scroll suave para anclas
// ═══════════════════════════════════════════════
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        const target = document.querySelector(targetId);
        
        if (target) {
            const navHeight = document.querySelector('nav').offsetHeight;
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// ═══════════════════════════════════════════════
// INTERSECTION OBSERVER - Animaciones al scroll
// ═══════════════════════════════════════════════
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observar elementos con clase .curso-card y .why-card
document.querySelectorAll('.curso-card, .why-card').forEach(el => {
    // Configurar estado inicial
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
    
    // Observar elemento
    observer.observe(el);
});

// ═══════════════════════════════════════════════
// PERFORMANCE - Debounce para scroll events
// ═══════════════════════════════════════════════
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Aplicar debounce al sticky CTA
const debouncedStickyHandler = debounce(handleStickyCtaVisibility, 10);
window.addEventListener('scroll', debouncedStickyHandler);

// ═══════════════════════════════════════════════
// CONSOLE INFO - Información de desarrollo
// ═══════════════════════════════════════════════
console.log('%c🚀 The Corner - Formación Empresas', 'color: #c2d500; font-size: 16px; font-weight: bold;');

}); // Fin DOMContentLoaded
