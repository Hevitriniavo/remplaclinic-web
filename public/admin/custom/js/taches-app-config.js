import { initDataTable, getCleanUrl } from 'admin-app'

function openTachesAppConfigModal(id, url, detailUrl) {
  $('#taches-app-config-modal').modal('show')
  const inputName = $('#taches-app-config-name')
  const inputValue = $('#taches-app-config-value')
  const inputStatut = $('#taches-app-config-status')
  const formTachesAppConfig = $('#taches-app-config-form-detail')
  const btnSaveTachesAppConfig = $('#btn-taches-app-config-detail-save')

  // remove errors
  inputName[0].classList.remove('is-valid', 'is-invalid')
  inputValue[0].classList.remove('is-valid', 'is-invalid')
  inputStatut[0].classList.remove('is-valid', 'is-invalid')
  formTachesAppConfig[0].classList.remove('was-validated')
  formTachesAppConfig[0].reset()
  
  btnSaveTachesAppConfig.attr('data-url', url)
  if (id && detailUrl) {
    btnSaveTachesAppConfig.attr('data-method', 'POST')
    axios.get(detailUrl)
      .then(res => {
        inputName.val(res.data.name)
        inputValue.val(res.data.value)
        inputStatut.prop('checked', res.data.active)
      })
  } else {
    btnSaveTachesAppConfig.attr('data-method', 'POST')
    // clear inputs
    inputName.val('')
    inputValue.val('')
    inputStatut.prop('checked', false)
  }
}

function hideTachesAppConfigModal() {
  $('#taches-app-config-modal').modal('hide')
}

function toggleTachesAppConfigurations(name, isDeleteOp) {
  const tachesAppRequired = $('#taches-app-configurations-required')
  const requiredNames = tachesAppRequired.attr('data-required-names')
  const missingNames = tachesAppRequired.attr('data-missing-values')

  let names = []
  if (requiredNames) {
    names = requiredNames.split(',')
  }

  let missings = []
  if (missingNames) {
    missings = missingNames.split(',')
  }

  if (name) {
    // create or update
    if (!isDeleteOp && names.includes(name)) {
      missings = missings.filter(n => n !== name)
    }

    // delete
    if (isDeleteOp && names.includes(name)) {
      missings.push(name)
    }
  }

  let html = []
  for(let name of missings) {
    html.push(`<span class="text-bold text-white">${name}</span>`)
  }

  tachesAppRequired.attr('data-missing-values', missings.join(','))

  tachesAppRequired.html(missings.length === 0 ? '' : `<div class="alert alert-warning text-center"> Les configurations ${html.join(', ')} sont requises pour que l'app fonctionne correctement.`)
}

$(function () {
  const tblDom = $("#tbl-taches-app-configs")
  const btnNew = $('#btn-taches-app-config-new')
  const btnSaveTachesAppConfig = $('#btn-taches-app-config-detail-save')
  const formTachesAppConfig = $('#taches-app-config-form-detail')

  toggleTachesAppConfigurations()

  const adminEmailDatatable = initDataTable('', tblDom, null, {
    order: [[1, 'asc']],
    columnDefs: [
      {
        targets: 0,
        data: "id",
        width: '4%',
        orderable: false,
        render: function (data, type, row, meta) {
          return `
            <div class="custom-control custom-checkbox">
              <input class="custom-control-input custom-control-input-secondary taches-app-config-selection" type="checkbox" id="taches-app-config-selection-${data}" value="${data}">
              <label for="taches-app-config-selection-${data}" class="custom-control-label"></label>
            </div>
          `
        }
      },
      {
        targets: 1,
        data: "id",
        width: '10%',
      },
      {
        targets: 2,
        data: "name",
        width: '35%',
      },
      {
        targets: 3,
        data: "value",
        width: '35%',
      },
      {
        targets: 4,
        data: 'active',
        width: '6%',
        render: function (data, type, row, meta) {
          return data ? 'Actif' : 'Inactif'
        }
      },
      {
        targets: 5,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '10%',
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id'])
          const editUrl = getCleanUrl(tblDom.data('edit-url'), row['id'])
          const detailUrl = getCleanUrl(tblDom.data('detail-url'), row['id'])
          return (
            "<div>" +
            '<a class="btn btn-sm btn-outline-info btn-edit" data-url="'+ editUrl +'" data-detail-url="'+ detailUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-edit"></i></a>' +
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
            "</div>"
          )
        },
      },
    ],
  })

  // delete
  $(document).on('deletedEvent', function() {
    adminEmailDatatable.draw()
  })

  // edit
  $(document).on('click', '.btn-edit', function() {
    const btn = $(this)
    const id = btn.data('id')
    const url = getCleanUrl(btn.data('url'), id)
    const detailUrl = getCleanUrl(btn.data('detail-url'), id)
    openTachesAppConfigModal(id, url, detailUrl)
  })

  // the modal
  btnNew.on('click', function (e) {
    openTachesAppConfigModal(null, $(this).data('url'))
  })

  btnSaveTachesAppConfig.on('click', function(e) {
    if (!formTachesAppConfig[0].checkValidity()) {
      e.preventDefault()
      e.stopPropagation()
      formTachesAppConfig[0].classList.add('was-validated')
    } else {
      const inputs = {
        name: $('#taches-app-config-name').val(),
        value: $('#taches-app-config-value').val(),
        active: $('#taches-app-config-status').prop('checked'),
      }

      axios.request({
        url: btnSaveTachesAppConfig.attr('data-url'),
        method: btnSaveTachesAppConfig.attr('data-method'),
        data: inputs,
      })
        .then(() => {
          hideTachesAppConfigModal()
          toggleTachesAppConfigurations(inputs.name, !inputs.active)
          adminEmailDatatable.draw()
        })
    }
  })
})
