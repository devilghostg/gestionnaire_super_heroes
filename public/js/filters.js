document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('.filters-section');
    const heroGrid = document.querySelector('.hero-grid');
    const applyFiltersBtn = document.querySelector('#apply-filters');

    // Fonction pour mettre à jour la grille des héros
    async function updateHeroGrid() {
        const name = document.querySelector('#filter-name').value;
        const team = document.querySelector('#filter-team').value;
        const status = document.querySelector('#filter-status').value;
        const power = document.querySelector('#filter-power').value;

        // Construire l'URL avec les paramètres de filtrage
        const params = new URLSearchParams();
        if (name) params.append('name', name);
        if (team) params.append('team', team);
        if (status) params.append('status', status);
        if (power) params.append('power', power);

        try {
            // Ajouter une classe pour l'animation de chargement
            heroGrid.classList.add('loading');

            const response = await fetch(`/superhero?${params.toString()}`);
            if (!response.ok) throw new Error('Erreur réseau');

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGrid = doc.querySelector('.hero-grid');

            // Mettre à jour la grille avec les nouveaux résultats
            heroGrid.innerHTML = newGrid.innerHTML;
        } catch (error) {
            console.error('Erreur lors de la mise à jour:', error);
            // Afficher un message d'erreur à l'utilisateur
            const toast = document.createElement('div');
            toast.className = 'toast error';
            toast.textContent = 'Une erreur est survenue lors de la mise à jour des résultats.';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        } finally {
            // Retirer la classe de chargement
            heroGrid.classList.remove('loading');
        }
    }

    // Gestionnaire d'événement pour le bouton "Appliquer les filtres"
    applyFiltersBtn.addEventListener('click', function(e) {
        e.preventDefault();
        updateHeroGrid();
    });

    // Gestionnaire d'événement pour la touche Entrée dans les champs de texte
    document.querySelector('#filter-name').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            updateHeroGrid();
        }
    });
});
