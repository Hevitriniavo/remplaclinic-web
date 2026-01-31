const { createApp, ref, onMounted, computed } = Vue

/******************************************/
/**** TABLEAU DE BORS DES UTILISATEURS ****/
/******************************************/
const VueDashboardUserCountItem = {
    props: [
        'boxData',
    ],
    template: `
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <img class="info-box-icon" :src="boxData.url" :alt="boxData.title">

                <div class="info-box-content">
                    <span class="info-box-text text-uppercase">$% boxData.title %$</span>
                    <div class="info-box-number">
                        $% boxData.count %$
                    </div>
                </div>
            </div>
        </div>
    `,
}

const VueDashboardUserCount = {
    props: [
        'boxItems'
    ],
    template: `
        <vue-dashboard-user-count-item
            v-for="boxData in boxItems"
            :key="boxData.title"
            :box-data="boxData"
        ></vue-dashboard-user-count-item>
    `
}

/**************************************/
/**** TABLEAU DE BORS DES DEMANDES ****/
/**************************************/
const VueDashboardRequestCountItem = {
    props: [
        'boxData',
    ],
    template: `
        <div class="col-lg-3 col-6">
            <div class="small-box" :class="boxData.bg">
                <div class="inner">
                    <h3>$% boxData.total %$ <sup v-if="boxData.percentage" style="font-size: 20px">%</sup></h3>
                    <p>$% boxData.title %$</p>

                    <p v-if="boxData.items && boxData.items.length > 0">
                        <template v-for="item in boxData.items" :key="item.label">
                            $% item.label %$: <span class="badge mr-2" :class="item.badgeClass">$% item.count %$</span>
                        </template>
                    </p>
                </div>
                <div class="icon">
                    <i :class="boxData.icon"></i>
                </div>
                <a v-if="boxData.href" :href="boxData.href" class="small-box-footer">Voir <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    `,
}

const VueDashboardRequestCount = {
    props: [
        'boxItems'
    ],
    template: `
        <vue-dashboard-request-count-item
            v-for="boxData in boxItems"
            :key="boxData.title"
            :box-data="boxData"
        ></vue-dashboard-request-count-item>
    `,
}

/***********************************/
/**** TABLEAU DE BORD GRAPHIQUE ****/
/***********************************/
const VueDashboardChart = {
    props: [
        'chartData',
    ],
    template: `
        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title">$% chartData.title %$</h3>
                    <a :href="chartData.viewHref">Voir plus</a>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex">
                    <p class="d-flex flex-column">
                        <span class="text-bold text-lg">$% chartData.total %$</span>
                        <span>$% chartData.title2 %$</span>
                    </p>
                    <p class="ml-auto d-flex flex-column text-right">
                        <span :class="[chartData.up ? 'text-success' : 'text-danger']">
                            <i class="fas" :class="[chartData.up ? 'fa-arrow-up' : 'fa-arrow-down']"></i> $% chartData.percentage %$%
                        </span>
                        <span class="text-muted">Cette semaine</span>
                    </p>
                </div>

                <slot></slot>

                <div class="d-flex flex-row justify-content-end">
                    <span class="mr-2">
                        <i class="fas fa-square text-primary"></i> Cette semaine
                    </span>

                    <span>
                        <i class="fas fa-square text-gray"></i> La semaine derniere
                    </span>
                </div>
            </div>
        </div>
    `,
}

const VueDashboardChartInscription = {
    props: [
        'dataCurrentWeek',
        'dataLastWeek',
        'chartData'
    ],
    template: `
        <vue-dashboard-chart
            :chart-data="chartData"
        >
            <div class="position-relative mb-4">
                <canvas ref="inscriptionChartCanvas" height="200"></canvas>
            </div>
        </vue-dashboard-chart
    `,
    setup(props) {
        const inscriptionChartCanvas = ref(null);

        function initInscriptionsChart() {
            new Chart(inscriptionChartCanvas.value, {
                data: {
                    labels: ['LUN', 'MAR', 'MERC', 'JEU', 'VEN', 'SAM', 'DIM'],
                    datasets: [{
                        type: 'line',
                        data: props.dataCurrentWeek,
                        backgroundColor: 'transparent',
                        borderColor: '#007bff',
                        pointBorderColor: '#007bff',
                        pointBackgroundColor: '#007bff',
                        fill: false
                    },
                    {
                        type: 'line',
                        data: props.dataLastWeek,
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

        onMounted(() => {
            initInscriptionsChart()
        })

        return {
            inscriptionChartCanvas,
        }
    }
}

const VueDashboardChartReponse = {
    props: [
        'dataCurrentWeek',
        'dataLastWeek',
        'chartData'
    ],
    template: `
        <vue-dashboard-chart
            :chart-data="chartData"
        >
            <div class="position-relative mb-4">
                <canvas ref="responseChartCanvas" height="200"></canvas>
            </div>
        </vue-dashboard-chart
    `,
    setup(props) {
        const responseChartCanvas = ref(null);

        function initReponsesChart() {
            new Chart(responseChartCanvas.value, {
                type: 'bar',
                data: {
                    labels: ['LUN', 'MAR', 'MERC', 'JEU', 'VEN', 'SAM', 'DIM'],
                    datasets: [
                        {
                            backgroundColor: '#007bff',
                            borderColor: '#007bff',
                            data: props.dataCurrentWeek
                        },
                        {
                            backgroundColor: '#ced4da',
                            borderColor: '#ced4da',
                            data: props.dataLastWeek
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

        onMounted(() => {
            initReponsesChart()
        })

        return {
            responseChartCanvas,
        }
    }
}

const VueDashboardApp = {
    template: '<slot :is-loaded="dashboardDataLoaded" :data="dashboardData"></slot>',
    props: [
        'iconRemplacant',
        'iconDoctor',
        'iconClinic',
        'iconDirector',
        'urlReplacement',
        'urlInstallation',
        'urlUser',
        'apiUrl',
    ],
    setup(props) {
        const dashboardDataTemplate = {
            users: {
                remplacant: 0,
                doctor: 0,
                director: 0,
                clinic: 0,
            },
            requests: {
                remplacement: {
                    total: 0,
                    status: [0, 0, 0]
                },
                installation: {
                    total: 0,
                    status: [0, 0, 0]
                },
                users: {
                    total: 0,
                    status: [0, 0]
                },
                reponses: {
                    percentage: 0,
                    status: [0, 0, 0]
                },
            },
            inscription: {
                currentWeek: [0, 0, 0, 0, 0, 0, 0],
                lastWeek: [0, 0, 0, 0, 0, 0, 0],
            },
            reponse: {
                currentWeek: [0, 0, 0, 0, 0, 0, 0],
                lastWeek: [0, 0, 0, 0, 0, 0, 0],
            }
        }

        const dashboardDataLoaded = ref(false)
        const responseData = ref(JSON.parse(JSON.stringify(dashboardDataTemplate)))

        const dashboardData = computed(() => {
            return {
                users: [
                    {
                        title: 'Remplaçant',
                        count: responseData.value.users.remplacant,
                        url: props.iconRemplacant
                    },
                    {
                        title: 'Cabinet médicale',
                        count: responseData.value.users.doctor,
                        url: props.iconDoctor
                    },
                    {
                        title: 'Directeur de clinique',
                        count: responseData.value.users.director,
                        url: props.iconDirector
                    },
                    {
                        title: 'Clinique',
                        count: responseData.value.users.clinic,
                        url: props.iconClinic
                    }
                ],
                requests: [
                    {
                        title: 'Demande de remplacement',
                        total: responseData.value.requests.remplacement.total,
                        href: props.urlReplacement,
                        icon: 'fas fa-handshake',
                        bg: 'bg-secondary',
                        items: [
                            {
                                label: 'A valider',
                                count: responseData.value.requests.remplacement.status[0],
                                badgeClass: 'badge-info',
                            },
                            {
                                label: 'En cours',
                                count: responseData.value.requests.remplacement.status[1],
                                badgeClass: 'badge-success',
                            },
                            {
                                label: 'Archive',
                                count: responseData.value.requests.remplacement.status[2],
                                badgeClass: 'badge-danger',
                            }
                        ]
                    },
                    {
                        title: 'Proposition d\'installaction',
                        total: responseData.value.requests.installation.total,
                        href: props.urlInstallation,
                        icon: 'fab fa-instalod',
                        bg: 'bg-info',
                        items: [
                            {
                                label: 'A valider',
                                count: responseData.value.requests.installation.status[0],
                                badgeClass: 'badge-info',
                            },
                            {
                                label: 'En cours',
                                count: responseData.value.requests.installation.status[1],
                                badgeClass: 'badge-success',
                            },
                            {
                                label: 'Archive',
                                count: responseData.value.requests.installation.status[2],
                                badgeClass: 'badge-danger',
                            }
                        ]
                    },
                    {
                        title: 'Utilisateurs inscrits',
                        total: responseData.value.requests.users.total,
                        href: props.urlUser,
                        icon: 'fas fa-user-nurse',
                        bg: 'bg-warning',
                        items: [
                            {
                                label: 'Actif',
                                count: responseData.value.requests.users.status[0],
                                badgeClass: 'badge-info',
                            },
                            {
                                label: 'Inactif',
                                count: responseData.value.requests.users.status[1],
                                badgeClass: 'badge-danger',
                            }
                        ]
                    },
                    {
                        title: 'Candidatures du mois',
                        total: responseData.value.requests.reponses.percentage,
                        percentage: true,
                        icon: 'fas fa-quote-right',
                        bg: 'bg-danger',
                        href: '#',
                        items: [
                            {
                                label: 'Accepte',
                                count: responseData.value.requests.reponses.status[0],
                                badgeClass: 'badge-success',
                            },
                            {
                                label: 'Plus d\info',
                                count: responseData.value.requests.reponses.status[1],
                                badgeClass: 'badge-info',
                            },
                            {
                                label: 'Exclu',
                                count: responseData.value.requests.reponses.status[2],
                                badgeClass: 'badge-danger',
                            }
                        ]
                    },
                ],
                inscription: {
                    currentWeek: responseData.value.inscription.currentWeek,
                    lastWeek: responseData.value.inscription.lastWeek,
                    chartData: {
                        itle: 'Inscriptions',
                        title2: 'Nouveaux inscrits',
                        viewHref: '#',
                        total: responseData.value.inscription.chartData.total,
                        up: responseData.value.inscription.chartData.up,
                        percentage: responseData.value.inscription.chartData.percentage,
                    }
                },
                response: {
                    currentWeek: responseData.value.reponse.currentWeek,
                    lastWeek: responseData.value.reponse.lastWeek,
                    chartData: {
                        itle: 'Candidatures',
                        title2: 'Nouvelles candidatures',
                        viewHref: '#',
                        total: responseData.value.reponse.chartData.total,
                        up: responseData.value.reponse.chartData.up,
                        percentage: responseData.value.reponse.chartData.percentage,
                    }
                },
            }
        })

        function getDashboardData() {
            axios.get(props.apiUrl)
                .then((res) => {
                    responseData.value = res.data
                    dashboardDataLoaded.value = true
                })
                .catch(() => {
                    dashboardDataLoaded.value = true
                })
        }

        onMounted(() => {
            getDashboardData()
        })

        return {
            dashboardData,
            dashboardDataLoaded,
        }
    }
}


const app = createApp({})

app.component('VueDashboardUserCountItem', VueDashboardUserCountItem)
app.component('VueDashboardUserCount', VueDashboardUserCount)
app.component('VueDashboardRequestCountItem', VueDashboardRequestCountItem)
app.component('VueDashboardRequestCount', VueDashboardRequestCount)
app.component('VueDashboardChart', VueDashboardChart)
app.component('VueDashboardChartInscription', VueDashboardChartInscription)
app.component('VueDashboardChartReponse', VueDashboardChartReponse)
app.component('VueDashboardApp', VueDashboardApp)

app.config.compilerOptions.delimiters = ["$%", "%$"]
app.mount("#root")