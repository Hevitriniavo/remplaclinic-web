import initPageCommon from './page-common.js'
import RemplaFormValidation from '../js/rempla-form-validation.js'

document.addEventListener('DOMContentLoaded', () => {
    initPageCommon()
    new RemplaFormValidation('form-contact')
})