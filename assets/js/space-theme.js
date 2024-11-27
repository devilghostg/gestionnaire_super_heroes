// Space Theme JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Create stars
    createStars();
    
    // Initialize tooltips
    initTooltips();
    
    // Add smooth scrolling
    addSmoothScrolling();
    
    // Initialize progress bars animation
    initProgressBars();
});

function createStars() {
    const starsContainer = document.createElement('div');
    starsContainer.className = 'stars';
    document.body.appendChild(starsContainer);

    // Create planets
    const planet1 = document.createElement('div');
    planet1.className = 'planet planet-1';
    document.body.appendChild(planet1);

    const planet2 = document.createElement('div');
    planet2.className = 'planet planet-2';
    document.body.appendChild(planet2);

    // Create stars
    for (let i = 0; i < 200; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        
        // Random position
        star.style.left = `${Math.random() * 100}%`;
        star.style.top = `${Math.random() * 100}%`;
        
        // Random size
        const size = Math.random() * 2;
        star.style.width = `${size}px`;
        star.style.height = `${size}px`;
        
        // Random animation duration
        star.style.setProperty('--duration', `${2 + Math.random() * 4}s`);
        
        starsContainer.appendChild(star);
    }
}

function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function addSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
}

function initProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const value = bar.getAttribute('aria-valuenow');
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = value + '%';
        }, 100);
    });
}

// Mission progress animation
function updateMissionProgress(missionId, progress) {
    const progressBar = document.querySelector(`#mission-${missionId} .progress-bar`);
    if (progressBar) {
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
        
        // Add animation class
        progressBar.classList.add('progress-animated');
        
        // Check for completion
        if (progress >= 100) {
            const missionCard = document.querySelector(`#mission-${missionId}`);
            missionCard.classList.add('mission-completed');
            showCompletionMessage(missionId);
        }
    }
}

function showCompletionMessage(missionId) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show mt-3';
    alert.innerHTML = `
        <strong>Mission Accomplie!</strong> La mission a été complétée avec succès.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const missionCard = document.querySelector(`#mission-${missionId}`);
    missionCard.appendChild(alert);
}
