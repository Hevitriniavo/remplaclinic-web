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