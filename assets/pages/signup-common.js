import initPageCommon from './page-common.js'
import axios from '../js/axios-instance.js'
import RemplaFormValidation from '../js/rempla-form-validation.js'
import RemplaModal from '../js/rempla-modal.js'
import showToast from '../js/rempla-toaster.js'

export default function initSignupCommon() {
    initPageCommon()

    const form = new RemplaFormValidation('form-signup', { debug: true, ajax: true })
    form.addValidator('passwordConfirmation', (data) => data.passwordConfirmation === data.password)
    form.addValidator('cguAccepted', (data) => {
        const isChecked = data.cguAccepted === '1'
        if (!isChecked) {
            showToast("Vous devriez accepter les conditions générales d'utilisation et s'engager à les respecter.", 'warning')
        }

        return isChecked
    })

    form.setSubmitSuccessHandler(({ _redirect }) => {
        window.location.href = _redirect
    })

    return form
}

export const loadUserInfos = function () {
    const form = document.querySelector('#form-signup')
    if (form && form.dataset.detailUrl) {
        const detailUrl = form.dataset.detailUrl

        form.removeAttribute('data-detail-url')

        axios.get(detailUrl)
            .then(res => {
                const dataMapping = {
                    'user-civilite': res.data.civility,
                    'user-prenom': res.data.surname,
                    'user-nom': res.data.name,
                    'user-numero-inscription': res.data.ordinaryNumber,
                    'user-telephone': res.data.telephone,
                    'user-address-thoroughfare': res.data.address ? res.data.address.thoroughfare : '',
                    'user-address-premise': res.data.address ? res.data.address.premise : '',
                    'user-address-postal-code': res.data.address ? res.data.address.postal_code : '',
                    'user-address-locality': res.data.address ? res.data.address.locality : '',
                    'user-annee-naissance': res.data.yearOfBirth,
                    'user-nationalite': res.data.nationality,
                    'user-email': res.data.email,
                    'user-numero-internat': res.data.yearOfAlternance,
                    'user-statut-actuel': res.data.currentSpeciality,
                    'user-specialite': res.data.speciality ? res.data.speciality.id : '',
                    'user-comment': res.data.userComment,
                    'user-cgu-accepted': true,
                    'user-position': res.data.position,
                    'user-mobile': res.data.telephone2,
                    'user-fax': res.data.fax,
                    'user-chief-service-name': res.data.establishment ? res.data.establishment.chiefServiceName : '',
                    'user-service-name': res.data.establishment ? res.data.establishment.serviceName : '',
                    'user-establishment-name': res.data.establishment ? res.data.establishment.name : '',
                    'user-beds-count': res.data.establishment ? res.data.establishment.bedsCount : '',
                    'user-site-web': res.data.establishment ? res.data.establishment.siteWeb : '',
                    'user-nb-consultations': res.data.establishment ? res.data.establishment.consultationCount : '',
                    'user-consultation-par': res.data.establishment ? res.data.establishment.per : '',
                }

                // sous-specialites
                const sousSpecialiteOptionsListe = document.querySelector('#user-sous-specialite-options-liste')
                if (res.data.subSpecialities && sousSpecialiteOptionsListe) {
                    sousSpecialiteOptionsListe.setAttribute(
                        'data-sous-specialites',
                        res.data.subSpecialities.map(sp => sp.id).join(',')
                    )
                }

                for (const formElementId in dataMapping) {
                    if (!Object.hasOwn(dataMapping, formElementId)) {
                        continue
                    }

                    const formElement = document.getElementById(formElementId)
                    if (formElement) {
                        if (formElement.type === 'checkbox') {
                            if (dataMapping[formElementId]) {
                                formElement.setAttribute('checked', dataMapping[formElementId])
                            } else {
                                formElement.removeAttribute('checked')
                            }
                        } else {
                            formElement.value = dataMapping[formElementId]

                            if (formElement.tagName.toLowerCase() === 'select') {
                                formElement.dispatchEvent(new CustomEvent('change'))
                            }
                        }
                    }
                }

                // mobilities
                if (res.data.mobilities) {
                    res.data.mobilities.forEach(sp => {
                        const formElement = document.getElementById('user-mobilite-' + sp.id)
                        if (formElement) {
                            formElement.setAttribute('checked', 'true')
                        }
                    })
                }
            })
            .catch((err) => {
                console.error('LOAD_USER_INFOS: ', err)
                showToast("Erreur: Impossible d'afficher vos informations personnelles. Veuillez contacter l'administrateur.", 'error', 5000)
            })
    }
}

export const initDesinscriptionModal = function (selector = 'modal-confirmation-desinscription') {
    const form = new RemplaModal(selector, {
        beforeOpenAsync: async (e, activator) => {
            e.preventDefault()
            // update modal data here
            return true
        }
    })

    const btnConfirmDesinscription = document.querySelector('.btn-confirm-desinscription')
    if (btnConfirmDesinscription) {
        btnConfirmDesinscription.addEventListener('click', e => {
            e.preventDefault()

            axios.delete(btnConfirmDesinscription.dataset.deleteUrl)
                .then(() => {
                    window.location.reload()
                })
                .catch(() => {})
        })
    }

    return form
}