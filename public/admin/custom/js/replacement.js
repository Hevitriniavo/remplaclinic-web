function getCleanUrl(url, id)
{
  let result = url;
  if (result) {
    result = result.replace('0000000000', id);
  }
  return result;
}

$(function () {
  const tblDom = $("#tbl-replacements");

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
        width: '30%',
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
        data: "email",
        width: '20%',
      },
      {
        targets: 4,
        data: 'createAt',
        width: '15%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }

          const date = new Date(data);
          const formatter = new Intl.DateTimeFormat("fr-FR", {
              day: "2-digit",
              month: "2-digit",
              year: "numeric",
              hour: '2-digit',
              minute: '2-digit'
          });
          return formatter.format(date);
        }
      },
      {
        targets: 5,
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
        targets: 6,
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
