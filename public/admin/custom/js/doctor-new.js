import { formatDate, initSelect2, initDatepicker, initSummernote, showAlert, toFormData as windowToFormData } from 'admin-app'

const { createApp, ref, onMounted } = Vue;

const app = createApp({
  setup() {
    const requesting = ref(false);
    const userData = ref({
      ordinaryNumber: null,
      civility: null,
      surname: null,
      name: null,
      telephone: null,
      telephone2: null,
      fax: null,
      email: null,
      password: null,
      passwordConfirmation: null,
      status: 1,
      roles: [6],
      speciality: null,
      consultationCount: null,
      per: null,
      siteWeb: null,
      thoroughfare: null,
      premise: null,
      postalCode: null,
      locality: null,
      subscriptionStatus: null,
      subscriptionEndAt: null,
      subscriptionEndNotification: null,
      installationCount: null,
      comment: null,
    });
    const userValidators = {
      civility: (value) => value === "M" || value === "Mme" || value == "Mlle",
      surname: (value) => !!value,
      name: (value) => !!value,
      telephone: (value) => !!value,
      thoroughfare: (value) => !!value,
      postalCode: (value) => !!value,
      locality: (value) => !!value,
      email: (value) =>
        !!value &&
        /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value),
      password: (value) => !!value,
      passwordConfirmation: (value, form) => !!value && value === form.password,
      status: (value) => value == 1 || value == 0,
      per: (value) => value == 'jour' || value == 'semaine',
      roles: (value) => !!value && value.length > 0,
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
      return windowToFormData(userData.value)
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
              ordinaryNumber: data.ordinaryNumber,
              civility: data.civility,
              surname: data.surname,
              name: data.name,
              telephone: data.telephone,
              telephone2: data.telephone2,
              thoroughfare: data.address ? data.address.thoroughfare : null,
              premise: data.address ? data.address.premise : null,
              postalCode: data.address ? data.address.postal_code : null,
              locality: data.address ? data.address.locality : null,
              fax: data.fax,
              email: data.email,
              status: data.status ? 1 : 0,
              roles: data.roles ? data.roles.map((r) => r.id) : [],
              speciality: data.speciality ? data.speciality.id : null,
              per: data.establishment ? data.establishment.per : null,
              consultationCount: data.establishment ? data.establishment.consultationCount : null,
              siteWeb: data.establishment ? data.establishment.siteWeb : null,
              comment: data.comment,
              subscriptionStatus:  data.subscription ? (data.subscription.status ? 1 : 0) : null,
              subscriptionEndAt:  data.subscription ? formatDate(data.subscription.endAt, false) : null,
              subscriptionEndNotification:  data.subscription ? (data.subscription.endNotification ? 1 : 0) : null,
              installationCount:  data.subscription ? data.subscription.installationCount : null,
            };
            requesting.value = false;
            jQuery("#doctor-comment").summernote("code", data.comment);
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
            jQuery("#btn-doctor-list")[0].click();
          })
          .catch(() => {
            requesting.value = false;
          });
      } else {
        showAlert('Veuillez saisir tous les informations qui sont requises !', 'warning');
      }
    }

    onMounted(() => {
      initSummernote('.editor', {
        height: 200,
      })

      getUserDetail().then(() => {
        initSelect2('.select2-input')

        initDatepicker('#doctor-subscription-end')

        jQuery('#doctor-speciality').on('change', function() {
          userData.value.speciality = jQuery(this).val()
        })

        jQuery("#doctor-subscription-end-input").on('change', function () {
          userData.value.subscriptionEndAt = jQuery(this).val()
        })

        $('#doctor-comment').on('summernote.change', function(we, contents, $editable) {
          userData.value.comment = contents
        })
      })
    })

    return {
      requesting,
      userData,
      formEl,
      shownCollapse,

      changeCollapse,
      getErrorClass,
      onCreateUser,
    };
  },
});
app.config.compilerOptions.delimiters = ["$%", "%$"];
app.mount("#root");
