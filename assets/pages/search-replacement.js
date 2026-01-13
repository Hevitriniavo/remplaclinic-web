import initPageCommon from './page-common.js'

document.addEventListener('DOMContentLoaded', () => {
    initPageCommon()

    // search
    const inputSearch = document.querySelector('#tbl-search-replacements-search')
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
    const limitOptions = document.querySelector('#tbl-search-replacements-pager select[name="limit"]')
    if (limitOptions) {
        limitOptions.addEventListener('change', (e) => {
            const href = window.location.href.split('?')
            const url = new URLSearchParams(href.length > 1 ? href[1] : '')

            url.set('limit', e.target.value)
            url.set('page', 1)

            window.location.href = href[0] + '?' + url.toString()
        })
    }
})