// public/js/stars.js
document.addEventListener('DOMContentLoaded', function () {
    const starField = document.querySelector('.stars');
    
    for (let i = 0; i < 100; i++) {
        const star = document.createElement('div');
        star.classList.add('star');
        
        // Position aléatoire des étoiles
        star.style.left = `${Math.random() * 100}vw`;
        star.style.top = `${Math.random() * 100}vh`;
        
        // Taille aléatoire des étoiles
        const size = Math.random() * 3 + 1;
        star.style.width = `${size}px`;
        star.style.height = `${size}px`;
        
        // Vitesse aléatoire de scintillement
        star.style.animationDuration = `${Math.random() * 3 + 1}s`;
        
        starField.appendChild(star);
    }
});