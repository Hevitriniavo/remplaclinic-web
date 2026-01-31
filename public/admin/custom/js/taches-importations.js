function openTachesImportationsModal(id, url, detailUrl) {
  $('#taches-importations-modal').modal('show')
  const inputLabel = $('#taches-importations-label')
  const inputScript = $('#taches-importations-script')
  const inputOptions = $('#taches-importations-options')
  const inputLastId = $('#taches-importations-last-id')
  const formScriptImportation = $('#taches-importations-form-detail')
  const btnSaveScriptImportation = $('#btn-taches-importations-detail-save')

  // clear validation classes
  inputLabel[0].classList.remove('is-valid', 'is-invalid')
  inputScript[0].classList.remove('is-valid', 'is-invalid')
  inputOptions[0].classList.remove('is-valid', 'is-invalid')
  inputLastId[0].classList.remove('is-valid', 'is-invalid')
  formScriptImportation[0].classList.remove('was-validated')

  const fillInput = (inputValues) => {
    inputLabel.val(inputValues.libelle)
    inputScript.val(inputValues.script)
    inputOptions.val(inputValues.options)
    inputLastId.val(inputValues.lastId)
  }
  
  btnSaveScriptImportation.attr('data-url', url)
  if (id && detailUrl) {
    btnSaveScriptImportation.attr('data-method', 'PUT')
    axios.get(detailUrl)
      .then(res => {
        const options = []

        if (res.data.options) {
          options = res.data.options.map(option => option.replace(/,/g, ',,'))
        }

        fillInput({
          ...res.data,
          options: options.join(',')
        })
      })
  } else {
    btnSaveScriptImportation.attr('data-method', 'POST')
    fillInput({
      label: '',
      script: '',
      options: '',
      lastId: '',
    })
  }
}

function hideTachesImportationsModal() {
  $('#taches-importations-modal').modal('hide')
}

$(function () {
  const tblDom = $("#tbl-taches-importations")
  const btnNew = $('#btn-taches-importations-new')
  const btnGenerateDefault = $('#btn-taches-importations-generate')
  const btnSaveScriptImportation = $('#btn-taches-importations-detail-save')
  const formScriptImportation = $('#taches-importations-form-detail')

  const importationDatatable = tblDom.DataTable({
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
    order: [[1, 'desc']],
    columnDefs: [
      {
        targets: 0,
        data: "id",
        width: '5%',
        orderable: false,
        render: function (data, type, row, meta) {
          return `
            <div class="custom-control custom-checkbox">
              <input class="custom-control-input custom-control-input-secondary taches-importations-selection" type="checkbox" id="taches-importations-selection-${data}" value="${data}">
              <label for="taches-importations-selection-${data}" class="custom-control-label"></label>
            </div>
          `
        }
      },
      {
        targets: 1,
        data: "id",
        width: '5%',
      },
      {
        targets: 2,
        data: "label",
        width: '15%',
      },
      {
        targets: 3,
        data: "script",
        width: '18%',
      },
      {
        targets: 4,
        data: "options",
        width: '12%',
        orderable: false,
        render: function (data, type, row, meta) {
          if (data) {
            const options = [];
            for (const optionName in data) {
              if (!Object.hasOwn(data, optionName)) continue
              
              options.push(`${optionName}=${data[optionName]}`)
            }

            return options.join(', ')
          }

          return ''
        }
      },
      {
        targets: 5,
        data: "status",
        width: '6%',
        render: function (data, type, row, meta) {
          const statusMap = [
            'CREE',
            'COMMENCEE',
            'ERREUR',
            'TERMINEE'
          ]

          return data && statusMap[data] ? statusMap[data] : ''
        }
      },
      {
        targets: 6,
        data: "lastId",
        width: '5%',
      },
      {
        targets: 7,
        data: "lastCount",
        width: '7%',
      },
      {
        targets: 8,
        data: "executedAt",
        width: '12%',
        render: function (data, type, row, meta) {
          if (data) {
            return formatDate(data)
          }

          return ''
        }
      },
      {
        targets: 9,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '15%',
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id'])
          const executeUrl = getCleanUrl(tblDom.data('execute-url'), row['id'])
          const detailUrl = getCleanUrl(tblDom.data('detail-url'), row['id'])
          return (
            "<div>" +
            '<a class="btn btn-sm btn-outline-secondary ml-2 btn-view-output" data-url="'+ detailUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-eye"></i></a>' +
            '<a class="btn btn-sm btn-outline-secondary ml-2 btn-execute" data-url="'+ executeUrl +'"><i class="fas fa-sync"></i></a>' +
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
            "</div>"
          )
        },
      },
    ],
    serverSide: true,
    ajax: function (data, callback) {
      axios.get(tblDom.data("url"), { params: data })
        .then(response => callback(response.data))
        .catch(() => callback({ data: [] }))
    },
  })

  // delete
  $(document).on('deletedEvent', function() {
    importationDatatable.draw()
  })

  // open new modal
  btnNew.on('click', function (e) {
    openTachesImportationsModal(null,  $(this).data('url'))
  })

  // generate default
  btnGenerateDefault.on('click', (e) => {
    const url = btnGenerateDefault.data('url')
    axios.post(url)
      .then(() => {
        importationDatatable.draw()
      }).catch(() => {})
  })

  // save script form
  btnSaveScriptImportation.on('click', function(e) {
    if (!formScriptImportation[0].checkValidity()) {
      e.preventDefault()
      e.stopPropagation()
      formScriptImportation[0].classList.add('was-validated')
    } else {
      const inputs = {
        label: $('#taches-importations-label').val(),
        script: $('#taches-importations-script').val(),
        options: $('#taches-importations-options').val(),
        lastId: $('#taches-importations-last-id').val(),
      }

      // change options
      const tmpChars = '_____'
      const newOption = inputs.options ? inputs.options.replace(/,,/g, tmpChars) : ''
      const optionListe = newOption.split(',').map(option => option.replace(tmpChars, ',').split('='))
      inputs.options = {}

      optionListe.forEach(e => {
        if (e.length > 0 && e[0]) {
          inputs.options[e[0]] = e.length > 1 ? e[1] : '';
        }
      })

      axios.request({
        url: btnSaveScriptImportation.attr('data-url'),
        method: btnSaveScriptImportation.attr('data-method'),
        data: inputs,
      })
        .then(() => {
          hideTachesImportationsModal()
          importationDatatable.draw()
        })
    }
  })

  // view output
  $(document).on('click', '.btn-view-output', function (e) {
    e.preventDefault()

    axios.get($(this).attr('data-url'))
      .then(res => {
        $('#taches-importations-output-modal').modal('show')
        $('#taches-importations-ouput').val(res.data.output ? res.data.output : '')
      })
  })

  // execute
  $(document).on('click', '.btn-execute', function (e) {
    e.preventDefault()

    axios.get($(this).attr('data-url'))
      .then(() => {
        importationDatatable.draw()
      })
  })
})
