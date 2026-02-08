import initPageCommon from './page-common.js'
import RemplaFormValidation from '../js/rempla-form-validation.js'

document.addEventListener('DOMContentLoaded', () => {
    initPageCommon()

    // link generation
    const form = new RemplaFormValidation('form-reset-password', { debug: false, ajax: true })
    form.setSubmitSuccessHandler(({ _redirect }) => {
        window.location.href = _redirect
    })

    // new password
    const newPasswordForm = new RemplaFormValidation('form-password-new', { debug: false, ajax: true })
    newPasswordForm.addValidator('passwordConfirmation', (data) => data.passwordConfirmation === data.password)
    newPasswordForm.setSubmitSuccessHandler(({ _redirect }) => {
        window.location.href = _redirect
    })
})