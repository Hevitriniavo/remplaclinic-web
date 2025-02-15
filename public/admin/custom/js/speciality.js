function openSpecialityModal(id, url, detailUrl) {
  $('#speciality-modal').modal('show');
  const inputName = $('#speciality-name');
  const inputSepcialityParent = $('#speciality-parent-id');
  const formSpeciality = $('#speciality-form-detail');
  const btnSaveSpeciality = $('#btn-speciality-detail-save');

  inputName[0].classList.remove('is-valid', 'is-invalid');
  inputSepcialityParent[0].classList.remove('is-valid', 'is-invalid');
  formSpeciality[0].classList.remove('was-validated');
  
  if (id && detailUrl) {
    btnSaveSpeciality.attr('data-url', url);
    btnSaveSpeciality.attr('data-method', 'PUT');
    axios.get(detailUrl)
      .then(res => {
        inputName.val(res.data.name);
        if (res.data.specialityParent) {
          inputSepcialityParent.val(res.data.specialityParent.id);
        } else {
          inputSepcialityParent.val('');
        }
        inputSepcialityParent.change();
      })
  } else {
    btnSaveSpeciality.attr('data-method', 'POST');
    inputName.val('');
    inputSepcialityParent.val('');
    inputSepcialityParent.change();
  }
}

function hideSpecialityModal() {
  $('#speciality-modal').modal('hide');
}

function getCleanUrl(url, id)
{
  let result = url;
  if (result) {
    result = result.replace('0000000000', id);
  }
  return result;
}

$(function () {
  const tblDom = $("#tbl-specilities");
  const btnNew = $('#btn-speciality-new');
  const btnSaveSpeciality = $('#btn-speciality-detail-save');
  const formSpeciality = $('#speciality-form-detail');

  const specialityDatatable = tblDom.DataTable({
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
        width: '40%',
      },
      {
        targets: 2,
        data: 'name',
        width: '40%',
        render: function (data, type, row, meta) {
          return row.specialityParent ? row.specialityParent.name : '';
        }
      },
      {
        targets: 3,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '10%',
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id']);
          const editUrl = getCleanUrl(tblDom.data('edit-url'), row['id']);
          const detailUrl = getCleanUrl(tblDom.data('detail-url'), row['id']);
          return (
            "<div>" +
            '<a class="btn btn-sm btn-outline-info btn-edit" data-url="'+ editUrl +'" data-detail-url="'+ detailUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-edit"></i></a>' +
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
            "</div>"
          );
        },
      },
    ],
    serverSide: true,
    ajax: {
      url: tblDom.data("url"),
      type: "GET",
    },
  });

  // delete
  $(document).on('deletedEvent', function() {
    specialityDatatable.draw();
  })

  $(document).on('click', '.btn-edit', function() {
    const btn = $(this);
    const id = btn.data('id');
    const url = getCleanUrl(btn.data('url'), id);
    const detailUrl = getCleanUrl(btn.data('detail-url'), id);
    openSpecialityModal(id, url, detailUrl);
  })

  // the modal
  btnNew.on('click', function (e) {
    openSpecialityModal();
  });

  $('.speciality-parent').select2({
    theme: 'bootstrap4',
    allowClear: true,
    placeholder: 'Choisir une spécialité'
  });

  btnSaveSpeciality.on('click', function(e) {
    if (!formSpeciality[0].checkValidity()) {
      e.preventDefault()
      e.stopPropagation()
      formSpeciality[0].classList.add('was-validated');
    } else {
      const inputs = new FormData(formSpeciality[0]);
      const payload = {
        name: inputs.get('name'),
      }
      if (inputs.get('specialityParent')) {
        payload.specialityParent = parseInt(inputs.get('specialityParent'));
      }
      axios.request({
        url: btnSaveSpeciality.attr('data-url'),
        method: btnSaveSpeciality.attr('data-method'),
        data: payload,
      })
        .then(() => {
          hideSpecialityModal()
          specialityDatatable.draw();
        });
    }
  });
});
