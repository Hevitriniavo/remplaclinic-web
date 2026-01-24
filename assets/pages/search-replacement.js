import initPageCommon, { addDataTableLimitOptionsEvent } from './page-common.js'

document.addEventListener('DOMContentLoaded', () => {
    initPageCommon()

    // search
    const inputSearch = document.getElementById('tbl-search-replacements-search')
    if (inputSearch) {
        inputSearch.addEventListener('keyup', (e) => {
            if (e.keyCode === 13) {
                const href = window.location.href.split('?')
                const url = new URLSearchParams(href.length > 1 ? href[1] : '')

                url.set('search', e.target.value)

                window.location.href = href[0] + '?' + url.toString()
            }
        })
    }

    // pagination
    addDataTableLimitOptionsEvent('#tbl-search-replacements-pager')
})