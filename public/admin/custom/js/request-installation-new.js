const { createApp, ref, onMounted, computed } = Vue

const app = createApp({
  setup() {
    const requesting = ref(false)
    const requestData = ref({
      applicant: null,
      speciality: null,
      region: null,
      remuneration: null,
      startedAt: null,
      raison: [],
      raisonValue: null,
      comment: null,
    })
    const requestValidators = {
      applicant: (value) => !!value,
      speciality: (value) => !!value,
      region: (value) => !!value,
      startedAt: (value) => !!value,
      raison: (value) => !!value && value.length > 0,
    }
    const requestErrors = ref({
      validated: false,
      error: false,
      details: {},
    })

    const raisons = [
      { id: 1, label: "Recrutement afin de compléter l'équipe actuelle" },
      { id: 2, label: "Création de poste" },
      { id: 3, label: "Création de cabinet ou proposition d'association" },
      { id: 4, label: "Cession de patientèle ou de fonds de commerce" },
      { id: 5, label: "Cession de parts d'une société (SCM, SELARL, etc)" },
      { id: 6, label: "Départ en retraite" },
      { id: 7, label: "Autre" },
    ]

    const formEl = ref(null)
    const otherChoosen = computed(() => {
      const hasOther = requestData.value.raison.some(r => r === 'Autre')
      return hasOther
    })

    function getErrorClass(fieldName) {
      if (requestErrors.value.error && requestErrors.value.validated) {
        return requestErrors.value.details[fieldName]
          ? "is-invalid"
          : "is-valid"
      }
      return ""
    }

    function validateFormData() {
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

    function getRequestDetail() {
      return Promise.resolve()
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
            openSelectUserModal(res.data.id, requestData.value.speciality, requestData.value.region)
          })
          .catch(() => {
            requesting.value = false
          })
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
        // select 2
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
      raisons,
      otherChoosen,

      getErrorClass,
      onCreateRequest,
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