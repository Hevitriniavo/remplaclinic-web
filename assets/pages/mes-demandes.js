import initPageCommon, { addDataTableLimitOptionsEvent } from './page-common.js'
import RemplaModal from '../js/rempla-modal.js'
import RemplaTabNavigation from '../js/rempla-tab-navigation.js'

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

    // detail demande navigation tab
    let requestUserListeLoaded = false
    const tabNavigation = new RemplaTabNavigation('.tab-btn', '.tab-content', {
        beforeChangeAsync: async (btn, index) => {
            if (!requestUserListeLoaded && index === 1) {
                // @TODO: load all related user
                console.log('LOAD USER LIST HERE')
                requestUserListeLoaded = true
            }

            return true
        }
    })

    // detail demande modal
    new RemplaModal('modal-detail-demande', {
        beforeOpenAsync: async () => {
            // set active tab to info
            requestUserListeLoaded = false
            tabNavigation.setActiveIndex(0)

            // @TODO: get request detail
            console.log('GET REQUEST DETAIL HERE')

            return true
        }
    })
})