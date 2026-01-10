import axios from 'axios'
import initSignupCommon from './signup-common.js'

document.addEventListener('DOMContentLoaded', () => {
    initSignupCommon()

    // selection d'une specialite
    const specialiteSelect = document.querySelector('#user-specialite')
    const sousSpecialiteWrapper = document.querySelector('#user-sous-specialite-wrapper')
    const sousSpecialiteOptionsListe = document.querySelector('#user-sous-specialite-options-liste')
    if (specialiteSelect && sousSpecialiteWrapper && sousSpecialiteOptionsListe && specialiteSelect.dataset.spsUrl) {
        const url = specialiteSelect.dataset.spsUrl

        specialiteSelect.removeAttribute('data-sps-url')

        specialiteSelect.addEventListener('change', () => {
            let spUrl = url.replace('0000000000', specialiteSelect.value)
            axios.get(spUrl)
                .then((res) => {
                    if (res.data && res.data.length > 0) {
                        const spsOptions = res.data.map(sp => {
                            return `
                                <div class="w-[49%]">
                                    <input type="checkbox" id="user-sous-specialite-${ sp.id }" name="subSpecialities[]" value="${ sp.id }" class="form-signup-element">
                                    <label for="user-sous-specialite-${ sp.id }">${ sp.name }</label>
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

    // selection & deselection region
    const selectionLinks = document.querySelectorAll('#user-mobilite-selection-wrapper a')
    selectionLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault()

            link.classList.toggle('hidden')

            const checkedValue = link.classList.contains('check_all')
            document.querySelectorAll('.user-mobilite-options')
                .forEach(option => {
                    if (checkedValue) {
                        option.setAttribute('checked', checkedValue)
                    } else {
                        option.removeAttribute('checked')
                    }
                })
            
            // show the opposite btn
            const oppositeClass = checkedValue ? 'uncheck_all' : 'check_all'
            const oppositeLink = document.querySelector('#user-mobilite-selection-wrapper .' + oppositeClass)
            oppositeLink.classList.toggle('hidden')
        })
    })
})