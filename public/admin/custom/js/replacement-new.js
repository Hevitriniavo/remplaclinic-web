const { createApp, ref, onMounted } = Vue;

const app = createApp({
  setup() {
    const requesting = ref(false);
    const userData = ref({
      civility: null,
      surname: null,
      name: null,
      ordinaryNumber: null,
      telephone: null,
      telephone2: null,
      thoroughfare: null,
      premise: null,
      postalCode: null,
      locality: null,
      yearOfBirth: null,
      nationality: null,
      email: null,
      password: null,
      passwordConfirmation: null,
      status: 1,
      roles: [4],
      yearOfResidency: null,
      currentSpeciality: null,
      speciality: null,
      subSpecialities: [],
      mobility: [],
      cv: null,
      diplom: null,
      licence: null,
      comment: null,
      userComment: null,
    });
    const userValidators = {
      civility: (value) => value === "M" || value === "Mme" || value == "Mlle",
      surname: (value) => !!value,
      name: (value) => !!value,
      telephone: (value) => !!value,
      thoroughfare: (value) => !!value,
      postalCode: (value) => !!value,
      locality: (value) => !!value,
      yearOfBirth: (value) => !!value && parseInt(value) > 1900,
      nationality: (value) => !!value,
      email: (value) =>
        !!value &&
        /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value),
      password: (value) => !!value,
      passwordConfirmation: (value, form) => !!value && value === form.password,
      status: (value) => value == 1 || value == 0,
      roles: (value) => !!value && value.length > 0,
      currentSpeciality: (value) => !!value && parseInt(value) > 0,
    };
    const userErrors = ref({
      validated: false,
      error: false,
      details: {},
    });

    const formEl = ref(null);
    const shownCollapse = ref(null);

    function changeCollapse(step) {
      if (step === shownCollapse.value) {
        shownCollapse.value = null;
      } else {
        shownCollapse.value = step;
      }
    }

    function getErrorClass(fieldName) {
      if (userErrors.value.error && userErrors.value.validated) {
        return userErrors.value.details[fieldName] ? "is-invalid" : "is-valid";
      }
      return "";
    }

    function validateFormData() {
      userErrors.value.validated = true;
      const formData = userData.value;
      let hasError = false;
      for (const key in formData) {
        if (Object.prototype.hasOwnProperty.call(formData, key)) {
          if (Object.prototype.hasOwnProperty.call(userValidators, key)) {
            const cb = userValidators[key];
            if (!cb(formData[key], formData)) {
              hasError = true;
              userErrors.value.details[key] = true;
            } else {
              userErrors.value.details[key] = false;
            }
          }
        }
      }
      userErrors.value.error = hasError;

      return hasError;
    }

    function toFormData() {
      const formData = userData.value;
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

    function handleFileUpload(field, event) {
      userData.value[field] = event.target.files[0];
    }

    function getUserDetail() {
      const url = formEl.value.dataset.detailUrl;
      if (url) {
        requesting.value = true;
        return axios
          .get(url)
          .then((res) => {
            const data = res.data;
            userData.value = {
              civility: data.civility,
              surname: data.surname,
              name: data.name,
              ordinaryNumber: data.ordinaryNumber,
              telephone: data.telephone,
              telephone2: data.telephone2,
              thoroughfare: data.address ? data.address.thoroughfare : null,
              premise: data.address ? data.address.premise : null,
              postalCode: data.address ? data.address.postal_code : null,
              locality: data.address ? data.address.locality : null,
              yearOfBirth: data.yearOfBirth,
              nationality: data.nationality,
              email: data.email,
              status: data.status ? 1 : 0,
              roles: data.roles ? data.roles.map((r) => r.id) : [],
              yearOfResidency: data.yearOfAlternance,
              currentSpeciality: data.currentSpeciality,
              speciality: data.speciality ? data.speciality.id : null,
              subSpecialities: data.subSpecialities
                ? data.subSpecialities.map((s) => s.id)
                : [],
              mobility: data.mobilities ? data.mobilities.map((m) => m.id) : [],
              comment: data.comment,
              userComment: data.userComment,
            };
            requesting.value = false;
            jQuery("#replacement-comment").summernote("code", data.comment);
            jQuery("#replacement-comments").summernote(
              "code",
              data.userComment
            );
          })
          .catch(() => {});
      }

      return Promise.resolve();
    }

    function onCreateUser() {
      userData.value.comment = jQuery("#replacement-comment").summernote(
        "code"
      );
      userData.value.userComment = jQuery("#replacement-comments").summernote(
        "code"
      );

      userData.value.speciality = jQuery("#replacement-speciality").val();
      userData.value.subSpecialities = jQuery(
        "#replacement-sub-specialities"
      ).val();
      userData.value.mobility = jQuery("#replacement-mobility").val();

      const payload = toFormData();

      if (!validateFormData()) {
        requesting.value = true;
        const action = formEl.value.dataset.url;
        axios
          .post(action, payload)
          .then(() => {
            jQuery("#btn-replacement-list")[0].click();
          })
          .catch(() => {
            requesting.value = false;
          });
      } else {
        window.showAlert('Veuillez saisir tous les informations qui sont requises !', 'warning');
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

      getUserDetail().then(() => {
        jQuery(".select2-input").select2({
          theme: "bootstrap4",
          allowClear: true,
          placeholder: "- Choisir une option -",
        });
      });
    });

    return {
      requesting,
      userData,
      formEl,
      shownCollapse,

      changeCollapse,
      getErrorClass,
      handleFileUpload,
      onCreateUser,
    };
  },
});
app.config.compilerOptions.delimiters = ["$%", "%$"];
app.mount("#root");
