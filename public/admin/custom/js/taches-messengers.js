import { initDataTable, getCleanUrl, formatDate } from 'admin-app'

$(function () {
  const tblDom = $("#tbl-taches-messengers")
  const btnCheck = $('#btn-messengers-check')

  const replacementDetailUrl = tblDom.data('detailReplacementUrl')
  const installationDetailUrl = tblDom.data('detailReplacementUrl')

  const parseDecodedMessage = (decoded, type, row) => {
    const result = {
      eventName: '',
      payload: ''
    }
    if (!decoded || !type) {
      return result
    }

    switch(type) {
      case 'App\\Message\\Request\\RequestMessage':

        const requestType = decoded.requestType === 'installation' ? "Proposition d'installation" : 'Demande de remplacement'
        const detailUrl = decoded.requestType === 'installation' ? installationDetailUrl : replacementDetailUrl
        const payload = [
          `<div><span>${requestType}: </span><a href="${ getCleanUrl(detailUrl, decoded.requestId) }"><span class="text-bold">${decoded.requestId}</span></a></div>`
        ]

        if (decoded.users && decoded.users.length > 0) {
          payload.push(`<div><span>Liste des utilisateurs: </span><span class="text-bold">${decoded.users.join(', ')}</span></div>`)
        }

        result.eventName = decoded.eventName
        result.payload = payload.join('')

        break
      case 'App\\Message\\Ping\\PingMessage':

        result.eventName = 'ping:pong'
        result.payload = `<div><span>Token: </span><span class="text-bold">${decoded.target}</span></div>`

        break
      case 'App\\Message\\Console\\RunCommandMessage':

        result.eventName = 'console command'
        result.payload = `<div><span class="text-bold">${row.decoded_str}</span></div>`

        break
      default:
        break
    }

    return result
  }

  const tachesMessagesDatatable = initDataTable('', tblDom, null, {
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
              <input class="custom-control-input custom-control-input-secondary taches-messengers-selection" type="checkbox" id="taches-messengers-selection-${data}" value="${data}">
              <label for="taches-messengers-selection-${data}" class="custom-control-label"></label>
            </div>
          `
        }
      },
      {
        targets: 1,
        data: "id",
        width: '9%',
      },
      {
        targets: 2,
        data: "decoded",
        orderable: false,
        width: '25%',
        render: function (data, type, row, meta) {
          return parseDecodedMessage(data, row.type, row).eventName
        }
      },
      {
        targets: 3,
        data: "decoded",
        orderable: false,
        width: '40%',
        render: function (data, type, row, meta) {
          return parseDecodedMessage(data, row.type, row).payload
        }
      },
      {
        targets: 4,
        data: "created_at",
        width: '12%',
        render: function (data, type, row, meta) {
          return formatDate(data)
        }
      },
      {
        targets: 5,
        data: "delivered_at",
        width: '12%',
        render: function (data, type, row, meta) {
          return data ? formatDate(data) : ''
        }
      },
      {
        targets: 6,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '7%',
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id'])
          return (
            "<div>" +
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
            "</div>"
          )
        },
      },
    ],
  })

  // delete
  $(document).on('deletedEvent', function() {
    tachesMessagesDatatable.draw()
  })

  btnCheck.on('click', function(e) {
    axios.get(btnCheck.data('url'))
      .then((res) => {
        tachesMessagesDatatable.draw()

        const statutContainer = $('#taches-messages-status')
        const typeAlert = res.data.status === 'UP' ? 'alert-success' : 'alert-danger'
        const contentAlert = res.data.status === 'UP' ? 'Worker up and running...' : 'Worker down or busy...'

        statutContainer.html(`<div class="alert text-center ${typeAlert}">${contentAlert}</div>`)

        setTimeout(() => {
          statutContainer.html('')
        }, 5000)
      })
  })
})
