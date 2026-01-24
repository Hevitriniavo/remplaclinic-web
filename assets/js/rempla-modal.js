export default class {
    /**
     * @param {String} selector 
     * @param {{
     *  beforeOpen: Function,
     *  beforeOpenAsync: Function
     * }} modalOptions 
     */
    constructor(selector, modalOptions = {}) {
        this.selector = selector
        this.modal = document.querySelector('#' + selector)

        this.options = modalOptions

        if (this.modal) {
            this.addEventListener()
        }
    }

    addEventListener() {
        this.addActivatorEventListener()
        this.addCloseEventListener()
    }

    addActivatorEventListener() {
        const that = this
        const activators = document.querySelectorAll('.' + this.selector + '-activator')
        activators.forEach(activator => {
            activator.addEventListener('click', (e) => {
                if (that.options.beforeOpen) {
                    if (that.options.beforeOpen(e, activator)) {
                        that.open()
                    }
                } else if (that.options.beforeOpenAsync) {
                    that.options.beforeOpenAsync(e, activator)
                        .then((opened) => {
                            if (opened) {
                                that.open()
                            }
                        })
                        .catch(() => {})
                } else {
                    that.open()
                }
            })
        })
    }

    addCloseEventListener() {
        const that = this
        const closeActions = document.querySelectorAll('#' + this.selector + ' .close')
        closeActions.forEach(closeAction => {
            closeAction.addEventListener('click', () => {
                that.close()
            })
        })
    }

    open() {
        if (this.modal) {
            this.modal.classList.remove('hidden')
            this.modal.classList.add('visible')
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('visible')
            this.modal.classList.add('hidden')
        }
    }

    // <button
    //     class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded my-modal-activator">
    //     Open modal
    // </button>

    // Alert
    // <div id="alert" class="relative p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">
    //     <span class="font-medium">Success!</span> Saved successfully.
    //     <button onclick="document.getElementById('alert').remove()"
    //         class="absolute top-2 right-2 text-green-700 hover:text-green-900">
    //         ✕
    //     </button>
    // </div>
}