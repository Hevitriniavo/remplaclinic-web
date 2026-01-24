import initPageCommon, { addDataTableLimitOptionsEvent } from './page-common.js'

document.addEventListener('DOMContentLoaded', () => {
    initPageCommon()

    // filtre
    const selectStatut = document.getElementById('mes-demandes-filtre-statut')
    if (selectStatut) {
        selectStatut.addEventListener('change', (e) => {
            const href = window.location.href.split('?')
            const url = new URLSearchParams(href.length > 1 ? href[1] : '')

            url.set('status', e.target.value)

            window.location.href = href[0] + '?' + url.toString()
        })
    }

    // pagination
    addDataTableLimitOptionsEvent('#tbl-mes-demandes-pager')
})