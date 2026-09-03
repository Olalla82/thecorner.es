/* ═══════════════════════════════════════════════
   KINDER CORNER - MAIN JAVASCRIPT
   Academia The Corner - Santa Coloma de Gramenet
   Matrícula 2026-27
════════════════════════════════════════════════ */

(function() {
  'use strict';

  // ═══════════════════════════════════════════════
  // SET ORIGIN FROM URL PARAMETER
  // ═══════════════════════════════════════════════
  function initOriginParam() {
    const urlParams = new URLSearchParams(window.location.search);
    const originParam = urlParams.get('origin');
    const originField = document.getElementById('origin-field');
    
    if (originField && originParam) {
      originField.value = originParam;
      console.log('✅ Origin capturado de URL:', originParam);
    } else if (originField) {
      console.log('✅ Origin por defecto:', originField.value);
    }
  }

  // ═══════════════════════════════════════════════
  // SMOOTH SCROLL
  // ═══════════════════════════════════════════════
  function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
      link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        
        // Ignorar enlaces sin hash específico
        if (href === '#') return;
        
        const target = document.querySelector(href);
        
        if (target) {
          e.preventDefault();
          
          const navHeight = document.querySelector('nav').offsetHeight;
          const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight - 20;
          
          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });
        }
      });
    });
  }

  // ═══════════════════════════════════════════════
  // STICKY CTA MOBILE - Show/Hide on scroll
  // ═══════════════════════════════════════════════
  function initStickyCTA() {
    const stickyCTA = document.querySelector('.sticky-cta-kinder');
    const footer = document.querySelector('footer');
    let lastScroll = 0;
    
    if (!stickyCTA) return;
    
    window.addEventListener('scroll', function() {
      const currentScroll = window.pageYOffset;
      const footerTop = footer.getBoundingClientRect().top;
      const windowHeight = window.innerHeight;
      
      // Ocultar cuando llegamos al footer
      if (footerTop < windowHeight) {
        stickyCTA.style.transform = 'translateY(100%)';
      } 
      // Mostrar solo si scrolleamos hacia abajo y hemos pasado el hero
      else if (currentScroll > 500 && currentScroll > lastScroll) {
        stickyCTA.style.transform = 'translateY(0)';
      } 
      // Ocultar cuando scrolleamos hacia arriba
      else if (currentScroll < lastScroll) {
        stickyCTA.style.transform = 'translateY(100%)';
      }
      
      lastScroll = currentScroll;
    });
  }

  // ═══════════════════════════════════════════════
  // REVEAL ON SCROLL - Intersection Observer
  // ═══════════════════════════════════════════════
  function initRevealOnScroll() {
    const reveals = document.querySelectorAll('.reveal');
    
    if ('IntersectionObserver' in window) {
      const revealObserver = new IntersectionObserver(
        (entries, observer) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('visible');
              observer.unobserve(entry.target);
            }
          });
        },
        {
          threshold: 0.1,
          rootMargin: '0px 0px -50px 0px'
        }
      );
      
      reveals.forEach(reveal => {
        revealObserver.observe(reveal);
      });
    } else {
      // Fallback para navegadores antiguos
      reveals.forEach(reveal => {
        reveal.classList.add('visible');
      });
    }
  }

  // ═══════════════════════════════════════════════
  // FORM VALIDATION
  // ═══════════════════════════════════════════════
  function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
      form.addEventListener('submit', function(e) {
        const inputs = this.querySelectorAll('input[required], select[required]');
        let isValid = true;
        
        inputs.forEach(input => {
          // Limpiar estilos previos
          input.style.borderColor = '';
          
          if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#ff6b6b';
            input.focus();
          }
          
          // Validación de email
          if (input.type === 'email' && input.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
              isValid = false;
              input.style.borderColor = '#ff6b6b';
            }
          }
          
          // Validación de teléfono (básica)
          if (input.type === 'tel' && input.value.trim()) {
            const phoneRegex = /^[0-9]{9,}$/;
            const cleanPhone = input.value.replace(/\s/g, '');
            if (!phoneRegex.test(cleanPhone)) {
              isValid = false;
              input.style.borderColor = '#ff6b6b';
            }
          }
        });
        
        if (!isValid) {
          e.preventDefault();
          showErrorMessage(this);
        } else {
          // Cambiar el botón mientras se envía
          const btn = this.querySelector('.form-submit');
          if (btn) {
            btn.textContent = 'Enviando...';
            btn.disabled = true;
          }
          // El formulario se enviará automáticamente a la thank-you-page
        }
      });
      
      // Limpiar error al escribir
      const inputs = form.querySelectorAll('input, select');
      inputs.forEach(input => {
        input.addEventListener('input', function() {
          this.style.borderColor = '';
        });
      });
    });
  }

  // ═══════════════════════════════════════════════
  // SHOW SUCCESS MESSAGE
  // ═══════════════════════════════════════════════
  function showSuccessMessage(form) {
    const message = document.createElement('div');
    message.className = 'form-message success';
    message.innerHTML = `
      <div style="
        padding: 16px 24px;
        background: #c2d500;
        color: #1a1a1a;
        border-radius: 12px;
        margin-top: 16px;
        font-weight: 600;
        text-align: center;
        animation: slideIn 0.3s ease;
      ">
        ✓ ¡Gracias! Te contactaremos en 24/48h
      </div>
    `;
    
    // Eliminar mensaje previo si existe
    const prevMessage = form.querySelector('.form-message');
    if (prevMessage) prevMessage.remove();
    
    form.appendChild(message);
    
    setTimeout(() => {
      message.remove();
    }, 5000);
  }

  // ═══════════════════════════════════════════════
  // SHOW ERROR MESSAGE
  // ═══════════════════════════════════════════════
  function showErrorMessage(form) {
    const message = document.createElement('div');
    message.className = 'form-message error';
    message.innerHTML = `
      <div style="
        padding: 16px 24px;
        background: #ff6b6b;
        color: white;
        border-radius: 12px;
        margin-top: 16px;
        font-weight: 600;
        text-align: center;
        animation: slideIn 0.3s ease;
      ">
        ⚠️ Por favor, completa todos los campos correctamente
      </div>
    `;
    
    // Eliminar mensaje previo si existe
    const prevMessage = form.querySelector('.form-message');
    if (prevMessage) prevMessage.remove();
    
    form.appendChild(message);
    
    setTimeout(() => {
      message.remove();
    }, 4000);
  }

  // ═══════════════════════════════════════════════
  // MOBILE MENU TOGGLE
  // ═══════════════════════════════════════════════
  function initMobileMenu() {
    const mobileBtn = document.querySelector('.nav-mobile-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (!mobileBtn || !navLinks) return;
    
    mobileBtn.addEventListener('click', function() {
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      
      this.setAttribute('aria-expanded', !isExpanded);
      navLinks.classList.toggle('active');
      
      // Cambiar icono
      if (!isExpanded) {
        this.innerHTML = `
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M6 18L18 6M6 6l12 12"/>
          </svg>
        `;
      } else {
        this.innerHTML = `
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M3 6h18M3 12h18M3 18h18"/>
          </svg>
        `;
      }
    });
    
    // Cerrar menú al hacer click en un enlace
    const menuLinks = navLinks.querySelectorAll('a');
    menuLinks.forEach(link => {
      link.addEventListener('click', function() {
        navLinks.classList.remove('active');
        mobileBtn.setAttribute('aria-expanded', 'false');
        mobileBtn.innerHTML = `
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M3 6h18M3 12h18M3 18h18"/>
          </svg>
        `;
      });
    });
  }

  // ═══════════════════════════════════════════════
  // AGE BADGE CLICK - Smooth scroll to form
  // ═══════════════════════════════════════════════
  function initAgeBadgeClick() {
    const ageBadges = document.querySelectorAll('.age-badge');
    const formSelect = document.querySelector('select[name="menu-523"]');
    
    if (!formSelect) return;
    
    ageBadges.forEach((badge, index) => {
      badge.addEventListener('click', function() {
        // Scroll al formulario
        const form = document.querySelector('#reservar');
        if (form) {
          const navHeight = document.querySelector('nav').offsetHeight;
          const targetPosition = form.getBoundingClientRect().top + window.pageYOffset - navHeight - 20;
          
          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });
          
          // Seleccionar la edad correspondiente
          setTimeout(() => {
            if (index === 0) formSelect.value = '3';
            if (index === 1) formSelect.value = '4';
            if (index === 2) formSelect.value = '5';
            
            // Highlight del select
            formSelect.style.borderColor = '#c2d500';
            setTimeout(() => {
              formSelect.style.borderColor = '';
            }, 2000);
          }, 600);
        }
      });
    });
  }

  // ═══════════════════════════════════════════════
  // NAVBAR SHADOW ON SCROLL
  // ═══════════════════════════════════════════════
  function initNavbarShadow() {
    const nav = document.querySelector('nav');
    
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 50) {
        nav.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
      } else {
        nav.style.boxShadow = '0 2px 12px rgba(0,0,0,0.06)';
      }
    });
  }

  // ═══════════════════════════════════════════════
  // LAZY LOAD IMAGES
  // ═══════════════════════════════════════════════
  function initLazyLoad() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.src; // Trigger load
            observer.unobserve(img);
          }
        });
      });
      
      images.forEach(img => imageObserver.observe(img));
    }
  }

  // ═══════════════════════════════════════════════
  // PROMO CARD CLICK - Smooth scroll to form
  // ═══════════════════════════════════════════════
  function initPromoCardClick() {
    const promoCTAs = document.querySelectorAll('.promo-cta');
    const formSelect = document.querySelector('select[name="menu-523"]');
    
    promoCTAs.forEach((cta, index) => {
      cta.addEventListener('click', function(e) {
        if (this.getAttribute('href') === '#reservar') {
          e.preventDefault();
          
          const form = document.querySelector('#reservar');
          if (form) {
            const navHeight = document.querySelector('nav').offsetHeight;
            const targetPosition = form.getBoundingClientRect().top + window.pageYOffset - navHeight - 20;
            
            window.scrollTo({
              top: targetPosition,
              behavior: 'smooth'
            });
            
            // Pre-seleccionar la edad en el formulario
            if (formSelect) {
              setTimeout(() => {
                if (index === 0) formSelect.value = '3';
                if (index === 1) formSelect.value = '4';
                if (index === 2) formSelect.value = '5';
                
                formSelect.style.borderColor = '#c2d500';
                setTimeout(() => {
                  formSelect.style.borderColor = '';
                }, 2000);
              }, 600);
            }
          }
        }
      });
    });
  }

  // ═══════════════════════════════════════════════
  // INIT ALL
  // ═══════════════════════════════════════════════
  function init() {
    console.log('🎈 Kinder Corner - The Corner Santa Coloma');
    
    initOriginParam();
    initSmoothScroll();
    initStickyCTA();
    initRevealOnScroll();
    initFormValidation();
    initMobileMenu();
    initAgeBadgeClick();
    initNavbarShadow();
    initLazyLoad();
    initPromoCardClick();
  }

  // Ejecutar cuando el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
