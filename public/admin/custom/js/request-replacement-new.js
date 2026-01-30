const { createApp, ref, onMounted, nextTick, shallowRef } = Vue

const app = createApp({
  setup() {
    const requesting = ref(false)
    const requestData = ref({
      id: null,
      title: null,
      status: null,
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
      subSpecialities: []
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
      replacementType: (value) => value === "ponctual" || value === "regular",
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
    const personneContacteDatatable = shallowRef(null)
    const personneContacteNew = ref(null)

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
      return window.toFormData(requestData.value)
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
          status: response.data.status,
          applicant: response.data.applicant.id,
          speciality: response.data.speciality.id,
          region: response.data.region.id,
          positionCount: response.data.positionCount,
          remuneration: response.data.remuneration,
          retrocession: response.data.retrocession,
          startedAt: window.formatDate(response.data.startedAt, false),
          endAt: response.data.endAt ? window.formatDate(response.data.endAt, false) : null,
          replacementType: response.data.replacementType,
          accomodationIncluded: response.data.accomodationIncluded,
          transportCostRefunded: response.data.transportCostRefunded,
          subSpecialities: [],
        }

        jQuery("#request-comment").summernote("code", response.data.comment)

        // sub specialities
        if (response.data.subSpecialities) {
          requestData.value.subSpecialities = response.data.subSpecialities.map(s => s.id)
        }

        // update date envoi
        const requestDateEnvoisList = response.data.sentDates || []
        requestDateEnvois.value = requestDateEnvoisList.map(dateEnvoi => window.formatDate(dateEnvoi.sentAt))

        return response.data
      }

      return null
    }

    function onSaveRequest() {
      const estModification = requestData.value.id !== null

      if (estModification) {
        onEditRequest()
      } else {
        onCreateRequest()
      }
    }

    function onCreateRequest() {
      requestData.value.comment = jQuery("#request-comment").summernote("code")

      const payload = toFormData()

      if (!validateFormData()) {
        requesting.value = true
        const action = formEl.value.dataset.url
        axios
          .post(action, payload)
          .then((res) => {
            requesting.value = false
            openSelectUserModal(res.data.id, requestData.value.speciality, requestData.value.region)
          })
          .catch(() => {
            requesting.value = false
          })
      }
    }

    function onEditRequest() {
      requestData.value.comment = jQuery("#request-comment").summernote("code")

      const payload = toFormData()

      if (!validateFormData()) {
        requesting.value = true
        const action = $('#root').data('updateUrl')
        axios
          .post(action, payload)
          .then(() => {
            requesting.value = false
            jQuery("#btn-request-list")[0].click()
          })
          .catch(() => {
            requesting.value = false
          })
      }
    }

    function getDateDesEnvois () {
      // data loaded with getRequestDetail

      return Promise.resolve()
    }

    function getPersonneContactes () {
      return new Promise((resolve) => {
        const tblDom = $("#tbl-personne-contacte")
        const url = tblDom.data("listUrl")

        personneContacteDatatable.value = tblDom.DataTable({
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
          order: [[1, 'desc']],
          columnDefs: [
            {
              targets: 0,
              data: "id",
              width: '6%',
              orderable: false,
              render: function (data, type, row, meta) {
                return `
                  <div class="custom-control custom-checkbox">
                    <input class="custom-control-input custom-control-input-secondary personne-contacte-selection" type="checkbox" id="personne-contacte-selection-${data}" value="${data}">
                    <label for="personne-contacte-selection-${data}" class="custom-control-label"></label>
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
              data: "user.name",
              width: '20%',
            },
            {
              targets: 3,
              data: "user.surname",
              width: '20%',
            },
            {
              targets: 4,
              data: 'user.speciality.name',
              width: '25%',
              // render: function (data, type, row, meta) {
              //   return row.specialityParent ? row.specialityParent.name : ''
              // }
            },
            {
              targets: 5,
              data: 'statut',
              width: '10%',
              render: function (data, type, row, meta) {
                const statutMap = {
                  '0': 'En cours',
                  '1': 'Accepte',
                  '2': 'Plus d\'infos',
                  '3': 'Exclu',
                }

                return statutMap[data] ? statutMap[data] : 'En cours'
              }
            },
            {
              targets: 6,
              data: "id",
              orderable: false,
              className: "text-right",
              width: '10%',
              render: function (data, type, row, meta) {
                const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id'])
                const editUrl = getCleanUrl(tblDom.data('edit-url'), row['id'])
                return (
                  "<div>" +
                  '<a class="btn btn-sm btn-outline-info btn-answer" data-url="'+ editUrl +'" data-statut="0" data-id="'+ row['id'] +'" title="Repondre"><i class="fas fa-reply"></i></a>' +
                  '<a class="btn btn-sm btn-outline-info btn-answer ml-2" data-url="'+ editUrl +'" data-statut="1" data-id="'+ row['id'] +'" title="Plus d\'info"><i class="fas fa-info"></i></a>' +
                  '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'" title="Supprimer"><i class="fas fa-trash"></i></a>' +
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
          personneContacteDatatable.value.draw()
        })

        // add user to a request
        jQuery("#request-user-new").select2({
          theme: "bootstrap4",
          allowClear: true,
          placeholder: "- Choisir une option -",
          ajax: {
            beforeSend: null,
            url: $('#request-user-new').data("url"),
            type: "get",
            dataType: "json",
            delay: 200,
            data: (params) => {
              return {
                search: params.term
              }
            },
            processResults: function(data) {
              return {
                results: data.map(user => ({ id: user.id, text: `${user.name} ${user.surname}` }))
              }
            }
          },
        })
        jQuery("#request-user-new").on('change', function() {
          personneContacteNew.value = jQuery(this).val()
        })

        resolve(true)
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

        const response = await actions[navigationTab]()

        if (navigationTab === 'modifier') {
          initFormView(response)
        }
      }
    }

    async function onAddMissingRequest(e) {
      axios.post(e.target.dataset.url, { users: [personneContacteNew.value]})
        .then(() => {
          // personneContacteNew.value = null
          $('#request-user-new').val('')
          $('#request-user-new').trigger('change')

          if (personneContacteDatatable.value) {
            personneContacteDatatable.value.draw()
          }
        })
        .catch(() => {})
    }

    function createUserApplicantSelect2(selector) {
      jQuery(selector).select2({
        theme: "bootstrap4",
        allowClear: true,
        placeholder: "- Choisir une option -",
        ajax: {
          beforeSend: null,
          url: formEl.value.dataset.applicantUrl,
          type: "get",
          dataType: "json",
          delay: 200,
          data: (params) => {
            return {
              search: params.term,
              roles: [5, 6]
            }
          },
          processResults: function(data) {
            return {
              results: data.map(user => ({ id: user.id, text: user.establishmentName ? user.establishmentName : `${user.name} ${user.surname}` }))
            }
          }
        },
      })
    }

    function initFormView(data) {
      jQuery(".select2-input").select2({
        theme: "bootstrap4",
        allowClear: true,
        placeholder: "- Choisir une option -",
      })

      initAndSetApplicant(data?.applicant)

      jQuery("#request-speciality").on('change', function() {
        requestData.value.speciality = jQuery(this).val()
      })
      jQuery("#request-applicant").on('change', function() {
        requestData.value.applicant = jQuery(this).val()
      })
      jQuery("#request-region").on('change', function() {
        requestData.value.region = jQuery(this).val()
      })
      
      jQuery("#request-sub-specialities").on('change', function() {
        requestData.value.subSpecialities = jQuery(this).val().filter(val => !!val)
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
    }

    function initAndSetApplicant(applicant) {
      createUserApplicantSelect2('#request-applicant')

      if (applicant) {
        const nomLibelle = applicant.establishment?.name ? applicant.establishment.name : `${applicant.name} ${applicant.surname}`
        jQuery('#request-applicant').html(`<option value="${applicant.id}" selected="selected">${nomLibelle}</option>`)
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

      getRequestDetail().then((data) => {
        initFormView(data)
      })
    })

    return {
      requesting,
      requestData,
      formEl,
      formNavigationTab,
      requestDateEnvois,
      personneContacteNew,

      getErrorClass,
      onSaveRequest,
      onCreateRequest,
      onEditRequest,
      onChangeNavigationTab,
      onAddMissingRequest,
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

/**
 * @param {string} requestId L'ID de la demande
 * @param {string} speciality La specialite selectionne
 * @param {string} mobility La region selectionne 
 */
function openSelectUserModal(requestId, speciality, mobility) {
  $('#select-user-modal').modal('show')

  $('#btn-select-user-save').attr('data-request-id', requestId)

  // MAJ de la valeur des filtres
  $('#select-user-speciality').val(speciality)
  $('#select-user-sous-speciality').val(speciality)
  $('#select-user-mobility').val(mobility)

  // Ajout event handlers aux filtres
  const filtres = [
    '#select-user-speciality',
    '#select-user-sous-speciality',
    '#select-user-mobility',
    '#select-user-or',
  ]
  filtres.forEach(filtreId => {
    $(filtreId).on('change', () => {
      tableSelectionUsers.draw()
    })
  })

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
      data: (params) => {
        return {
          ...params,
          ...getFiltreParametres(),
        }
      },
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
  const btnSave = $('#btn-select-user-save')
  const requestUrl = btnSave.attr('data-request-url')
  const requestId = btnSave.attr('data-request-id')

  const url = getCleanUrl(requestUrl, requestId)

  axios.post(url, {
    users: Array.from(selectedUsersId),
  })
    .then(() => {})
    .catch(() => {})
  
  hideSelectUserModal(true)
}

function toggleSelectedUserId(userId, isSelected, updateCount = true) {
  const alreadySelected = selectedUsersId.has(userId)

  // select
  if (isSelected && !alreadySelected) {
    selectedUsersId.add(userId)
  }

  // deselect
  if (!isSelected && alreadySelected) {
    selectedUsersId.delete(userId)
  }

  if (updateCount) {
    $('#select-user-count').text(selectedUsersId.size)
  }
}

/**
 * Recuperer les IDs des utilisateur pour pouvoir les selectionner.
 * 
 * @param {number} checked La valeur du checkbox (selectionner ou pas?)
 */
async function getUserIdFor(checked) {
  const idsUrl = $('#select-user-modal').data('idsUrl')

  const params = getFiltreParametres()
  
  // search value
  const searchValue = $('#tbl-select-users_filter input').val()

  if (searchValue) {
    params.search = searchValue
  }

  const response = await axios.get(idsUrl, {
    params: params
  })

  response.data.forEach(userId => {
    toggleSelectedUserId(userId, checked, false)
  })

  // update counter text
  $('#select-user-count').text(selectedUsersId.size)

  // actualiser le tableau
  if (tableSelectionUsers) {
    tableSelectionUsers.draw()
  }
}

/**
 * Recuperer les parametres correspondant aux filtres.
 * 
 * @return {{
 *  speciality: String,
 *  sousSpeciality: String,
 *  mobility: String,
 *  condition_type: String
 * }}
 */
function getFiltreParametres() {
  const params = {
    condition_type: 'and'
  }

  const speciality = $('#select-user-speciality')
  if (speciality.prop('checked')) {
    params.speciality = speciality.val()
  }

  const sousSpeciality = $('#select-user-sous-speciality')
  if (sousSpeciality.prop('checked')) {
    params.sousSpeciality = sousSpeciality.val()
  }

  const mobility = $('#select-user-mobility')
  if (mobility.prop('checked')) {
    params.mobility = mobility.val()
  }

  const conditionTypeOr = $('#select-user-or')
  if (conditionTypeOr.prop('checked')) {
    params.condition_type = 'or'
  }

  return params
}

$('#select-user-modal').on('hidden.bs.modal', () => {
  if (tableSelectionUsers) {
    tableSelectionUsers.destroy()
  }
})

$(document).on('change', '.select-user-checkbox', (e) => {
  const value = e.target.value
  const checked = e.target.checked

  if (value === 'tout') {
    getUserIdFor(checked)
      .then(() => {})
      .catch(() => {})
  } else {
    const userId = +value
    toggleSelectedUserId(userId, checked)
  }
})

$('#btn-select-user-save').on('click', () => {
  saveSelectedUsers()
})