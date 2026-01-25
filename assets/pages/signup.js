import { default as initSignupCommon, loadUserInfos, initDesinscriptionModal } from './signup-common.js'

document.addEventListener('DOMContentLoaded', () => {
    initSignupCommon()

    // load user infos if needed
    loadUserInfos()

    // desinscription
    initDesinscriptionModal('modal-confirmation-desinscription')
})