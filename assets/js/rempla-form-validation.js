export default class RemplaFormValidation {
    constructor(formSelector) {
        this.formSelector = formSelector
        this.form = document.getElementById(formSelector)
        if (this.form) {
            this.addSubmitEventListener()
        }
    }

    addSubmitEventListener() {
        const that = this
        this.form.addEventListener('submit', (e) => {
            const elements = that.form.querySelectorAll('.' + that.formSelector + '-element')

            let isValid = true

            if (elements.length > 0) {
                elements.forEach(inputEl => {
                    const val = inputEl.value
                    const dataset = inputEl.dataset

                    let isInputValid = true

                    if (dataset.required === 'true' && !val) {
                        isInputValid = false
                    }
                    
                    if (isInputValid) {
                        inputEl.classList.remove('invalid')
                    } else {
                        inputEl.classList.add('invalid')
                        isValid = false
                    }
                })
            }

            if (!isValid) {
                e.preventDefault()
            }
        })
    }
}