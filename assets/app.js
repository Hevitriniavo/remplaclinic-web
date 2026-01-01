import './styles/app.css'

import createHomeSlider from './js/home-slider.js'
import createRemplaItemSlider from './js/rempla-item-slider.js'

document.addEventListener('DOMContentLoaded', () => {
    createHomeSlider()
    createRemplaItemSlider('.slide-partenaire-logo', 10000)
    createRemplaItemSlider('.slide-evidence', 5000)
})