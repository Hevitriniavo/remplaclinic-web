import { getCleanUrl, initSelect2, initSummernote, showAlert, toFormData as windowToFormData } from 'admin-app'

const { createApp, ref, onMounted, nextTick } = Vue;

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
      comment: null,
      userComment: null,
    });

    const userAttachment = ref({
      cv: null,
      diplom: null,
      licence: null,
    })

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
      speciality: (value) => !!value && parseInt(value) > 0,
      mobility: (value) => !!value && value.length > 0,
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
      const result = windowToFormData(userData.value)

      for (const fieldName in userAttachment.value) {
        if (!Object.hasOwn(userAttachment.value, fieldName)) continue

        const file = userAttachment.value[fieldName]
        if (file) {
          result.append(fieldName, file)
        }
      }

      return result;
    }

    function handleFileUpload(field, event) {
      userAttachment.value[field] = event.target.files[0];
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
            initSousSpecialiteSelectOptions(data.subSpecialities ? data.subSpecialities : [])
          })
          .catch(() => {});
      }

      return Promise.resolve();
    }

    function onCreateUser() {
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
        showAlert('Veuillez saisir tous les informations qui sont requises !', 'warning');
      }
    }

    function initSousSpecialiteSelectOptions(options) {
      options.forEach(function(item) {
        const option = new Option(item.name, item.id, true, true)
        jQuery('#replacement-sub-specialities').append(option)
      })
    }

    async function initSousSpecialiteSelect() {

      await nextTick()

      initSelect2('#replacement-sub-specialities', {
        ajax: {
          beforeSend: null,
          url: getCleanUrl(jQuery('#replacement-speciality').data('spsUrl'), userData.value.speciality),
          type: 'get',
          dataType: 'json',
          delay: 200,
          processResults: function(data) {
            return {
              results: data.map(sp => ({ id: sp.id, text: sp.name }))
            }
          }
        },
      })
    }

    onMounted(() => {
      initSummernote('.editor', {
        height: 200,
      })

      getUserDetail().then(() => {
        initSelect2('.select2-input')

        jQuery("#replacement-speciality").on('change', function() {
          userData.value.speciality = jQuery(this).val()

          initSousSpecialiteSelect()
        })

        jQuery("#replacement-mobility").on('change', function() {
          userData.value.mobility = jQuery(this).val().filter(val => !!val)
        })

        jQuery("#replacement-sub-specialities").on('change', function() {
          userData.value.subSpecialities = jQuery(this).val().filter(val => !!val)
        })

        $('#replacement-comment').on('summernote.change', function(we, contents, $editable) {
          userData.value.comment = contents
        })

        $('#replacement-comments').on('summernote.change', function(we, contents, $editable) {
          userData.value.userComment = contents
        })

        // pour modification
        if (userData.value.speciality) {
          initSousSpecialiteSelect()
        }
      })
    })

    return {
      requesting,
      userData,
      userAttachment,
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
