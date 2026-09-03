document.addEventListener('DOMContentLoaded', function() {
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

    const stickyCta = document.querySelector('.sticky-form-cta');
    const formSection = document.querySelector('.form-section');

    function handleStickyCtaVisibility() {
        if (!stickyCta || !formSection) return;

        const scrolled = window.scrollY > 600;
        const formInView = formSection.getBoundingClientRect().top < window.innerHeight;

        if (scrolled && !formInView) {
            stickyCta.classList.add('visible');
        } else {
            stickyCta.classList.remove('visible');
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }

    if (stickyCta && formSection) {
        window.addEventListener('scroll', debounce(handleStickyCtaVisibility, 10));
        handleStickyCtaVisibility();
    }

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            if (!this.checkValidity()) {
                return;
            }

            const getFieldValue = (name) => {
                const field = this.querySelector(`[name="${name}"]`);
                return field ? field.value.trim() : '';
            };

            const setHiddenField = (name, value) => {
                let field = this.querySelector(`input[type="hidden"][name="${name}"]`);
                if (!field) {
                    field = document.createElement('input');
                    field.type = 'hidden';
                    field.name = name;
                    this.appendChild(field);
                }
                field.value = value;
            };

            const originValue = getFieldValue('origin') || '15';
            const empleadosValue = getFieldValue('num-empleados');
            const tematicaValue = getFieldValue('tematica');
            const cargoValue = getFieldValue('cargo');
            const messageField = this.querySelector('[name="your-message"]');
            const originalMessage = messageField ? messageField.value.trim() : '';

            const leadDetails = [
                `No. de empleados: ${empleadosValue}`,
                `Curso(s) de interes: ${tematicaValue}`,
                cargoValue ? `Cargo: ${cargoValue}` : '',
                originalMessage ? `Mensaje: ${originalMessage}` : ''
            ].filter(Boolean).join('\n');

            if (messageField) {
                messageField.value = leadDetails;
            }

            setHiddenField('num_empleados', empleadosValue);
            setHiddenField('empleados', empleadosValue);
            setHiddenField('curso_interes', tematicaValue);
            setHiddenField('curso-de-interes', tematicaValue);
            setHiddenField('curso', tematicaValue);

            this.action = `https://thecorner.es/thank-you-page/?origin=${originValue}`;

            const btn = this.querySelector('.form-submit');
            if (btn) {
                btn.textContent = 'Enviando...';
                btn.disabled = true;
            }

            console.log('Formulario enviado con:', {
                action: this.action,
                origin: originValue,
                empleados: empleadosValue,
                tematica: tematicaValue
            });
        });
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();

            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);

            if (target) {
                const nav = document.querySelector('nav');
                const navHeight = nav ? nav.offsetHeight : 0;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

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

    document.querySelectorAll('.curso-card, .why-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });
});
