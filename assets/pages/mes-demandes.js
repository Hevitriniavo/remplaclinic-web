import axios from 'axios'
import initPageCommon, { getCleanUrl, addDataTableLimitOptionsEvent } from './page-common.js'
import RemplaModal from '../js/rempla-modal.js'
import RemplaTabNavigation from '../js/rempla-tab-navigation.js'
import RemplaDatatable from '../js/rempla-datatable.js'

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
    let requestId = null
    const tabNavigation = new RemplaTabNavigation('.tab-btn', '.tab-content', {
        beforeChangeAsync: async (btn, index) => {
            if (!requestUserListeLoaded && index === 1) {
                // load all related user
                const initRequestOptions = {
                    url: getCleanUrl(btn.dataset.listeRemplacantsUrl, requestId),
                }

                const remplacantSelectionTable = new RemplaDatatable({
                    selector: 'tbl-request-replacements',
                    requestData: (requestOptions = {}) => {
            
                        const apiUrl = requestOptions.url ? requestOptions.url : initRequestOptions.url
                        const params = requestOptions.params ? requestOptions.params : {}
            
                        return axios.get(apiUrl, {
                            params
                        })
                            .then((res) => res.data)
                            .catch(() => ({}))
                    },
                    renderRow: (row) => {
                        return `
                            <td class="px-4 py-2">${row.user.name}</td>
                            <td class="px-4 py-2">${row.user.current_speciality}</td>
                            <td class="px-4 py-2 max-w-[60%]">${ row.user.sous_specialite }</td>
                            <td class="px-4 py-2">${ row.statut }</td>
                        `
                    }
                })
            
                remplacantSelectionTable.draw(initRequestOptions)

                requestUserListeLoaded = true
            }

            return true
        }
    })

    // detail demande modal
    new RemplaModal('modal-detail-demande', {
        beforeOpenAsync: async (e, activator) => {
            // save current request
            requestId = activator.dataset.id

            // set active tab to info
            requestUserListeLoaded = false
            tabNavigation.setActiveIndex(0)

            // clear liste remplacants table
            document.querySelector('#tbl-request-replacements tbody').innerHTML = ''
            document.querySelector('.tbl-request-replacements-pagination-container').innerHTML = ''

            // get request detail
            const url = getCleanUrl(document.getElementById('modal-detail-demande').dataset.detailUrl, requestId)
            const response = await axios.get(url)

            if (response.data) {
                for (const key in response.data) {
                    if (!Object.hasOwn(response.data, key)) continue
                    
                    const el = document.querySelector(`.demande-detail-${key}`)
                    if (el) {
                        const contentProp = key === 'commentaire' ? 'innerHTML' : 'innerText'
                        el[contentProp] = response.data[key]
                    }
                }
            }

            return true
        }
    })
})