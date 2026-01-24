export default class {
    /**
     * @param {String} btnSelector
     * @param {String} contentSelector 
     * @param {{
     *  beforeChange: Function,
     *  beforeChangeAsync: Function
     * }} tabNavigationOptions 
     */
    constructor(btnSelector, contentSelector, tabNavigationOptions = {}) {
        this.buttons = document.querySelectorAll(btnSelector)
        this.contents = document.querySelectorAll(contentSelector)

        this.currentTab = -1
        this.options = tabNavigationOptions

        this.buttons.forEach((btn, index) => {
            btn.addEventListener('click', () => {
                this.handleChangeTab(btn, index)
            })
        })
    }

    setActiveIndex(index) {
        if (index >= 0 && index < this.buttons.length) {
            this.handleChangeTab(this.buttons[index], index)
        }
    }

    handleChangeTab (btn, index) {
        if (this.options.beforeChange) {
            if (this.options.beforeChange(btn, index, this.currentTab)) {
                this.clickActiveTab(btn, index)
            }
        } else if (this.options.beforeChangeAsync) {
            this.options.beforeChangeAsync(btn, index, this.currentTab)
                .then((changed) => {
                    if (changed) {
                        this.clickActiveTab(btn, index)
                    }
                })
                .catch(() => {})
        } else {
            this.clickActiveTab(btn, index)
        }
    }

    clickActiveTab(btn, index) {
        this.buttons.forEach(b => {
            b.classList.remove('text-[#4e4e4e]', 'border-[#4e4e4e]')
            b.classList.add('text-gray-600', 'border-transparent')
        })
        this.contents.forEach(c => c.classList.add('hidden'))

        btn.classList.remove('text-gray-600', 'border-transparent')
        btn.classList.add('text-[#4e4e4e]', 'border-[#4e4e4e]')
        document.getElementById(btn.dataset.tab).classList.remove('hidden')

        this.currentTab = index
    }
}