function openRegionModal(id, url, detailUrl) {
  $('#region-modal').modal('show');
  const inputName = $('#region-name');
  const formRegion = $('#region-form-detail');
  const btnSaveRegion = $('#btn-region-detail-save');

  inputName[0].classList.remove('is-valid', 'is-invalid');
  formRegion[0].classList.remove('was-validated');
  
  btnSaveRegion.attr('data-url', url);
  if (id && detailUrl) {
    btnSaveRegion.attr('data-method', 'PUT');
    axios.get(detailUrl)
      .then(res => {
        inputName.val(res.data.name);
      })
  } else {
    btnSaveRegion.attr('data-method', 'POST');
    inputName.val('');
  }
}

function hideRegionModal() {
  $('#region-modal').modal('hide');
}

$(function () {
  const tblDom = $("#tbl-specilities");
  const btnNew = $('#btn-region-new');
  const btnSaveRegion = $('#btn-region-detail-save');
  const formRegion = $('#region-form-detail');

  const regionDatatable = tblDom.DataTable({
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
        width: '80%',
      },
      {
        targets: 2,
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
    regionDatatable.draw();
  })

  $(document).on('click', '.btn-edit', function() {
    const btn = $(this);
    const id = btn.data('id');
    const url = getCleanUrl(btn.data('url'), id);
    const detailUrl = getCleanUrl(btn.data('detail-url'), id);
    openRegionModal(id, url, detailUrl);
  })

  // the modal
  btnNew.on('click', function (e) {
    openRegionModal(null,  $(this).data('url'));
  });

  btnSaveRegion.on('click', function(e) {
    if (!formRegion[0].checkValidity()) {
      e.preventDefault()
      e.stopPropagation()
      formRegion[0].classList.add('was-validated');
    } else {
      const inputs = new FormData(formRegion[0]);
      const payload = {
        name: inputs.get('name'),
      }
      axios.request({
        url: btnSaveRegion.attr('data-url'),
        method: btnSaveRegion.attr('data-method'),
        data: payload,
      })
        .then(() => {
          hideRegionModal()
          regionDatatable.draw();
        });
    }
  });
});
