function openPartenaireModal(id, url, detailUrl) {
  $('#partenaire-modal').modal('show');
  const inputName = $('#partenaire-name');
  const formPartenaire = $('#partenaire-form-detail');
  const btnSavePartenaire = $('#btn-partenaire-detail-save');

  inputName[0].classList.remove('is-valid', 'is-invalid');
  formPartenaire[0].classList.remove('was-validated');
  formPartenaire[0].reset();
  
  btnSavePartenaire.attr('data-url', url);
  if (id && detailUrl) {
    btnSavePartenaire.attr('data-method', 'POST');
    axios.get(detailUrl)
      .then(res => {
        inputName.val(res.data.name);
      })
  } else {
    btnSavePartenaire.attr('data-method', 'POST');
  }
}

function hidePartenaireModal() {
  $('#partenaire-modal').modal('hide');
}

$(function () {
  const tblDom = $("#tbl-partenaires");
  const btnNew = $('#btn-partenaire-new');
  const btnSavePartenaire = $('#btn-partenaire-detail-save');
  const formPartenaire = $('#partenaire-form-detail');

  const partenaireDatatable = tblDom.DataTable({
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
        data: 'logo',
        orderable: false,
        width: '40%',
        render: function (data, type, row, meta) {
          const imageUrl = tblDom.data('image-url') + '/' + row['logo'];
          return (
            '<img src="'+ imageUrl + '" style="max-width: 200px;max-height: 200px">'
          );
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
    ajax: function (data, callback) {
      axios.get(tblDom.data("url"), { params: data })
        .then(response => callback(response.data))
        .catch(() => callback({ data: [] }))
    },
  });

  // delete
  $(document).on('deletedEvent', function() {
    partenaireDatatable.draw();
  })

  $(document).on('click', '.btn-edit', function() {
    const btn = $(this);
    const id = btn.data('id');
    const url = getCleanUrl(btn.data('url'), id);
    const detailUrl = getCleanUrl(btn.data('detail-url'), id);
    openPartenaireModal(id, url, detailUrl);
  })

  // the modal
  btnNew.on('click', function (e) {
    openPartenaireModal(null, $(this).data('url'));
  });

  btnSavePartenaire.on('click', function(e) {
    if (!formPartenaire[0].checkValidity()) {
      e.preventDefault()
      e.stopPropagation()
      formPartenaire[0].classList.add('was-validated');
    } else {
      const inputs = new FormData(formPartenaire[0]);
      axios.request({
        url: btnSavePartenaire.attr('data-url'),
        method: btnSavePartenaire.attr('data-method'),
        data: inputs,
      })
        .then(() => {
          hidePartenaireModal()
          partenaireDatatable.draw();
        });
    }
  });
});
