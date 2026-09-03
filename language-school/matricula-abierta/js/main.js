/* ================================================================
   THE CORNER — Landing Matrícula 2025-26
   main.js
   ================================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── 0. SET ORIGIN FROM URL PARAMETER ──────────────────────── */
  const urlParams = new URLSearchParams(window.location.search);
  const originParam = urlParams.get('origin');
  const originField = document.getElementById('origin-field');
  const originField2 = document.getElementById('origin-field-2');
  
  if (originField && originParam) {
    originField.value = originParam;
  }
  if (originField2 && originParam) {
    originField2.value = originParam;
  }
  // Si no existe parámetro, mantiene el valor por defecto (7)

  /* ── 1. SCROLL REVEAL ──────────────────────────────────────── */
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
  );

  document.querySelectorAll('.reveal').forEach((el, i) => {
    el.style.transitionDelay = `${(i % 4) * 0.08}s`;
    revealObserver.observe(el);
  });


  /* ── 2. FORM SUBMIT FEEDBACK ───────────────────────────────── */
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
      // Validar campos antes de enviar
      if (!this.checkValidity()) {
        return; // Deja que el navegador muestre los mensajes de validación
      }

      const btn = this.querySelector('.form-submit');
      if (!btn) return;

      // Cambiar el botón mientras se envía
      btn.textContent = 'Enviando...';
      btn.disabled = true;
      
      // El formulario se enviará automáticamente a la thank-you-page
    });
  });


  /* ── 3. STICKY NAV SCROLL STATE ────────────────────────────── */
  const nav = document.querySelector('nav');
  if (nav) {
    const scrollHandler = () => {
      nav.classList.toggle('scrolled', window.scrollY > 60);
    };
    window.addEventListener('scroll', scrollHandler, { passive: true });
  }


  /* ── 4. MOBILE MENU TOGGLE ─────────────────────────────────── */
  const mobileBtn  = document.querySelector('.nav-mobile-btn');
  const navLinks   = document.querySelector('.nav-links');
  const navCta     = document.querySelector('.nav-cta');

  if (mobileBtn && navLinks) {
    mobileBtn.addEventListener('click', () => {
      const isOpen = navLinks.classList.toggle('mobile-open');
      mobileBtn.setAttribute('aria-expanded', isOpen);
      if (navCta) navCta.classList.toggle('mobile-open', isOpen);
    });

    // Close menu on link click
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('mobile-open');
        if (navCta) navCta.classList.remove('mobile-open');
        mobileBtn.setAttribute('aria-expanded', 'false');
      });
    });
  }


  /* ── 5. SMOOTH SCROLL FOR ANCHOR LINKS ────────────────────── */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      const navH = document.querySelector('nav')?.offsetHeight || 80;
      const top  = target.getBoundingClientRect().top + window.scrollY - navH - 16;
      window.scrollTo({ top, behavior: 'smooth' });
    });
  });


  /* ── 6. STICKY MOBILE CTA SHOW/HIDE ────────────────────────── */
  const stickyCta = document.querySelector('.sticky-cta');
  if (stickyCta) {
    window.addEventListener('scroll', () => {
      stickyCta.style.display = window.scrollY > window.innerHeight * 0.5 ? 'flex' : 'none';
    }, { passive: true });
  }

});
