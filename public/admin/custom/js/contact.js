import { initDataTable, formatDate, getCleanUrl } from 'admin-app'

$(function () {
  const tblDom = $("#tbl-contacts");
  const mapTypes = {
    '86' : 'Contact',
    '98' : 'Contact',
    '992' : 'Assistance',
    '1030' : 'Ouverture de compte',
    '1085' : 'Installation',
  }

  const contactDatatable = initDataTable('', tblDom, null, {
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
              <input class="custom-control-input custom-control-input-secondary contact-selection" type="checkbox" id="contact-selection-${data}" value="${data}">
              <label for="contact-selection-${data}" class="custom-control-label"></label>
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
        data: "contact_type",
        width: '10%',
        render: function(data, type, row, meta) {
          return mapTypes[data] ? mapTypes[data] : 'Contact'
        }
      },
      {
        targets: 3,
        data: 'submitted_at',
        width: '12%',
        render: function(data, type, row, meta) {
          if (!data) {
            return "";
          }

          return formatDate(data, true);
        }
      },
      {
        targets: 4,
        data: "remote_addr",
        width: '10%',
      },
      {
        targets: 5,
        data: "name",
        width: '15%',
      },
      {
        targets: 6,
        data: "surname",
        width: '10%',
      },
      {
        targets: 7,
        data: "email",
        width: '13%',
      },
      {
        targets: 8,
        data: "telephone",
        width: '12%',
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
            '<a class="btn btn-sm btn-outline-info btn-contact-view" href="'+ detailUrl +'"><i class="fas fa-eye"></i></a>' +
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
            "</div>"
          );
        },
      },
    ],
  })

  // delete
  $(document).on('deletedEvent', function() {
    contactDatatable.draw();
  });

  // view detail
  $(document).on('click', '.btn-contact-view', function (e) {
    e.preventDefault()

    axios.get($(this).attr('href'))
      .then(res => {
        const data = {
          name: res.data.name,
          surname: res.data.surname,
          email: res.data.email,
          telephone: res.data.telephone,
          contact_type: mapTypes[res.data.contact_type] ? mapTypes[res.data.contact_type] : 'Contact',
          submitted_at: formatDate(res.data.submitted_at, true),
          remote_addr: res.data.remote_addr,
          object: res.data.object ? res.data.object.join(', ') : '',
          fonction: res.data.fonction,
          message: res.data.message
        }

        for (const key in data) {
          if (!Object.hasOwn(data, key)) continue
          
          const element = data[key]
          
          const el = document.querySelector('.contact-detail-' + key)
          if (el) {
            el.innerText = element ? element : 'N/R'
          }
        }

        $('#conatct-detail-modal').modal('show')
      }).catch(() => {})
  })
});
