import './styles/app.css'

import createHomeSlider from './js/home-slider.js'
import createPartenaireLogoSlider from './js/partenaire-logo-slider.js'

document.addEventListener('DOMContentLoaded', () => {
    createHomeSlider()
    createPartenaireLogoSlider()
})