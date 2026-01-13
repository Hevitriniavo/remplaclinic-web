import axios from 'axios'

export default class RemplaFormValidation {
    constructor(formSelector, options = {}) {
        this.formSelector = formSelector
        this.debug = options.debug === true
        this.ajax = options.ajax === true

        this.form = document.getElementById(formSelector)
        this.validators = {}

        if (this.form) {
            this.addSubmitEventListener()
            this.buildValidators()

            if (this.debug) {
                this.setSubmitSuccessHandler((data) => {
                    console.log('FORM SUBMISSION RESULT: ', data)
                })
                this.setSubmitErrorHandler((error) => {
                    console.error('FORM SUBMISSION ERROR: ', error)
                })
            }
        }
    }

    setSubmitSuccessHandler(cb) {
        this.submitSuccessHandler = cb
    }

    setSubmitErrorHandler(cb) {
        this.submitErrorHandler = cb
    }

    setAjaxHandler(cb) {
        this.ajaxHandler = cb
    }

    getFormElements() {
        const elements = this.form.querySelectorAll('.' + this.formSelector + '-element')
        return elements.length > 0 ? elements : []
    }

    buildValidators() {
        const elements = this.getFormElements()

        const isEmptyVal = (val) => val === undefined || val === null || val === ''

        elements.forEach((inputEl) => {
            const dataset = inputEl.dataset

            // required
            if (dataset.required === 'true') {
                this.addValidator(inputEl.name, (data) => !isEmptyVal(data[inputEl.name]))
            }

            // regex
            if (dataset.regex) {
                this.addValidator(inputEl.name, (data) => isEmptyVal(data[inputEl.name]) || new RegExp(dataset.regex).test(data[inputEl.name]))
            }

            // min & data-min
            if (dataset.min || inputEl.min) {
                this.addValidator(inputEl.name, (data) => isEmptyVal(data[inputEl.name]) || data[inputEl.name] >= Number(dataset.min ? dataset.min : inputEl.min))
            }

            // max
            if (dataset.max || inputEl.max) {
                this.addValidator(inputEl.name, (data) => isEmptyVal(data[inputEl.name]) || data[inputEl.name] <= Number(dataset.max ? dataset.max : inputEl.max))
            }

            // minlength && dataset.minlength
            if (dataset.minlength || inputEl.minlength) {
                this.addValidator(inputEl.name, (data) => isEmptyVal(data[inputEl.name]) || data[inputEl.name].length >= Number(dataset.minlength ? dataset.minlength : inputEl.minlength))
            }

            // maxlength && dataset.maxlength
            if (dataset.maxlength || inputEl.maxlength) {
                this.addValidator(inputEl.name, (data) => isEmptyVal(data[inputEl.name]) || data[inputEl.name].length <= Number(dataset.maxlength ? dataset.maxlength : inputEl.maxlength))
            }
        })
    }

    getFormData() {
        const elements = this.getFormElements()

        let data = {}

        elements.forEach(inputEl => {
            let inputData = null
            let ignore = false
            // checkbox
            if (inputEl.type === 'checkbox') {
                const checked = inputEl.checked
                const checkedValue = inputEl.dataset.checkedValue
                const uncheckedValue = inputEl.dataset.uncheckedValue

                let value = false

                if (checked) {
                    value = checkedValue ? checkedValue : inputEl.value
                } else {
                    value = uncheckedValue ? uncheckedValue : false

                    // pour un checkbox multiple or choices, on ignore
                    ignore = inputEl.name.indexOf('[]') > 0
                }

                inputData = value
            } else if (inputEl.type === 'number') {
                let val = inputEl.value
                if (typeof val === 'string') {
                    val = val.trim()
                }
                inputData = Number(val)
            } else {
                let val = inputEl.value
                if (typeof val === 'string') {
                    val = val.trim()
                }
                inputData = val
            }

            if (!ignore) {
                let inputKey = inputEl.name
                const indexKeyArray = inputKey.indexOf('[]')
                if (indexKeyArray > 0) {
                    inputKey = inputKey.substring(0, indexKeyArray)
                }

                if (Object.hasOwn(data, inputKey)) {
                    if (Array.isArray(data[inputKey])) {
                        data[inputKey].push(inputData)
                    } else {
                        data[inputKey] = [data[inputKey], inputData]
                    }
                } else {
                    data[inputKey] = inputData
                }
            }
        })

        return data
    }

    addValidator(field, validator) {
        if (!Object.hasOwn(this.validators, field)) {
            this.validators[field] = []
        }
        this.validators[field].push(validator)
    }

    isValid(field, data) {
        if (!Object.hasOwn(this.validators, field)) {
            return true
        }

        const fieldValidators = this.validators[field]
        for (const validator of fieldValidators) {
            if (!validator(data)) {
                if (this.debug) {
                    console.log('INVALID DATA: ', { field, field_data: data[field], data, validator })
                }
                return false
            }
        }

        return true
    }

    addSubmitEventListener() {
        const that = this
        that.form.addEventListener('submit', (e) => {
            e.preventDefault()
            that.updateBtnSubmitStatus(true)

            const data = that.getFormData()

            const elements = that.getFormElements()

            let isValid = true

            elements.forEach(inputEl => {
                if (that.isValid(inputEl.name, data)) {
                    inputEl.classList.remove('invalid')
                } else {
                    inputEl.classList.add('invalid')
                    isValid = false
                }
            })

            if (isValid) {
                if (that.ajax) {
                    if (that.ajaxHandler) {
                        that.updateBtnSubmitStatus(false)
                        that.ajaxHandler(that.form, data)
                    } else {
                        that.submitAjax()
                            .then((res) => {
                                if (that.submitSuccessHandler) {
                                    that.submitSuccessHandler(res.data, res)
                                }
                            }).catch((err) => {
                                if (that.submitErrorHandler) {
                                    that.submitErrorHandler(err)
                                }
                            }).finally(() => {
                                that.updateBtnSubmitStatus(false)
                            })
                    }
                } else {
                    that.updateBtnSubmitStatus(false)
                    that.submit()
                }
            } else {
                that.updateBtnSubmitStatus(false)
            }
        })
    }

    updateBtnSubmitStatus(isLoader = true) {
        const button = this.form.querySelector('.' + this.formSelector + '-btn-submit')

        if (button) {
            // const text = button.querySelector('.btn-text')
            const spinner = button.querySelector('.btn-spinner')

            if (!spinner) {
                return
            }

            if (isLoader) {
                button.disabled = true
                // text.classList.add('invisible')
                spinner.classList.remove('hidden')
            } else {
                button.disabled = false
                // text.classList.remove('invisible')
                spinner.classList.add('hidden')
            }
        }
    }

    submit() {
        this.form.submit()
    }

    async submitAjax() {
        const url = this.form.action
        const method = this.form.method

        const data = new FormData(this.form)

        const response = await axios.request({
            url: url,
            method: method,
            data: data,
        })

        return response
    }
}