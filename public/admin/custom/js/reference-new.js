const { createApp, ref, onMounted } = Vue;

const app = createApp({
  setup() {
    const requesting = ref(false);
    const formEvidenceEl = ref(null);
    const evidenceData = ref({
      title: "",
      clinicName: "",
      specialityName: "",
      body: "",
    });
    const formError = ref({
      error: false,
      validated: false,
      details: {
        title: false,
        clinicName: false,
        specialityName: false,
        body: false,
      },
    });

    function validateFormData() {
      formError.value.validated = true;
      const formData = evidenceData.value;
      let hasError = false;
      for (const key of ["title", "clinicName", "specialityName", "body"]) {
        if (!formData[key]) {
          hasError = true;
          formError.value.details[key] = true;
        } else {
          formError.value.details[key] = false;
        }
      }
      formError.value.error = hasError;

      return hasError;
    }

    function getEvidenceDetail() {
      const url = formEvidenceEl.value.dataset.detailUrl;
      if (detailUrl) {
        requesting.value = true;
        return axios.get(url, evidenceData.value)
            .then((res) => {
              evidenceData.value = res.data;
              requesting.value = false;
              jQuery("#reference-body").summernote('code', evidenceData.value.body)
            })
            .catch(() => {})
      }
    }

    function onCreateEvidence() {
      evidenceData.value.body = jQuery("#reference-body").summernote('code');

      if (!validateFormData()) {
        requesting.value = true;
        const action = formEvidenceEl.value.action;
        axios.post(action, evidenceData.value)
          .then(() => {
            jQuery("#btn-reference-list")[0].click();
          })
          .catch(() => {
            requesting.value = false;
          })
      }
    }

    function getErrorClass(fieldName) {
      if (formError.value.error && formError.value.validated) {
        return formError.value.details[fieldName] ? "is-invalid" : "is-valid";
      }
      return "";
    }

    onMounted(() => {
      getEvidenceDetail();
    });

    return {
      evidenceData,
      formError,
      requesting,
      formEvidenceEl,

      onCreateEvidence,
      getErrorClass,
    };
  },
});
app.mount("#root");

$(function () {
  $("#reference-body").summernote({
    height: 300,
  });
});

