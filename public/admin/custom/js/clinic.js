$(function () {
  const tblDom = $("#tbl-clinics");

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
        data: "status",
        width: '5%',
        render: function (data, type, row, meta) {
          return data == 1 ? 'Actif' : 'Bloqué';
        }
      },
      {
        targets: 2,
        data: "name",
        width: '15%',
        render: function (data, type, row, meta) {
          if (!data) {
            return '';
          }
          
          return (
            "<div>" + data + " " + row['surname'] + "</div>"
          );
        }
      },
      {
        targets: 3,
        data: 'establishment',
        width: '15%',
        render: function (data, type, row, meta) {
          if (!data) {
            return '';
          }

          return (
            "<div>" + data.name + "</div>"
          );
        }
      },
      {
        targets: 4,
        data: "email",
        width: '10%',
      },
      {
        targets: 5,
        data: 'createAt',
        width: '12%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }

          return formatDate(data);
        }
      },
      {
        targets: 6,
        data: 'speciality',
        width: '15%',
        render: function (data, type, row, meta) {
          if (!data) {
            return '';
          }

          return (
            "<div>" + data.name + "</div>"
          );
        }
      },
      {
        targets: 7,
        data: 'subscription',
        width: '10%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }
          return formatDate(data.endAt, false);
        }
      },
      {
        targets: 8,
        data: 'subscription',
        width: '5%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }
          return data.installationCount ? data.installationCount : 0;
        }
      },
      {
        targets: 9,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '8%',
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
