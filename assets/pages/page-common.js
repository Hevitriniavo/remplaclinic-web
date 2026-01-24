import '../styles/app.css'

import createHomeSlider from '../js/home-slider.js'
import createRemplaItemSlider from '../js/rempla-item-slider.js'
import initFormLoginValidation from '../js/login.js'

export default function initPageCommon() {
    createHomeSlider()
    createRemplaItemSlider('.slide-partenaire-logo', 10000)
    createRemplaItemSlider('.slide-evidence', 5000)

    initFormLoginValidation()
}

export const getCleanUrl = (url, id) => {
    let result = url
    if (result) {
        result = result.replace('0000000000', id)
    }
    return result
}