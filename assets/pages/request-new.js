import { Datepicker } from 'vanillajs-datepicker'
import 'vanillajs-datepicker/dist/css/datepicker-bulma.min.css'

import axios from '../js/axios-instance.js'
import { default as initPageCommon, getCleanUrl } from './page-common.js'
import { default as RemplaFormValidation, updateBtnSubmitStatus } from '../js/rempla-form-validation.js'
import RemplaModal from '../js/rempla-modal.js'
import RemplaDatatable from '../js/rempla-datatable.js'
import showToast from '../js/rempla-toaster.js'

function initSelectRemplacantModal(modal, modalDetail, initRequestOptions, requestDetail = {}) {
    const remplacantsSelection = {
        all: false,
        ids: new Set(),
        exclus: new Set(),
        totalRecords: 0,
    }

    const getSelectionCount = () => {
        let selectedCount = remplacantsSelection.ids.size

        const totalRecords = remplacantsSelection.totalRecords

        if (remplacantsSelection.all) {
            selectedCount = totalRecords - remplacantsSelection.exclus.size
        }

        return selectedCount
    }

    const selectionCounterElement = document.getElementById('tbl-search-replacements-selection-count')
    const updateSelectionCounter = () => {
        selectionCounterElement.innerText = getSelectionCount()
    }

    const remplacantSelectionTable = new RemplaDatatable({
        selector: 'tbl-search-replacements',
        requestData: (requestOptions = {}) => {

            const apiUrl = requestOptions.url ? requestOptions.url : initRequestOptions.url
            const params = requestOptions.params ? requestOptions.params : {}

            const queryQPosition = apiUrl.indexOf('?')

            const paramsUrl = queryQPosition < 0 ? '' : apiUrl.substr(queryQPosition + 1)
            const searchParams = new URLSearchParams(paramsUrl)
            searchParams.set('specialite', initRequestOptions.params.specialite)
            searchParams.set('region', initRequestOptions.params.region)

            for (const key in params) {
                if (!Object.hasOwn(params, key)) continue
                
                searchParams.set(key, params[key])
            }

            const url = queryQPosition < 0 ? apiUrl : apiUrl.substr(0, queryQPosition)

            return axios.get(`${url}?${searchParams.toString()}`)
                .then((res) => res.data)
                .catch(() => ({}))
        },
        renderRow: (row) => {
            const estSelection = !remplacantsSelection.all && remplacantsSelection.ids.has(row.id)
            const estNonExclu = remplacantsSelection.all && !remplacantsSelection.exclus.has(row.id)

            const checkedAttr = estSelection || estNonExclu ? ' checked' : ''

            return `
                <td class="px-4 py-2">
                    <input type="checkbox" class="user-replacement-checkbox" id="user-replacement-${ row.id }" name="remplacants[]" value="${ row.id }"${checkedAttr}>
                </td>
                <td class="px-4 py-2 col-prenom">
                    <span class="blue-span cursor-pointer" data-id="${ row.id }">${ row.prenom }</span>
                </td>
                <td class="px-4 py-2">${ row.statut_name }</td>
                <td class="px-4 py-2 max-w-[60%]">${ row.sous_specialite }</td>
            `
        },
        afterDrawn: (data, datatable) => {
            // set total records text
            document.getElementById('tbl-search-replacements-total-records').innerText = data.result.totalRecords
            remplacantsSelection.totalRecords = data.result.totalRecords

            // open the modal
            modal.open()

            // add checkbox selection listener - for all
            if (datatable.drawn === 1) {
                const selectionTout = document.getElementById('user-replacement-all')
                selectionTout.addEventListener('change', (e) => {
                    remplacantsSelection.all = e.target.checked
                    remplacantsSelection.ids = new Set()
                    remplacantsSelection.exclus = new Set()

                    updateSelectionCounter()

                    document.querySelectorAll('.user-replacement-checkbox').forEach(selectionCheckbox => {
                        if (e.target.checked) {
                            selectionCheckbox.checked = true
                        } else {
                            selectionCheckbox.checked = false
                        }
                    })
                })
            }

            // @TODO: move these two listener into render column
            // add checkbox selection listener - for each row
            const selectionCheckboxs = document.querySelectorAll('.user-replacement-checkbox')
            selectionCheckboxs.forEach(selectionCheckbox => {
                selectionCheckbox.addEventListener('change', (e) => {
                    const id = Number(e.target.value)

                    if (e.target.checked) {
                        remplacantsSelection.ids.add(id)
                        remplacantsSelection.exclus.delete(id)
                    } else {
                        remplacantsSelection.ids.delete(id)
                        remplacantsSelection.exclus.add(id)
                    }

                    updateSelectionCounter()
                })
            })

            // @TODO: move these two listener into render column
            // add view detail listener
            const prenomColumns = document.querySelectorAll('.col-prenom span')
            prenomColumns.forEach(prenomColumn => {
                prenomColumn.addEventListener('click', (e) => {

                    const id = e.target.getAttribute('data-id')
                    const detailUrl = getCleanUrl(data.params._detail_url, id)
                    axios.get(detailUrl)
                        .then(res => {
                            const userData = res.data

                            userData.request_speciality = requestDetail.specialite ? requestDetail.specialite : ''
                            userData.request_region = requestDetail.region ? requestDetail.region : ''

                            for (const key in userData) {
                                if (!Object.hasOwn(userData, key)) continue
                                
                                const el = document.querySelector(`.user-detail-${key}`)
                                if (el) {
                                    el.innerText = userData[key]
                                }
                            }
                            
                            modalDetail.open()
                        }).catch(() => {})
                })
            })
        }
    })

    remplacantSelectionTable.draw(initRequestOptions)

    // validate selection
    const btnValidateSelection = document.querySelector('.btn-validate-selection')
    btnValidateSelection.addEventListener('click', () => {
            if (getSelectionCount() <= 0) {
                showToast('Vous devez sélectionner au moins un remplaçant.', 'warning')
            } else {
                updateBtnSubmitStatus(btnValidateSelection, true)

                axios.post(getCleanUrl(btnValidateSelection.dataset.initResponseUrl, initRequestOptions.params.request_id), {
                    all: remplacantsSelection.all,
                    users: Array.from(remplacantsSelection.all ? remplacantsSelection.exclus : remplacantsSelection.ids),
                })
                    .then(() => {})
                    .catch(() => {})
                    .finally(() => {
                        updateBtnSubmitStatus(btnValidateSelection, false)
                        modal.close()
                        window.location.href = initRequestOptions._redirect
                    })
            }
        })
}

document.addEventListener('DOMContentLoaded', () => {
    initPageCommon()

    // form
    const form = new RemplaFormValidation('form-request', { debug: false, ajax: true })

    // selection des remplacants
    const modal = new RemplaModal('selection-remplacant-modal')
    const modalDetail = new RemplaModal('remplacant-detail-modal')

    form.setSubmitSuccessHandler(({ id, _redirect }) => {
        const url = document.getElementById('tbl-search-replacements').dataset.listUrl
        const specialite = document.querySelector('#request-specialite')
        const region = document.querySelector('#request-region')

        initSelectRemplacantModal(
            modal,
            modalDetail,
            {
                url,
                _redirect,
                params: {
                    request_id: id,
                    specialite: specialite.value,
                    region: region.value
                }
            },
            {
                specialite: specialite.querySelector(`option[value="${specialite.value}"]`).textContent,
                region: region.querySelector(`option[value="${region.value}"]`).textContent
            }
        )
    })

    // selection d'une specialite
    const specialiteSelect = document.querySelector('#request-specialite')
    const sousSpecialiteWrapper = document.querySelector('#request-sous-specialite-wrapper')
    const sousSpecialiteOptionsListe = document.querySelector('#request-sous-specialite-options-liste')
    if (specialiteSelect && sousSpecialiteWrapper && sousSpecialiteOptionsListe && specialiteSelect.dataset.spsUrl) {
        const url = specialiteSelect.dataset.spsUrl

        specialiteSelect.removeAttribute('data-sps-url')

        specialiteSelect.addEventListener('change', () => {
            let spUrl = url.replace('0000000000', specialiteSelect.value)

            const sousSpecialitesListe = sousSpecialiteOptionsListe.getAttribute('data-sous-specialites')
            const sousSpecialites = sousSpecialitesListe ? sousSpecialitesListe.split(',') : []

            axios.get(spUrl)
                .then((res) => {
                    if (res.data && res.data.length > 0) {
                        const spsOptions = res.data.map(sp => {
                            const isChecked = sousSpecialites.find(id => id == sp.id)

                            return `
                                <div class="w-[49%]">
                                    <input type="checkbox" id="request-sous-specialite-${ sp.id }" name="subSpecialities[]" value="${ sp.id }" class="form-request-element" ${isChecked ? 'checked' : ''}>
                                    <label for="request-sous-specialite-${ sp.id }">${ sp.name }</label>
                                </div>
                            `
                        })
                        sousSpecialiteOptionsListe.innerHTML = spsOptions.join('')
                        sousSpecialiteWrapper.classList.remove('hidden')
                        sousSpecialiteWrapper.classList.add('flex')
                    } else {
                        sousSpecialiteOptionsListe.innerHTML = ''
                        sousSpecialiteWrapper.classList.remove('flex')
                        sousSpecialiteWrapper.classList.add('hidden')
                    }
                })
                .catch(() => {
                    sousSpecialiteOptionsListe.innerHTML = ''
                    sousSpecialiteWrapper.classList.remove('flex')
                    sousSpecialiteWrapper.classList.add('hidden')
                })
        })
    }

    // change raison 'Autre'
    const autreCheckbox = document.querySelector('#request-raison-6')
    const autreRaisonValue = document.querySelector('#request-raison-value-wrapper')
    if (autreCheckbox && autreRaisonValue) {
        autreCheckbox.addEventListener('change', (e) => {
            if (e.target.checked) {
                autreRaisonValue.classList.remove('hidden')
            } else {
                autreRaisonValue.classList.add('hidden')
            }
        })
    }

    // setup datepickers
    const datepickerElems = document.querySelectorAll('.rempla-datepicker')
    datepickerElems.forEach((elem) => {
        new Datepicker(elem, {
            format: 'dd/mm/yyyy',
            minDate: new Date().getTime(),
            autohide: true,
            todayHighlight: true,
        })
    })
})