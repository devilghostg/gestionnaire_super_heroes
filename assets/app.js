/*
 * Welcome to your app's main JavaScript file!
 */

// Import Bootstrap
import 'bootstrap';

// Import our custom CSS
import './styles/space-theme.css';

// Import our custom JS
import './js/space-theme.js';

// Start the app
console.log('Welcome to Super Heroes HQ!');

// Fonction pour gérer la progression de la mission
function initializeMissionProgress() {
    const progressBar = document.getElementById('missionProgress');
    const timerElement = document.getElementById('missionTimer');
    
    if (!progressBar || !timerElement) return;

    // Récupérer les données de la mission depuis l'élément data
    const timeLimit = parseInt(progressBar.dataset.timeLimit) || 300; // 5 minutes par défaut
    const startTime = new Date().getTime();
    let isCompleted = false;

    function updateProgress() {
        if (isCompleted) return;

        const currentTime = new Date().getTime();
        const elapsedTime = (currentTime - startTime) / 1000; // en secondes
        const progress = Math.min((elapsedTime / timeLimit) * 100, 100);

        // Mettre à jour la barre de progression
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);

        // Mettre à jour le timer
        const timeLeft = Math.max(timeLimit - elapsedTime, 0);
        const minutes = Math.floor(timeLeft / 60);
        const seconds = Math.floor(timeLeft % 60);
        timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

        // Ajouter la classe urgent si moins de 30 secondes
        if (timeLeft <= 30) {
            timerElement.classList.add('urgent');
        }

        // Si la mission est terminée
        if (progress >= 100 && !isCompleted) {
            isCompleted = true;
            progressBar.classList.remove('progress-bar-animated');
            // Vous pouvez ajouter ici une logique pour terminer la mission
        }
    }

    // Mettre à jour toutes les secondes
    const progressInterval = setInterval(updateProgress, 1000);
    updateProgress(); // Première mise à jour immédiate

    // Nettoyer l'intervalle si l'élément est supprimé
    const observer = new MutationObserver((mutations, obs) => {
        if (!document.contains(progressBar)) {
            clearInterval(progressInterval);
            obs.disconnect();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

// Fonction pour gérer les timers des missions annulées
function initializeMissionTimers() {
    console.log('Initializing mission timers...');
    const missionTimers = document.querySelectorAll('.mission-timer');
    console.log('Found timers:', missionTimers.length);

    missionTimers.forEach(timerElement => {
        console.log('Processing timer element:', timerElement.dataset);
        const cancelledAt = new Date(timerElement.dataset.cancelledAt);
        const difficulty = parseInt(timerElement.dataset.difficulty);
        const deleteDelay = Math.min(2 + (difficulty - 1), 5) * 60; // En secondes
        const deleteTime = new Date(cancelledAt.getTime() + (deleteDelay * 1000));

        console.log('Timer details:', {
            cancelledAt,
            difficulty,
            deleteDelay,
            deleteTime
        });

        function updateTimer() {
            const now = new Date();
            const timeLeft = deleteTime - now;

            console.log('Updating timer, time left:', timeLeft);

            if (timeLeft <= 0) {
                timerElement.innerHTML = '<span class="timer urgent">Suppression imminente...</span>';
                return;
            }

            const minutes = Math.floor(timeLeft / 60000);
            const seconds = Math.floor((timeLeft % 60000) / 1000);
            const isUrgent = timeLeft <= 30000; // 30 secondes

            const timerHtml = `
                <span class="timer${isUrgent ? ' urgent' : ''}">
                    ${minutes}:${seconds.toString().padStart(2, '0')}
                </span>`;
            timerElement.innerHTML = timerHtml;
        }

        // Mettre à jour immédiatement et toutes les secondes
        updateTimer();
        const intervalId = setInterval(updateTimer, 1000);
        
        // Nettoyer l'intervalle si l'élément est supprimé
        const observer = new MutationObserver((mutations, obs) => {
            if (!document.contains(timerElement)) {
                clearInterval(intervalId);
                obs.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
}

// Initialiser les timers au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded');
    initializeMissionTimers();
    initializeMissionProgress();
});

// Backup en cas de chargement asynchrone
if (document.readyState === 'complete') {
    console.log('Document already complete');
    initializeMissionTimers();
    initializeMissionProgress();
}
