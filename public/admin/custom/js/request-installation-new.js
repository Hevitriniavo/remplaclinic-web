const { createApp, ref, onMounted, computed } = Vue;

const app = createApp({
  setup() {
    const requesting = ref(false);
    const requestData = ref({
      applicant: null,
      speciality: null,
      region: null,
      remuneration: null,
      startedAt: null,
      raison: [],
      raisonValue: null,
      comment: null,
    });
    const requestValidators = {
      applicant: (value) => !!value,
      speciality: (value) => !!value,
      region: (value) => !!value,
      startedAt: (value) => !!value,
      raison: (value) => !!value && value.length > 0,
    };
    const requestErrors = ref({
      validated: false,
      error: false,
      details: {},
    });

    const raisons = [
      { id: 1, label: "Recrutement afin de compléter l'équipe actuelle" },
      { id: 2, label: "Création de poste" },
      { id: 3, label: "Création de cabinet ou proposition d'association" },
      { id: 4, label: "Cession de patientèle ou de fonds de commerce" },
      { id: 5, label: "Cession de parts d'une société (SCM, SELARL, etc)" },
      { id: 6, label: "Départ en retraite" },
      { id: 7, label: "Autre" },
    ];

    const formEl = ref(null);
    const otherChoosen = computed(() => {
      const hasOther = requestData.value.raison.some(r => r === 'Autre');
      return hasOther;
    });

    function getErrorClass(fieldName) {
      if (requestErrors.value.error && requestErrors.value.validated) {
        return requestErrors.value.details[fieldName]
          ? "is-invalid"
          : "is-valid";
      }
      return "";
    }

    function validateFormData() {
      requestErrors.value.validated = true;
      const formData = requestData.value;
      let hasError = false;
      for (const key in formData) {
        if (Object.prototype.hasOwnProperty.call(formData, key)) {
          if (Object.prototype.hasOwnProperty.call(requestValidators, key)) {
            const cb = requestValidators[key];
            if (!cb(formData[key], formData)) {
              hasError = true;
              requestErrors.value.details[key] = true;
            } else {
              requestErrors.value.details[key] = false;
            }
          }
        }
      }
      requestErrors.value.error = hasError;

      return hasError;
    }

    function toFormData() {
      const formData = requestData.value;
      const result = new FormData();
      for (const key in formData) {
        if (Object.prototype.hasOwnProperty.call(formData, key)) {
          const value = formData[key];
          if (Array.isArray(value)) {
            let index = 0;
            for (const aValue of value) {
              result.append(`${key}[${index}]`, aValue);
              index++;
            }
          } else if (value) {
            result.append(key, value);
          }
        }
      }
      return result;
    }

    function getRequestDetail() {
      return Promise.resolve();
    }

    function onCreateRequest() {
      requestData.value.comment = jQuery("#request-comment").summernote("code");

      const payload = toFormData();
      
      if (!validateFormData()) {
        requesting.value = true;
        const action = formEl.value.dataset.url;
        axios
          .post(action, payload)
          .then(() => {
            jQuery("#btn-request-list")[0].click();
          })
          .catch(() => {
            requesting.value = false;
          });
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
      });

      getRequestDetail().then(() => {
        // select 2
        jQuery(".select2-input").select2({
          theme: "bootstrap4",
          allowClear: true,
          placeholder: "- Choisir une option -",
        });
        jQuery("#request-speciality").on('change', function() {
          requestData.value.speciality = jQuery(this).val();
        });
        jQuery("#request-applicant").on('change', function() {
          requestData.value.applicant = jQuery(this).val();
        });
        jQuery("#request-region").on('change', function() {
          requestData.value.region = jQuery(this).val();
        });

        jQuery("#request-started-at, #request-end-at").datepicker({
          format: "dd/mm/yyyy",
          autoclose: true,
        });
        jQuery("#request-started-at-input").on("change", function () {
          requestData.value.startedAt = jQuery(this).val();
        });
        jQuery("#request-end-at-input").on("change", function () {
          requestData.value.endAt = jQuery(this).val();
        });
      });
    });

    return {
      requesting,
      requestData,
      formEl,
      raisons,
      otherChoosen,

      getErrorClass,
      onCreateRequest,
    };
  },
});
app.config.compilerOptions.delimiters = ["$%", "%$"];
app.mount("#root");
