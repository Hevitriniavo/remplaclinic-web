import PaginationView from './pagination-view.js'

export default class {
    /**
     * Les options pour le tableau
     * 
     * @param {{
     *  selector: String,
     *  tableBody: HTMLElement,
     *  requestData: Function,
     *  columns: Array<String>,
     *  renderRow: Function,
     *  paginationContainer: HTMLElement,
     *  paginationView: Function,
     *  renderNoData: Function,
     *  emptyText: String,
     *  afterDrawn: Function,
     *  onError: Function
     * }} options 
     */
    constructor(options) {
        this.options = options
        this.drawn = 0
    }

    draw(requestOptions = {}) {
        const that = this

        const onError = that.options.onError ? that.options.onError : () => {}
        const afterDrawn = that.options.afterDrawn ? that.options.afterDrawn : () => {}

        if (that.options.requestData) {
            // request data must return a promise with data: {result: {data: [], totalRecords: 0}, params: {...}}
            that.options.requestData(requestOptions)
                .then(data => {
                    if (data.result) {
                        if (data.result.data) {
                            that.render(data.result.data)
                            that.renderPagination(data.result.totalRecords, data.params)
                            
                            afterDrawn(data, that)

                            that.addEventListener()
                        } else {
                            onError(new Error('Response data not valid. Response data must have result.data key.', { cause: 'REQUEST_DATA_NO_RESULT_DATA_KEY' }))
                        }
                    } else {
                        onError(new Error('Response data not valid. Response data must have result key.',  { cause: 'REQUEST_DATA_NO_RESULT_KEY' }))
                    }
                }).catch(err => {
                    onError(new Error('Request data failed', { cause: err }))
                })
        } else {
            onError(new Error('No request data options.', { cause: 'NO_OPTIONS_REQUEST_DATA' }))
        }
    }

    render(data) {
        this.resetTableBody()

        if (!data || data.length == 0) {
            const renderNoData = this.getRenderNoDataFn()
            renderNoData()
        } else {
            if (this.options.render) {
                this.options.render(data)
            } else {
                const tableBody = this.getTableBody()
                const createRow = this.getCreateRowFn()
                const rowRenderer = this.getRenderRowFn()
    
                if (!tableBody) {
                    throw new Error('No body for the given table. You must specify the correct table body.')
                }
    
                data.forEach((rowData, index) => {
                    const rowHtml = createRow(index)
                    rowHtml.innerHTML = rowRenderer(rowData, index)
    
                    tableBody.appendChild(rowHtml)
                })
            }
        }


        this.drawn++
    }

    renderPagination(totalRecords, params) {
        const renderView = this.options.paginationView ? this.options.paginationView : this.defaultPaginationView
        const paginationContainer = this.getTablePaginationContainer()

        if (paginationContainer) {
            paginationContainer.innerHTML = renderView(totalRecords, params)
        }
    }

    resetTableBody () {
        const body =  this.getTableBody()
        body.innerHTML = ''
    }

    addEventListener () {
        const that = this

        // @TODO: add sort support
        // @TODO: add search support


        // pagination
        const paginationContainer =  this.getTablePaginationContainer()

        // limit
        const selectLimitOptions = paginationContainer.querySelector('select[name="limit"]')
        selectLimitOptions.addEventListener('change', (e) => {
            that.draw({ params: { limit: e.target.value, page: 1 } })
        })
        
        // page
        const pageLinks = paginationContainer.querySelectorAll('.page-links a')
        pageLinks.forEach(pageLink => {
            pageLink.addEventListener('click', (e) => {
                e.preventDefault()

                that.draw({
                    url: e.target.href,
                })
            })
        })
    }

    getTableBody() {
        return this.options.tableBody ? this.options.tableBody : document.querySelector('#' + this.options.selector + ' tbody')
    }

    getTablePaginationContainer () {
        return this.options.paginationContainer ? this.options.paginationContainer : document.querySelector('.' + this.options.selector + '-pagination-container')
    }

    getCreateRowFn () {
        return this.options.createRow ? this.options.createRow : this.defaultCreateRow
    }

    getRenderRowFn () {
        return this.options.renderRow ? this.options.renderRow : this.defaultRenderRow
    }

    getRenderNoDataFn () {
        return this.options.renderNoData ? this.options.renderNoData : this.defaultRenderNoData
    }

    getColumnCount() {
        return this.options.columns ? this.options.columns.length : document.querySelectorAll('#' + this.options.selector + ' thead tr th').length
    }

    defaultPaginationView(totalRecords, params = {}) {
        const paginationView = new PaginationView(params)

        return paginationView.render(totalRecords, params)
    }

    defaultCreateRow(index) {
        const rowHtml = document.createElement('tr')
        rowHtml.className = `hover:bg-[#fff5e9] border-b ${ index % 2 ? '' : 'bg-gray-50' }`

        return rowHtml
    }

    defaultRenderRow (rowData, index) {
        const result = []
        if (this.options.columns) {
            for (const colName of this.options.columns) {
                result.push(this.defaultRenderColumn(colName, rowData[colName], rowData))
            }
        } else {
            for (const colName in rowData) {
                if (!Object.hasOwn(rowData, colName)) continue
                
                result.push(this.defaultRenderColumn(colName, rowData[colName], rowData))
            }
        }
    }

    defaultRenderColumn (colName, data, rowData) {
        return `<td class="px-4 py-2">${data}</td>`
    }

    defaultRenderNoData () {
        const createRow = this.getCreateRowFn()
        const rowHtml = createRow(0)
        const emptyText = this.options.emptyText ? this.options.emptyText : 'No data available'

        rowHtml.innerHTML = `<td colspan="${this.getColumnCount()}" class="px-4 py-2">${emptyText}</td>`
    }
}