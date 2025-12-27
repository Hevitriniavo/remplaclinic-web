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
    let id = null;

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

window.getCleanUrl = (url, id) => {
  let result = url
  if (result) {
    result = result.replace('0000000000', id)
  }
  return result
}

window.formatDate = (data, withHour = true) => {
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
