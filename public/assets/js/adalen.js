// Adalen Custom JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Animation des statistiques
    const statNumbers = document.querySelectorAll('.stat-number');
    
    const animateValue = (element, start, end, duration, suffix = '') => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = value + suffix;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    };
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                entry.target.classList.add('animated');
                const value = parseInt(entry.target.getAttribute('data-value'));
                const suffix = entry.target.getAttribute('data-suffix') || '';
                animateValue(entry.target, 0, value, 2000, suffix);
            }
        });
    }, observerOptions);
    
    statNumbers.forEach(stat => {
        observer.observe(stat);
    });
    
    // Smooth scroll pour les liens d'ancrage
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Animation au scroll pour les éléments avec classes d'animation
    const animatedElements = document.querySelectorAll('.fade-in, .slide-up, .fade-in-left, .fade-in-right, .scale-in');
    
    const scrollObserverOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Délai progressif pour un effet en cascade
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, index * 100);
                scrollObserver.unobserve(entry.target);
            }
        });
    }, scrollObserverOptions);
    
    animatedElements.forEach(element => {
        scrollObserver.observe(element);
    });
    
    // Animation spécifique pour les images
    const images = document.querySelectorAll('.coop-lamb-image, .coop-vote-image, .coop-animal-image-wrapper img, .coop-garden-image-wrapper img, .coop-seeds-image-wrapper img, .coop-child-image-wrapper img, .activity-image');
    
    const imageObserverOptions = {
        threshold: 0.2,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'scale(0.95)';
                entry.target.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'scale(1)';
                }, 100);
                
                imageObserver.unobserve(entry.target);
            }
        });
    }, imageObserverOptions);
    
    images.forEach(image => {
        imageObserver.observe(image);
    });
});


