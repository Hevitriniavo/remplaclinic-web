import initPageCommon from './page-common.js'
import RemplaFormValidation from '../js/rempla-form-validation.js'

document.addEventListener('DOMContentLoaded', () => {
    initPageCommon()

    const form = new RemplaFormValidation('form-contact', { debug: false, ajax: true })
    form.setSubmitSuccessHandler(({ _redirect }) => {
        window.location.href = _redirect
    })
})