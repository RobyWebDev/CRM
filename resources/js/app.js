

import Alpine from 'alpinejs';
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip } from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

window.Alpine = Alpine;

// Pipeline tölcsér-diagram — vízszintes oszlopdiagram, mert a Chart.js alap csomagja nem
// tartalmaz "funnel" típust, de lépésenként csökkenő nyitott üzletszám vízszintes oszlopokkal
// vizuálisan ugyanazt a tölcsér-hatást adja, extra plugin nélkül (lásd deals/index.blade.php).
window.renderFunnelChart = function (canvas, labels, counts) {
    const style = getComputedStyle(document.documentElement);
    const accent = style.getPropertyValue('--accent-primary').trim();
    const textColor = style.getPropertyValue('--text-secondary').trim();
    const gridColor = style.getPropertyValue('--border-subtle').trim();

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: counts,
                backgroundColor: accent,
                borderRadius: 4,
                maxBarThickness: 32,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: textColor },
                    grid: { color: gridColor },
                },
                y: {
                    ticks: { color: textColor },
                    grid: { display: false },
                },
            },
        },
    });
};

Alpine.start();
