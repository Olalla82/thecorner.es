/* ══════════════════════════════════════════════════════════════════════════════ */
/* ── THE CORNER SUMMER CAMP — JAVASCRIPT ────────────────────────────────────── */
/* ══════════════════════════════════════════════════════════════════════════════ */

(function() {
  'use strict';

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── TRACKING DE ORIGEN (ORIGEN 7 = SUMMER CAMP) ────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initOriginTracking() {
    // Leer parámetro ?origin= de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const originParam = urlParams.get('origin');
    
    // Si existe el parámetro, actualizar los campos hidden
    if (originParam) {
      const originField1 = document.getElementById('origin-field');
      const originField2 = document.getElementById('origin-field-2');
      
      if (originField1) {
        originField1.value = originParam;
        console.log('✓ Origin field 1 actualizado a:', originParam);
      }
      if (originField2) {
        originField2.value = originParam;
        console.log('✓ Origin field 2 actualizado a:', originParam);
      }
    } else {
      console.log('✓ Origin por defecto (7) para Summer Camp');
    }
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── PRESELECCIÓN DE CURSO EN FORMULARIO ────────────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initCourseSelection() {
    const courseButtons = document.querySelectorAll('.curso-btn[data-curso]');
    
    courseButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Obtener el curso del atributo data
        const cursoSeleccionado = this.getAttribute('data-curso');
        
        // Preseleccionar en AMBOS selects (hero y formulario final)
        const selects = document.querySelectorAll('select[name="menu-523"]');
        selects.forEach(select => {
          select.value = cursoSeleccionado;
          // Añadir feedback visual
          select.style.borderColor = 'rgba(196, 214, 0, 0.8)';
          setTimeout(() => {
            select.style.borderColor = '';
          }, 2000);
        });
        
        // Hacer scroll suave al formulario final
        const formularioFinal = document.querySelector('#reservar');
        if (formularioFinal) {
          const navHeight = 80; // Altura aproximada del nav
          const targetPosition = formularioFinal.getBoundingClientRect().top + window.pageYOffset - navHeight - 20;
          
          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });
        }
        
        console.log('✓ Curso preseleccionado:', cursoSeleccionado);
      });
    });
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── ANIMACIONES SCROLL REVEAL ──────────────────────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initScrollReveal() {
    const revealElements = document.querySelectorAll('.reveal');
    
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            entry.target.classList.add('active');
          }, index * 100); // Efecto cascada
          revealObserver.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.15,
      rootMargin: '0px 0px -50px 0px'
    });

    revealElements.forEach(element => {
      revealObserver.observe(element);
    });
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── SMOOTH SCROLL PARA NAVEGACIÓN ──────────────────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
      link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        
        // Ignorar enlaces vacíos o solo #
        if (href === '#' || href === '') {
          e.preventDefault();
          return;
        }
        
        const targetId = href.substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
          e.preventDefault();
          
          const navHeight = document.querySelector('nav').offsetHeight;
          const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navHeight - 20;
          
          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });
        }
      });
    });
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── MANEJO DE FORMULARIOS ──────────────────────────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initFormHandling() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
      form.addEventListener('submit', function(e) {
        // Validación básica (con nuevos nombres de campos)
        const nombre = form.querySelector('[name="your-name"]');
        const email = form.querySelector('[name="your-email"]');
        const telefono = form.querySelector('[name="telefono"]');
        const curso = form.querySelector('[name="menu-523"]');
        
        let isValid = true;
        let errorMessage = '';
        
        // Validar nombre
        if (nombre && nombre.value.trim().length < 2) {
          isValid = false;
          errorMessage += 'Por favor, introduce un nombre válido.\n';
          nombre.style.borderColor = '#ff7a3d';
        } else if (nombre) {
          nombre.style.borderColor = '';
        }
        
        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email.value)) {
          isValid = false;
          errorMessage += 'Por favor, introduce un email válido.\n';
          email.style.borderColor = '#ff7a3d';
        } else if (email) {
          email.style.borderColor = '';
        }
        
        // Validar teléfono
        const telefonoRegex = /^[0-9]{9,}$/;
        if (telefono && !telefonoRegex.test(telefono.value.replace(/\s/g, ''))) {
          isValid = false;
          errorMessage += 'Por favor, introduce un teléfono válido (mínimo 9 dígitos).\n';
          telefono.style.borderColor = '#ff7a3d';
        } else if (telefono) {
          telefono.style.borderColor = '';
        }
        
        // Validar curso seleccionado
        if (curso && !curso.value) {
          isValid = false;
          errorMessage += 'Por favor, selecciona un curso.\n';
          curso.style.borderColor = '#ff7a3d';
        } else if (curso) {
          curso.style.borderColor = '';
        }
        
        if (!isValid) {
          e.preventDefault(); // Solo prevenir si hay errores
          alert(errorMessage);
          return;
        }
        
        // Si todo es válido, permitir el envío normal del formulario
        // El formulario se enviará a https://thecorner.es/thank-you-page-intensivos/
        console.log('✓ Formulario válido, enviando a thank-you page...');
      });
    });
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── NAVEGACIÓN CON SCROLL ──────────────────────────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initNavbarScroll() {
    const nav = document.querySelector('nav');
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;
      
      if (currentScroll <= 0) {
        nav.style.boxShadow = 'none';
      } else {
        nav.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.3)';
      }
      
      lastScroll = currentScroll;
    });
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── FAQ ACCORDION ──────────────────────────────────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
      const summary = item.querySelector('.faq-q');
      
      summary.addEventListener('click', () => {
        // Cerrar otros items abiertos (opcional - comentar si quieres múltiples abiertos)
        faqItems.forEach(otherItem => {
          if (otherItem !== item && otherItem.hasAttribute('open')) {
            otherItem.removeAttribute('open');
          }
        });
      });
    });
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── VALIDACIÓN INPUTS EN TIEMPO REAL ───────────────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initInputValidation() {
    const emailInputs = document.querySelectorAll('input[type="email"]');
    const telInputs = document.querySelectorAll('input[type="tel"]');
    
    // Validación de email en tiempo real
    emailInputs.forEach(input => {
      input.addEventListener('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
          this.style.borderColor = '#ff7a3d';
        } else {
          this.style.borderColor = '';
        }
      });
      
      input.addEventListener('input', function() {
        if (this.style.borderColor === 'rgb(255, 122, 61)') {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (emailRegex.test(this.value)) {
            this.style.borderColor = '';
          }
        }
      });
    });
    
    // Formateo de teléfono
    telInputs.forEach(input => {
      input.addEventListener('input', function(e) {
        // Eliminar todo lo que no sea número
        let value = this.value.replace(/\D/g, '');
        
        // Si tiene más de 9 dígitos y empieza con "34" (prefijo España), eliminarlo
        if (value.length > 9 && value.startsWith('34')) {
          value = value.substring(2); // Quitar el "34"
        }
        
        // Limitar a 9 dígitos
        if (value.length > 9) {
          value = value.substring(0, 9);
        }
        
        this.value = value;
      });
      
      input.addEventListener('blur', function() {
        if (this.value && this.value.length < 9) {
          this.style.borderColor = '#ff7a3d';
        } else {
          this.style.borderColor = '';
        }
      });
    });
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── TRACKING DE INTERACCIONES (GA4, META PIXEL, ETC.) ──────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initTracking() {
    // CTA clicks
    const ctaButtons = document.querySelectorAll('.btn-primary, .curso-btn, .form-submit');
    
    ctaButtons.forEach(button => {
      button.addEventListener('click', function() {
        const buttonText = this.textContent.trim();
        
        // Google Analytics 4
        if (typeof gtag !== 'undefined') {
          gtag('event', 'cta_click', {
            'event_category': 'engagement',
            'event_label': buttonText,
            'value': 1
          });
        }
        
        // Meta Pixel
        if (typeof fbq !== 'undefined') {
          fbq('track', 'Lead', {
            content_name: buttonText
          });
        }
        
        console.log('CTA Click tracked:', buttonText);
      });
    });
    
    // Scroll depth tracking
    let scrollDepth = {
      25: false,
      50: false,
      75: false,
      100: false
    };
    
    window.addEventListener('scroll', function() {
      const scrollPercentage = (window.scrollY + window.innerHeight) / document.documentElement.scrollHeight * 100;
      
      Object.keys(scrollDepth).forEach(depth => {
        if (scrollPercentage >= depth && !scrollDepth[depth]) {
          scrollDepth[depth] = true;
          
          if (typeof gtag !== 'undefined') {
            gtag('event', 'scroll_depth', {
              'event_category': 'engagement',
              'event_label': depth + '%',
              'value': parseInt(depth)
            });
          }
          
          console.log('Scroll depth tracked:', depth + '%');
        }
      });
    });
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── LAZY LOADING DE IMÁGENES ───────────────────────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function initLazyLoading() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src || img.src;
            img.classList.add('loaded');
            imageObserver.unobserve(img);
          }
        });
      });
      
      images.forEach(img => imageObserver.observe(img));
    }
  }

  /* ══════════════════════════════════════════════════════════════════════════════ */
  /* ── INICIALIZACIÓN GLOBAL ──────────────────────────────────────────────────── */
  /* ══════════════════════════════════════════════════════════════════════════════ */

  function init() {
    // Esperar a que el DOM esté completamente cargado
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', runInit);
    } else {
      runInit();
    }
  }

  function runInit() {
    console.log('🚀 The Corner Summer Camp - JavaScript Initialized');
    
    // Ejecutar todas las inicializaciones
    initOriginTracking();  // PRIMERO: leer parámetro origin de URL
    initCourseSelection(); // SEGUNDO: preselección de curso desde botones
    initScrollReveal();
    initSmoothScroll();
    initFormHandling();
    initNavbarScroll();
    initFaqAccordion();
    initInputValidation();
    initTracking();
    initLazyLoading();
    
    console.log('✅ All modules loaded successfully');
  }

  // Iniciar aplicación
  init();

})();
