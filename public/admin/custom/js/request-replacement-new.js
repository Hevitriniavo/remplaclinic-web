const { createApp, ref, onMounted, nextTick } = Vue

const app = createApp({
  setup() {
    const requesting = ref(false)
    const requestData = ref({
      id: null,
      title: null,
      applicant: null,
      speciality: null,
      region: null,
      positionCount: null,
      remuneration: null,
      retrocession: null,
      startedAt: null,
      endAt: null,
      replacementType: null,
      accomodationIncluded: null,
      transportCostRefunded: null,
      comment: null,
    })
    const requestValidators = {
      applicant: (value) => !!value,
      speciality: (value) => !!value,
      region: (value) => !!value,
      positionCount: (value) => parseInt(value) > 0 && parseInt(value) <= 10,
      remuneration: (value, form) => !!value || !!form.retrocession,
      retrocession: (value, form) => !!value || !!form.remuneration,
      startedAt: (value) => !!value,
      endAt: (value) => !!value,
      replacementType: (value) => value === "pontual" || value === "regular",
    }
    const requestErrors = ref({
      validated: false,
      error: false,
      details: {},
    })

    const formEl = ref(null)
    const formNavigationTab = ref({
      active: 'modifier', // modifier - date-envoi - personne-contacte
    })

    const requestDateEnvois = ref([])

    function getErrorClass(fieldName) {
      if (requestErrors.value.error && requestErrors.value.validated) {
        return requestErrors.value.details[fieldName]
          ? "is-invalid"
          : "is-valid"
      }
      return ""
    }

    function validateFormData(isUpdate = false) {
      requestErrors.value.validated = true
      const formData = requestData.value
      let hasError = false
      for (const key in formData) {
        if (Object.prototype.hasOwnProperty.call(formData, key)) {
          if (Object.prototype.hasOwnProperty.call(requestValidators, key)) {
            const cb = requestValidators[key]
            if (!cb(formData[key], formData)) {
              hasError = true
              requestErrors.value.details[key] = true
            } else {
              requestErrors.value.details[key] = false
            }
          }
        }
      }

      if (isUpdate && !formData.title) {
        hasError = true
        requestErrors.value.details.title = true
      }

      requestErrors.value.error = hasError

      return hasError
    }

    function toFormData() {
      const formData = requestData.value
      const result = new FormData()
      for (const key in formData) {
        if (Object.prototype.hasOwnProperty.call(formData, key)) {
          const value = formData[key]
          if (Array.isArray(value)) {
            let index = 0
            for (const aValue of value) {
              result.append(`${key}[${index}]`, aValue)
              index++
            }
          } else if (value) {
            result.append(key, value)
          }
        }
      }
      return result
    }

    async function getRequestDetail() {
      const url = $('#root').data('detailUrl')

      if (!url) {
        // create new request
        return Promise.resolve()
      }

      const response = await axios.get(url)

      if (response.data) {
        requestData.value = {
          id: response.data.id,
          title: response.data.title,
          applicant: response.data.applicant.id,
          speciality: response.data.speciality.id,
          region: response.data.region.id,
          positionCount: response.data.positionCount,
          remuneration: response.data.remuneration,
          retrocession: response.data.retrocession,
          startedAt: formatDate(response.data.startedAt, false),
          endAt: formatDate(response.data.endAt, false),
          replacementType: response.data.replacementType,
          accomodationIncluded: response.data.accomodationIncluded,
          transportCostRefunded: response.data.transportCostRefunded,
        }

        jQuery("#request-comment").summernote("code", response.data.comment)

        // update date envoi
        const requestDateEnvoisList = response.data.sentDates || []
        requestDateEnvois.value = requestDateEnvoisList.map(dateEnvoi => formatDate(dateEnvoi))
      }
    }

    function onCreateRequest() {
      openSelectUserModal()
      // requestData.value.comment = jQuery("#request-comment").summernote("code")

      // const payload = toFormData()

      // if (!validateFormData()) {
      //   requesting.value = true
      //   const action = formEl.value.dataset.url
      //   axios
      //     .post(action, payload)
      //     .then(() => {
      //       jQuery("#btn-request-list")[0].click()
      //     })
      //     .catch(() => {
      //       requesting.value = false
      //     })
      // }
    }

    function getDateDesEnvois () {
      // data loaded with getRequestDetail

      return Promise.resolve()
    }

    function getPersonneContactes () {
      return new Promise((resolve) => {
        const tblDom = $("#tbl-personne-contacte")
        const url = getCleanUrl(tblDom.data("listUrl"), requestData.value.id)

        const personneContacteDatatable = tblDom.DataTable({
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
              data: "user.name",
              width: '25%',
            },
            {
              targets: 1,
              data: "user.surname",
              width: '25%',
            },
            {
              targets: 2,
              data: 'user.speciality.name',
              width: '25%',
              // render: function (data, type, row, meta) {
              //   return row.specialityParent ? row.specialityParent.name : ''
              // }
            },
            {
              targets: 3,
              data: 'statut',
              width: '15%',
              // render: function (data, type, row, meta) {
              //   return row.specialityParent ? row.specialityParent.name : ''
              // }
            },
            {
              targets: 4,
              data: "id",
              orderable: false,
              className: "text-right",
              width: '10%',
              render: function (data, type, row, meta) {
                const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id'])
                const editUrl = getCleanUrl(tblDom.data('edit-url'), row['id'])
                return (
                  "<div>" +
                  '<a class="btn btn-sm btn-outline-info btn-answer" data-url="'+ editUrl +'" data-statut="0" data-id="'+ row['id'] +'"><i class="fas fa-reply"></i></a>' +
                  '<a class="btn btn-sm btn-outline-info btn-answer" data-url="'+ editUrl +'" data-statut="1" data-id="'+ row['id'] +'"><i class="fas fa-info"></i></a>' +
                  '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
                  "</div>"
                )
              },
            },
          ],
          serverSide: true,
          ajax: {
            url: url,
            type: "GET",
          },
        })

        // delete
        $(document).on('deletedEvent', function() {
          personneContacteDatatable.draw()
        })

        resolve(personneContacteDatatable)
      })
    }

    async function onChangeNavigationTab(navigationTab) {
      if (['modifier', 'date-envoi', 'personne-contacte'].includes(navigationTab)) {
        formNavigationTab.value.active = navigationTab
        
        await nextTick()

        const actions = {
          modifier: getRequestDetail,
          'date-envoi': getDateDesEnvois,
          'personne-contacte': getPersonneContactes,
        }

        await actions[navigationTab]()
      }
    }

    onMounted(() => {
      jQuery(".editor").summernote({
        height: 200,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'italic', 'clear']],
          ['fontname', ['fontname']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']],
        ],
      })

      getRequestDetail().then(() => {
        jQuery(".select2-input").select2({
          theme: "bootstrap4",
          allowClear: true,
          placeholder: "- Choisir une option -",
        })
        jQuery("#request-speciality").on('change', function() {
          requestData.value.speciality = jQuery(this).val()
        })
        jQuery("#request-applicant").on('change', function() {
          requestData.value.applicant = jQuery(this).val()
        })
        jQuery("#request-region").on('change', function() {
          requestData.value.region = jQuery(this).val()
        })

        jQuery("#request-started-at, #request-end-at").datepicker({
          format: "dd/mm/yyyy",
          autoclose: true,
        })
        jQuery("#request-started-at-input").on("change", function () {
          requestData.value.startedAt = jQuery(this).val()
        })
        jQuery("#request-end-at-input").on("change", function () {
          requestData.value.endAt = jQuery(this).val()
        })
      })
    })

    return {
      requesting,
      requestData,
      formEl,
      formNavigationTab,
      requestDateEnvois,

      getErrorClass,
      onCreateRequest,
      onChangeNavigationTab,
    }
  },
})
app.config.compilerOptions.delimiters = ["$%", "%$"]
app.mount("#root")

/****************************************/
/**** SELECTION PERSONNE A CONTACTER ****/
/****************************************/

const selectedUsersId = new Set()
let tableSelectionUsers = null

function openSelectUserModal() {
  $('#select-user-modal').modal('show')

  const tblDom = $('#tbl-select-users')
  const listPersonneUrl = tblDom.data('listUrl')

  tableSelectionUsers = tblDom.DataTable({
    paging: true,
    searching: true,
    ordering: true,
    responsive: true,
    order: [[1, 'asc']],
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
        orderable: false,
        render: function (data, type, row, meta) {
          let ckeckedAttr = ''

          if (selectedUsersId.has(data)) {
            ckeckedAttr = ' checked="true"'
          }

          return `<div class="form-check"><input class="form-check-input select-user-checkbox" type="checkbox" value="${data}"${ckeckedAttr}></div>`
        }
      },
      {
        targets: 1,
        data: "name",
        width: '25%',
      },
      {
        targets: 2,
        data: 'surname',
        width: '25%',
      },
      {
        targets: 3,
        data: 'speciality.name',
        width: '20%',
      },
      {
        targets: 4,
        data: "mobilities",
        width: '20%',
        orderable: false,
        render: function (data, type, row, meta) {
          if (data && data.length > 0) {
            return data[0].name
          }
          return ''
        },
      },
    ],
    serverSide: true,
    ajax: {
      url: listPersonneUrl,
      type: "GET",
    },
  })
}

function hideSelectUserModal(redirect = false) {
  $('#select-user-modal').modal('hide')

  if (redirect) {
    jQuery("#btn-request-list")[0].click()
  }
}

function saveSelectedUsers() {
  console.log('SELECTED USERS: ', selectedUsersId)
}

function toggleSelectedUserId(userId, isSelected) {
  const alreadySelected = selectedUsersId.has(userId)

  // select
  if (isSelected && !alreadySelected) {
    selectedUsersId.add(userId)
  }

  // deselect
  if (!isSelected && alreadySelected) {
    selectedUsersId.delete(userId)
  }
}

async function getUserIdFor() {
  const idsUrl = $('#select-user-modal').data('idsUrl')

  const searchValue = $('#tbl-select-users_filter input').val()
  const speciality = $('#select-user-speciality')
  const sousSpeciality = $('#select-user-sous-speciality')
  const mobility = $('#select-user-mobility')

  const params = {}
  
  if (searchValue) {
    params.search = searchValue
  }

  if (speciality.prop('checked')) {
    params.speciality = speciality.val()
  }

  if (sousSpeciality.prop('checked')) {
    params.sousSpeciality = sousSpeciality.val()
  }

  if (mobility.prop('checked')) {
    params.mobility = mobility.val()
  }

  console.log("PARAMS: ", params)

  const response = await axios.get(idsUrl, {
    params: params
  })

  return response.data
}

$('#select-user-modal').on('hidden.bs.modal', () => {
  if (tableSelectionUsers) {
    tableSelectionUsers.destroy()
  }
})

$(document).on('change', '.select-user-checkbox', (e) => {
  const el = e.target
  const value = e.target.value
  const checked = e.target.checked

  if (value === 'tout') {
    getUserIdFor()
      .then(userIds => {
        userIds.forEach(userId => {
          toggleSelectedUserId(userId, checked)
        })

        if (tableSelectionUsers) {
          tableSelectionUsers.draw()
        }
      })

  } else {
    const userId = +value
    toggleSelectedUserId(userId, checked)
  }
})

$('#btn-select-user-save').on('click', () => {
  saveSelectedUsers()
})