import initPageCommon from './page-common.js'
import RemplaFormValidation from '../js/rempla-form-validation.js'

import showToast from '../js/rempla-toater.js'

document.addEventListener('DOMContentLoaded', () => {
    initPageCommon()

    const form = new RemplaFormValidation('form-signup', true)
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
})