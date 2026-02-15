import { initDataTable, getCleanUrl, formatDate } from 'admin-app'

$(function () {
  const tblDom = $("#tbl-inbox-emails");

  const inboxEmailsDatatable = initDataTable('', tblDom, null, {
    order: [[1, 'desc']],
    columnDefs: [
      {
        targets: 0,
        data: "id",
        width: '3%',
        orderable: false,
        render: function (data, type, row, meta) {
          return `
            <div class="custom-control custom-checkbox">
              <input class="custom-control-input custom-control-input-secondary inbox-email-selection" type="checkbox" id="inbox-email-selection-${data}" value="${data}">
              <label for="inbox-email-selection-${data}" class="custom-control-label"></label>
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
        data: "event",
        width: '10%',
      },
      {
        targets: 3,
        data: 'sentAt',
        width: '12%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }

          return formatDate(data);
        }
      },
      {
        targets: 4,
        data: "subject",
        orderable: false,
        width: '20%',
      },
      {
        targets: 5,
        data: "html",
        width: '6%',
        orderable: false,
        render: function (data, type, row, meta) {
          return data ? 'HTML' : 'TEXT'
        }
      },
      {
        targets: 6,
        data: 'sender',
        width: '8%',
        orderable: false,
        render: function (data, type, row, meta) {
          if (!data) {
            return '';
          }

          return (
            "<div>" + data.email + "</div>"
          );
        }
      },
      {
        targets: 7,
        data: 'target',
        width: '13%',
      },
      {
        targets: 8,
        data: 'cc',
        width: '13%',
        orderable: false,
        render: function (data, type, row, meta) {
          const result = []
          // cc
          if (row.cc) {
            result.push(row.cc)
          }

          // bcc
          if (row.bcc) {
            result.push(row.bcc)
          }

          return (
            result.length > 0 ? "<div>" + result.join(' - ') + "</div>" : ''
          );
        }
      },
      {
        targets: 9,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '10%',
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id']);
          const detailUrl = getCleanUrl(tblDom.data('detail-url'), row['id']);
          return (
            "<div>" +
            '<a class="btn btn-sm btn-outline-info btn-edit" href="'+ detailUrl +'" target="_blank"><i class="fas fa-eye"></i></a>' +
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
            "</div>"
          );
        },
      },
    ],
  })

  // delete
  $(document).on('deletedEvent', function() {
    inboxEmailsDatatable.draw();
  });
});
