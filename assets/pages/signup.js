import { default as initSignupCommon, loadUserInfos } from './signup-common.js'

document.addEventListener('DOMContentLoaded', () => {
    initSignupCommon()

    // load user infos if needed
    loadUserInfos()
})