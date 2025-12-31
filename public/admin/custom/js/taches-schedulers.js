function openTachesSchedulersModal(id, url, detailUrl) {
  $('#taches-schedulers-modal').modal('show')
  const inputLabel = $('#taches-schedulers-label')
  const inputScript = $('#taches-schedulers-script')
  const inputOptions = $('#taches-schedulers-options')
  const inputTime = $('#taches-schedulers-time')
  const formScriptScheduler = $('#taches-schedulers-form-detail')
  const btnSaveScriptScheduler = $('#btn-taches-schedulers-detail-save')

  // clear validation classes
  inputLabel[0].classList.remove('is-valid', 'is-invalid')
  inputScript[0].classList.remove('is-valid', 'is-invalid')
  inputOptions[0].classList.remove('is-valid', 'is-invalid')
  inputTime[0].classList.remove('is-valid', 'is-invalid')
  formScriptScheduler[0].classList.remove('was-validated')

  const fillInput = (inputValues) => {
    inputLabel.val(inputValues.libelle)
    inputScript.val(inputValues.script)
    inputOptions.val(inputValues.options)
    inputTime.val(inputValues.time)
  }
  
  btnSaveScriptScheduler.attr('data-url', url)
  if (id && detailUrl) {
    btnSaveScriptScheduler.attr('data-method', 'PUT')
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
    btnSaveScriptScheduler.attr('data-method', 'POST')
    fillInput({
      label: '',
      script: '',
      options: '',
      time: '',
    })
  }
}

function hideTachesSchedulersModal() {
  $('#taches-schedulers-modal').modal('hide')
}

$(function () {
  const tblDom = $("#tbl-taches-schedulers")
  const btnNew = $('#btn-taches-schedulers-new')
  const btnGenerateDefault = $('#btn-taches-schedulers-generate')
  const btnSaveScriptScheduler = $('#btn-taches-schedulers-detail-save')
  const formScriptScheduler = $('#taches-schedulers-form-detail')

  const schedulerDatatable = tblDom.DataTable({
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
              <input class="custom-control-input custom-control-input-secondary taches-schedulers-selection" type="checkbox" id="taches-schedulers-selection-${data}" value="${data}">
              <label for="taches-schedulers-selection-${data}" class="custom-control-label"></label>
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
        width: '15%',
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
        data: "time",
        width: '12%',
      },
      {
        targets: 6,
        data: "executedAt",
        width: '15%',
      },
      {
        targets: 7,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '15%',
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id'])
          const executeUrl = getCleanUrl(tblDom.data('execute-url'), row['id'])

          return (
            "<div>" +
            '<a class="btn btn-sm btn-outline-default ml-2 btn-execute" data-url="'+ executeUrl +'"><i class="fas fa-sync"></i></a>' +
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
            "</div>"
          )
        },
      },
    ],
    serverSide: true,
    ajax: {
      url: tblDom.data("url"),
      type: "GET",
    },
  })

  // delete
  $(document).on('deletedEvent', function() {
    schedulerDatatable.draw()
  })

  // open new modal
  btnNew.on('click', function (e) {
    openTachesSchedulersModal(null,  $(this).data('url'))
  })

  // generate default
  btnGenerateDefault.on('click', (e) => {
    const url = btnGenerateDefault.data('url')
    axios.post(url)
      .then(() => {
        schedulerDatatable.draw()
      }).catch(() => {})
  })

  // save script form
  btnSaveScriptScheduler.on('click', function(e) {
    if (!formScriptScheduler[0].checkValidity()) {
      e.preventDefault()
      e.stopPropagation()
      formScriptScheduler[0].classList.add('was-validated')
    } else {
      const inputs = {
        label: $('#taches-schedulers-label').val(),
        script: $('#taches-schedulers-script').val(),
        options: $('#taches-schedulers-options').val(),
        time: $('#taches-schedulers-time').val(),
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
        url: btnSaveScriptScheduler.attr('data-url'),
        method: btnSaveScriptScheduler.attr('data-method'),
        data: inputs,
      })
        .then(() => {
          hideTachesSchedulersModal()
          schedulerDatatable.draw()
        })
    }
  })
})
