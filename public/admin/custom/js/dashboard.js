function initInscriptionsChart() {

    const canvas = document.getElementById('inscription-chart-canvas')
    const dataLastWeek = JSON.parse(canvas.dataset.lastWeek)
    const dataCurrentWeek = JSON.parse(canvas.dataset.currentWeek)

    new Chart(canvas, {
        data: {
            labels: ['LUN', 'MAR', 'MERC', 'JEU', 'VEN', 'SAM', 'DIM'],
            datasets: [{
                type: 'line',
                data: dataCurrentWeek,
                backgroundColor: 'transparent',
                borderColor: '#007bff',
                pointBorderColor: '#007bff',
                pointBackgroundColor: '#007bff',
                fill: false
            },
            {
                type: 'line',
                data: dataLastWeek,
                backgroundColor: 'tansparent',
                borderColor: '#ced4da',
                pointBorderColor: '#ced4da',
                pointBackgroundColor: '#ced4da',
                fill: false
            }]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: 'index',
                intersect: true
            },
            hover: {
                mode: 'index',
                intersect: true
            },
            legend: {
                display: false
            },
            scales: {
                yAxes: [{
                    gridLines: {
                        display: true,
                        lineWidth: '4px',
                        color: 'rgba(0, 0, 0, .2)',
                        zeroLineColor: 'transparent'
                    },
                    ticks: {
                        beginAtZero: true,
                        suggestedMax: 20,
                        fontColor: '#495057',
                        fontStyle: 'bold'
                    }
                }],
                xAxes: [{
                    display: true,
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        fontColor: '#495057',
                        fontStyle: 'bold'
                    }
                }]
            }
        }
    })
}

function initReponsesChart() {
    const canvas = document.getElementById('reponse-chart-canvas')
    const dataLastWeek = JSON.parse(canvas.dataset.lastWeek)
    const dataCurrentWeek = JSON.parse(canvas.dataset.currentWeek)
    
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['LUN', 'MAR', 'MERC', 'JEU', 'VEN', 'SAM', 'DIM'],
            datasets: [
                {
                    backgroundColor: '#007bff',
                    borderColor: '#007bff',
                    data: dataCurrentWeek
                },
                {
                    backgroundColor: '#ced4da',
                    borderColor: '#ced4da',
                    data: dataLastWeek
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: 'index',
                intersect: true
            },
            hover: {
                mode: 'index',
                intersect: true
            },
            legend: {
                display: false
            },
            scales: {
                yAxes: [{
                    gridLines: {
                        display: true,
                        lineWidth: '4px',
                        color: 'rgba(0, 0, 0, .2)',
                        zeroLineColor: 'transparent'
                    },
                    ticks: {
                        beginAtZero: true,
                        suggestedMax: 20,
                        fontColor: '#495057',
                        fontStyle: 'bold',
                    }
                }],
                xAxes: [{
                    display: true,
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        fontColor: '#495057',
                        fontStyle: 'bold'
                    }
                }]
            }
        }
    })
}

document.addEventListener('DOMContentLoaded', () => {
    initInscriptionsChart()
    initReponsesChart()
})