function initDeleteButtons() {
  const deleteModal = $("#delete-modal")
  const deleteForm = $("#form-delete-id")

  $(document).on("click", ".btn-delete", function (e) {
    const button = $(this)

    // params
    const estMultipleAttr = button.data("multiple")
    // quand on ajout dynamiquement l'attr alors son valeur est de type string
    const estMultiple = estMultipleAttr === true || estMultipleAttr === 'true'
    const multipleIdsSelector = button.data('selector')

    // id
    let id = null

    if (estMultiple) {
      // action multiple
      const allIds = $(multipleIdsSelector + ':checked')

      const ids = []
      allIds.each((index, element) => { ids.push(element.value) })

      // si id vide alors on ouvre pas le modal
      if (ids.length > 0) {
        id = ids.join(',')
      }
    } else {
      id = button.data("id")
    }

    if (id) {
      deleteModal.modal("show")

      // url
      const deleteUrl = button.data("url")
      deleteModal.find("#btn-delete").attr("data-url", deleteUrl)

      // id
      const inputId = deleteModal.find("#id")
      inputId.attr('data-multiple', estMultiple)
      inputId.val(id)
    }
  })

  $(document).on("click", "#btn-delete", function (e) {
    const inputId = deleteModal.find("#id")
    const id = inputId.val()
    const estMultipleAttr = inputId.attr("data-multiple")
    const estMultiple = estMultipleAttr === 'true' || estMultipleAttr === true

    const deleteUrl = $(this).attr("data-url")
    const typeId = $("#typed-id")
    const typeIdValue = typeId.val()

    if (estMultiple && typeIdValue != 'multiple' || (!estMultiple && id !== typeIdValue)) {
      typeId[0].classList.add("is-invalid")
    } else {
      const deletePayload = {}

      if (estMultiple) {
        deletePayload.ids = id.split(',')
      }

      axios.delete(deleteUrl, {
        data: deletePayload
      }).then(() => {
        deleteModal.modal("hide")
        document.dispatchEvent(
          new CustomEvent("deletedEvent", {
            detail: { deleted: true },
          })
        )
      })
    }
  })

  deleteModal.on("hidden.bs.modal", function () {
    const typeId = $("#typed-id")
    typeId[0].classList.remove("is-invalid")
    typeId.val("")

    const inputId = deleteModal.find("#id")
    inputId.val("")
    inputId.attr("data-multiple", false)

    deleteModal.find("#btn-delete").attr("data-url", "")
    deleteForm[0].classList.remove("was-validated")
  })
}

function initToggleSidebar() {
  $('body').on('collapsed.lte.pushmenu', () => {
    updateSidebarState(true)
  })

  $('body').on('shown.lte.pushmenu', () => {
    updateSidebarState(false)
  })

  function updateSidebarState(collapsed) {
    const saveUrl = document.body.dataset.sidebarUrlSave

    axios.put(
      saveUrl,
      new URLSearchParams({
        collapsed: collapsed ? 1 : 0
      })
    )
  }

}

function initSelectionToutCheckbox() {
  $(document).on('change', '.checkbox-selection-tout', (e) => {
    const selector = e.target.dataset.selector

    if (selector) {
      const allIds = $(selector)
      allIds.each((index, element) => { $(element).prop('checked', e.target.checked) })
    }
  })
}

function showLoader() {
    $('#top-loader').fadeIn(100)
}

function hideLoader() {
    $('#top-loader').fadeOut(200, function () {
        $('.top-loader-bar').css('width', '0%')
    })
}


const getCleanUrl = (url, id) => {
  let result = url
  if (result) {
    result = result.replace('0000000000', id)
  }
  return result
}

const formatDate = (data, withHour = true) => {
  const date = new Date(data)
  const options = {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  }
  if (withHour) {
    options['hour'] = '2-digit'
    options['minute'] = '2-digit'
  }
  const formatter = new Intl.DateTimeFormat("fr-FR", options)
  return formatter.format(date)
}

document.addEventListener('DOMContentLoaded', () => {
  initDeleteButtons()

  initToggleSidebar()

  initSelectionToutCheckbox()
})

export { getCleanUrl, formatDate }

/**************************/
/**** FORM DATA HELPER ****/
/**************************/
const toQueryParams = (data, baseUrl = '') => {
  const builder = new URLSearchParams()
  const dataArrayLike = toArrayDataLike(data)
  for (const line of dataArrayLike) {
    builder.append(line.name, line.value)
  }
  const params = builder.toString()
  if (!baseUrl || !typeof baseUrl === 'string') {
    return params
  }
  if (baseUrl.indexOf('?') > 0) {
    return `${baseUrl}&${params}`
  }

  return `${baseUrl}?${params}`
}

const toFormData = (data, parsed = false) => {
  const builder = new FormData()
  const dataArrayLike = parsed ? data : toArrayDataLike(data)
  for (const line of dataArrayLike) {
    builder.append(line.name, line.value)
  }
  return builder
}

const toArrayDataLike = (data, rootName = '') => {
  let results = []
  for (const key in data) {
    if (Object.hasOwnProperty.call(data, key)) {
      const element = data[key]
      if (element === null || element === undefined) {
        continue
      }
      const path = rootName ? `${rootName}[${key}]` : `${key}`
      // if (Array.isArray(data)) {
      //   path = `${path}[${key}]`
      // } else {
      //   path = `${path}${path ? '.' : ''}${key}`
      // }
      if (Array.isArray(element) || typeof element === 'object') {
        results = results.concat(toArrayDataLike(element, path))
      } else {
        results.push({
          name: path,
          value: element
        })
      }
    }
  }
  return results
}

const isEmptyValue = (filterValue) => {
    return filterValue === '' || filterValue === null || filterValue === undefined || (Array.isArray(filterValue) && filterValue.length === 0);
}

export { toQueryParams, toFormData, toArrayDataLike, isEmptyValue }

/**************************/
/**** Add axios loader ****/
/**************************/
axios.interceptors.request.use(
  function (config) {
    showLoader()
    return config
  },
  function (error) {
    hideLoader()
    return Promise.reject(error)
  }
)

axios.interceptors.response.use(
  function (response) {
    hideLoader()
    return response
  },
  function (error) {
    hideLoader()

    console.error('API_ERROR: ', error.response?.data || error)

    // check if error is an api error
    const message = error.response?.data?.error
    if (message) {
      showAlert(message)
    }

    return Promise.reject(error.response?.data || error)
  }
)

/****************************************/
/**** ALERT MESSAGE (API ERROR, ETC) ****/
/****************************************/
const ALERT_TYPES = {
    error: {
        class: 'alert-danger',
        icon: 'fas fa-times-circle',
        title: 'Error'
    },
    warning: {
        class: 'alert-warning',
        icon: 'fas fa-exclamation-triangle',
        title: 'Warning'
    },
    success: {
        class: 'alert-success',
        icon: 'fas fa-check-circle',
        title: 'Success'
    },
    info: {
        class: 'alert-info',
        icon: 'fas fa-info-circle',
        title: 'Info'
    }
}

let alertTimeout = null

function showAlert(message, type = 'error', duration = 5000) {
    const container = document.getElementById('alert-container')
    const box = document.getElementById('alert-box')
    const icon = document.getElementById('alert-icon')
    const title = document.getElementById('alert-title')
    const messageEl = document.getElementById('alert-message')

    const config = ALERT_TYPES[type] || ALERT_TYPES.error

    // Reset classes
    box.className = 'alert alert-dismissible fade show shadow d-flex align-items-start'
    box.classList.add(config.class)

    icon.className = config.icon + ' mr-2 mt-1'
    title.textContent = config.title
    messageEl.textContent = message

    container.style.display = 'block'

    if (alertTimeout) clearTimeout(alertTimeout)
    alertTimeout = setTimeout(hideAlert, duration)
}

function hideAlert() {
    document.getElementById('alert-container').style.display = 'none'
}

export { showAlert, hideAlert }

/**********************************/
/**** DATATABLE INITIALIZATION ****/
/**********************************/
const initDataTable = (selector, jQueryDom = null, url = null, options = {}) => {
    const tblDom = jQueryDom ? jQueryDom : jQuery(selector)
    const tblApiUrl = url ? url : tblDom.data('url')
    const defaultOptions = {
        paging: true,
        searching: true,
        ordering: true,
        responsive: true,
        language: {
            lengthMenu: "Afficher _MENU_ ligne par page",
            zeroRecords: "Aucun entré trouvé",
            infoFiltered: "(Nombre de lignes: _MAX_)",
            infoEmpty: "",
            info: "Ligne _START_ à _END_ sur _TOTAL_ lignes.",
            paginate: {
                previous: "<<",
                next: ">>",
            },
        },
        serverSide: true,
        ajax: function (data, callback) {
            axios.get(tblApiUrl, { params: data })
                .then(response => callback(response.data))
                .catch((err) => {
                    console.error('DATATABLE ERROR: ', err)
                    callback({ data: [] })
                })
        },
    }

    Object.assign(defaultOptions, options)

    return tblDom.DataTable(defaultOptions)
}

export { initDataTable }

/********************************/
/**** SELECT2 INITIALIZATION ****/
/********************************/
const initSelect2 = (selector, options = {}) => {
    const defaultOptions = {
        theme: 'bootstrap4',
        allowClear: true,
        placeholder: '- Choisir une option -'
    }

    Object.assign(defaultOptions, options)

    jQuery(selector).select2(defaultOptions)
}

export { initSelect2 }

/***********************************/
/**** DATEPICKER INITIALIZATION ****/
/***********************************/
const initDatepicker = (selector, options = {}) => {
    const defaultOptions = {
        format: "dd/mm/yyyy",
        autoclose: true,
    }

    Object.assign(defaultOptions, options)

    jQuery(selector).datepicker(defaultOptions)
}

export { initDatepicker }

/***********************************/
/**** SUMMERNOTE INITIALIZATION ****/
/***********************************/
const initSummernote = (selector, options = {}) => {
    const defaultOptions = {
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']],
        ],
    }

    Object.assign(defaultOptions, options)

    jQuery(selector).summernote(defaultOptions)
}

export { initSummernote }