function openAdminEmailModal(id, url, detailUrl) {
  $('#admin-email-modal').modal('show')
  const inputName = $('#admin-email-name')
  const inputEmail = $('#admin-email-email')
  const inputEvents = $('#admin-email-events')
  const inputStatut = $('#admin-email-status')
  const formAdminEmail = $('#admin-email-form-detail')
  const btnSaveAdminEmail = $('#btn-admin-email-detail-save')

  // remove errors
  inputName[0].classList.remove('is-valid', 'is-invalid')
  inputEmail[0].classList.remove('is-valid', 'is-invalid')
  inputEvents[0].classList.remove('is-valid', 'is-invalid')
  inputStatut[0].classList.remove('is-valid', 'is-invalid')
  formAdminEmail[0].classList.remove('was-validated')
  formAdminEmail[0].reset()
  
  btnSaveAdminEmail.attr('data-url', url)
  if (id && detailUrl) {
    btnSaveAdminEmail.attr('data-method', 'POST')
    axios.get(detailUrl)
      .then(res => {
        inputName.val(res.data.name)
        inputEmail.val(res.data.email)
        inputStatut.prop('checked', res.data.status == 1)
        inputEvents.val(res.data.events)
        inputEvents.trigger('change')
      })
  } else {
    btnSaveAdminEmail.attr('data-method', 'POST')
    // clear inputs
    inputName.val('')
    inputEmail.val('')
    inputStatut.prop('checked', false)
    inputEvents.val([])
    inputEvents.trigger('change')
  }
}

function hideAdminEmailModal() {
  $('#admin-email-modal').modal('hide')
}

$(function () {
  const tblDom = $("#tbl-admin-emails")
  const btnNew = $('#btn-admin-email-new')
  const btnSaveAdminEmail = $('#btn-admin-email-detail-save')
  const formAdminEmail = $('#admin-email-form-detail')

  // select events
  $('#admin-email-events').select2({
    theme: 'bootstrap4',
    allowClear: true,
    placeholder: 'Choisir une ou des option(s)'
  })

  const adminEmailDatatable = tblDom.DataTable({
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
    columnDefs: [
      {
        targets: 0,
        data: "id",
        width: '10%',
      },
      {
        targets: 1,
        data: "name",
        width: '20%',
      },
      {
        targets: 2,
        data: "email",
        width: '20%',
      },
      {
        targets: 3,
        data: 'events',
        orderable: false,
        width: '34%',
        render: function (data, type, row, meta) {
          if (!data) {
            return ''
          }
          return data.join(', ')
        }
      },
      {
        targets: 4,
        data: 'status',
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
    serverSide: true,
    ajax: {
      url: tblDom.data("url"),
      type: "GET",
    },
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
    openAdminEmailModal(id, url, detailUrl)
  })

  // the modal
  btnNew.on('click', function (e) {
    openAdminEmailModal(null, $(this).data('url'))
  })

  btnSaveAdminEmail.on('click', function(e) {
    if (!formAdminEmail[0].checkValidity()) {
      e.preventDefault()
      e.stopPropagation()
      formAdminEmail[0].classList.add('was-validated')
    } else {
      const inputs = {
        name: $('#admin-email-name').val(),
        email: $('#admin-email-email').val(),
        events: $('#admin-email-events').val(),
        status: $('#admin-email-status').prop('checked'),
      }

      axios.request({
        url: btnSaveAdminEmail.attr('data-url'),
        method: btnSaveAdminEmail.attr('data-method'),
        data: inputs,
      })
        .then(() => {
          hideAdminEmailModal()
          adminEmailDatatable.draw()
        })
    }
  })
})
