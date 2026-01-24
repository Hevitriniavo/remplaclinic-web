export default class {
    /**
     * @param {{
     *   limit: Number,
     *   limit_options: Array<Number>,
     *   offset: Number,
     *   page: Number,
     *   max_page: Number,
     *   pages: Array<Number>
     * }} pagination Les elements de la pagination
     * @param {String} url 
     */
    constructor(pagination, url = '') {
        this.pagination = pagination
        this.url = url
    }

    /**
     * Recuperer le HTML pour afficher les options pour l'affichage
     * 
     * @param {Number} totalRecords 
     */
    renderLimitOptions(totalRecords) {
        const limitOptions = this.pagination.limit_options
        const limit = this.pagination.limit
        const offset = this.pagination.offset

        const optionHtml = []
        for (const limitOption of limitOptions) {
            const selected = limitOption === limit ? ' selected' : ''
            optionHtml.push(`<option value="${limitOption}"${selected}>${limitOption}</option>`)
        }

        return `Afficher <select name="limit" class="outline-none border-[1px] border-solid border-[#eaeaea] h-50 w-100 p-2 rounded-md bg-transparent">${optionHtml.join('')}</select> sur <span>${totalRecords}</span> lignes (de <span>${offset + 1}</span> à <span>${Math.min(offset + limit, totalRecords)}</span> lignes)`
    }

    /**
     * Recuperer le HTML qui represente la liste des pages
     * 
     * @returns {String}
     */
    renderPageLinks () {
        const page = this.pagination.page
        const maxPage = this.pagination.max_page

        const url = this.getUrl()
        
        const pages = this.pagination.pages

        const pageLinksHtml = []

        // previous page link
        pageLinksHtml.push(this.getPageLink(page - 1, url, false, page <= 1, '&lt;'))
        
        // first page
        for (const p of pages) {
            if (p === '...') {
                pageLinksHtml.push(this.getPageLink(0, url, false, false, '...'))
            } else {
                pageLinksHtml.push(this.getPageLink(p, url, p === page, false))
            }
        }

        // next page
        pageLinksHtml.push(this.getPageLink(page + 1, url, false, page >= maxPage, '&gt;'))

        return pageLinksHtml.join('')
    }

    /**
     * Recuperer le HTML pour afficher les controls de la pagination.
     * 
     * @param {Number} totalRecords 
     * @returns {String}
     */
    render (totalRecords) {
        const limitOptionsView = this.renderLimitOptions(totalRecords)
        const pageLinksView = this.renderPageLinks()

        return `<div class="flex justify-between items-center gap-2 mt-2"><div>${limitOptionsView}</div><div class="page-links">${pageLinksView}</div></div>`
    }

    /**
     * L'URL de base pour le tableau
     * 
     * @returns {String}
     */
    getUrl() {
        return this.url ? this.url : (this.pagination._url ? this.pagination._url : '')
    }

    /**
     * Recuper le HTML qui represente une seule page.
     * 
     * @param {Number|String} page Le page qui appartient au lien
     * @param {String} url L'URL de base 
     * @param {Boolean} active Si c'est la page courante 
     * @param {Boolean} disabled Si c'est une page d'indication 
     * @param {String} pageText Le texte a afficher sur le lien
     * @returns {String}
     */
    getPageLink(page, url, active = false, disabled = false, pageText = '') {
        let baseUrl = ''
        if (url.indexOf('?') < 0) {
            baseUrl = url + '?'
        } else {
            baseUrl = url + '&'
        }

        if (!pageText) {
            pageText = page
        }

        const activeClass = active ? 'border-[#86d0f0] bg-[#ebf6fc] text-[#2d8eb8]' : 'border-[#eaeaea]'
        const startTag = disabled ? 'span' : `a href="${baseUrl}page=${page}"`
        const endTag = disabled ? 'span' : 'a'

        return `<${startTag} class="p-2 border-[1px] border-solid ${activeClass}" data-page="${page}">${pageText}</${endTag}>`
    }
}