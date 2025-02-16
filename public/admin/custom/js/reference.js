function getCleanUrl(url, id)
{
  let result = url;
  if (result) {
    result = result.replace('0000000000', id);
  }
  return result;
}

$(function () {
  const tblDom = $("#tbl-references");

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
        width: '5%',
      },
      {
        targets: 1,
        data: "clinicName",
        width: '13%',
      },
      {
        targets: 2,
        data: "specialityName",
        width: '12%',
      },
      {
        targets: 3,
        data: 'title',
        width: '20%',
      },
      {
        targets: 4,
        data: 'body',
        width: '40%',
        render: function (data, type, row, meta) {
          return (
            "<div>" + data + "</div>"
          );
        }
      },
      {
        targets: 5,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '10%',
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id']);
          const detailUrl = getCleanUrl(tblDom.data('detail-url'), row['id']);
          return (
            "<div>" +
            '<a class="btn btn-sm btn-outline-info btn-edit" href="'+ detailUrl +'"><i class="fas fa-edit"></i></a>' +
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
  });
});
