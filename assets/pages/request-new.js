import axios from 'axios'

import { Datepicker } from 'vanillajs-datepicker'
import 'vanillajs-datepicker/dist/css/datepicker-bulma.min.css'

import initPageCommon from './page-common.js'
import RemplaFormValidation from '../js/rempla-form-validation.js'

document.addEventListener('DOMContentLoaded', () => {
    initPageCommon()

    const form = new RemplaFormValidation('form-request', { debug: true, ajax: true })

    form.setSubmitSuccessHandler(({ _redirect }) => {
        window.location.href = _redirect
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