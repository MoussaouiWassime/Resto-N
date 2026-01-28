// public/js/chart-handler.js

/**
 * Fonction principale pour initialiser un graphique
 * @param {string} canvasId - L'ID de l'élément canvas
 */
export function initChart(canvasId) {
    const ctx = document.getElementById(canvasId);

    // 1. Vérifications de sécurité
    if (!ctx) return; // Le canvas n'existe pas sur cette page

    // On attend que la librairie Chart.js (CDN) soit chargée
    if (typeof Chart === 'undefined') {
        // On réessaie dans 100ms si la librairie n'est pas encore là
        setTimeout(() => initChart(canvasId), 100);
        return;
    }

    // 2. Récupération des données depuis les attributs HTML (data-*)
    // dataset.dates correspond à data-dates="..."
    const dates = JSON.parse(ctx.dataset.dates);
    const values = JSON.parse(ctx.dataset.values);
    const caption = ctx.dataset.caption;
    const color = ctx.dataset.color;
    const bgColor = ctx.dataset.bgcolor;

    // 3. Nettoyage (Si un graphique existe déjà, on le détruit pour éviter les bugs)
    const existingChart = Chart.getChart(ctx);
    if (existingChart) {
        existingChart.destroy();
    }

    // 4. Création du graphique
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: caption,
                data: values,
                backgroundColor: bgColor,
                borderColor: color,
                borderWidth: 2,
                borderRadius: 4,
                maxBarThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}
