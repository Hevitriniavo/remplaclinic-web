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

export const addDataTableLimitOptionsEvent = (tableSelector) => {
    const limitOptions = document.querySelectorAll(tableSelector + ' select[name="limit"]')
    limitOptions.forEach(limitOption => {
        limitOption.addEventListener('change', (e) => {
            const href = window.location.href.split('?')
            const url = new URLSearchParams(href.length > 1 ? href[1] : '')
    
            url.set('limit', e.target.value)
            url.set('page', 1)
    
            window.location.href = href[0] + '?' + url.toString()
        })
    })
}