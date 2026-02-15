import { formatDate, initSelect2, initDatepicker, initSummernote, showAlert, toFormData as windowToFormData } from 'admin-app'

const { createApp, ref, onMounted } = Vue;

const app = createApp({
  setup() {
    const requesting = ref(false);
    const userData = ref({
      position: null,
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
      roles: [5],
      speciality: null,
      chiefServiceName: null,
      establishmentName: null,
      bedsCount: null,
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
      establishmentName: (value) => !!value,
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
      return windowToFormData(userData.value);
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
              position: data.position,
              telephone: data.telephone,
              telephone2: data.telephone2,
              thoroughfare: data.address ? data.address.thoroughfare : null,
              premise: data.address ? data.address.premise : null,
              postalCode: data.address ? data.address.postal_code : null,
              locality: data.address ? data.address.locality : null,
              fax: data.fax,
              speciality: data.speciality ? data.speciality.id : null,
              email: data.email,
              status: data.status ? 1 : 0,
              roles: data.roles ? data.roles.map((r) => r.id) : [],
              chiefServiceName: data.establishment ? data.establishment.chiefServiceName : null,
              establishmentName: data.establishment ? data.establishment.name : null,
              bedsCount: data.establishment ? data.establishment.bedsCount : null,
              siteWeb: data.establishment ? data.establishment.siteWeb : null,
              comment: data.comment,
              subscriptionStatus:  data.subscription ? (data.subscription.status ? 1 : 0) : null,
              subscriptionEndAt:  data.subscription ? formatDate(data.subscription.endAt, false) : null,
              subscriptionEndNotification:  data.subscription ? (data.subscription.endNotification ? 1 : 0) : null,
              installationCount:  data.subscription ? data.subscription.installationCount : null,
            };
            requesting.value = false;
            jQuery("#clinic-comment").summernote("code", data.comment);
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
            jQuery("#btn-clinic-list")[0].click();
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

        initDatepicker('#clinic-subscription-end')

        jQuery('#clinic-speciality').on('change', function() {
          userData.value.speciality = jQuery(this).val()
        })

        jQuery('#clinic-subscription-end-input').on('change', function () {
          userData.value.subscriptionEndAt = jQuery(this).val();
        });

        $('#clinic-comment').on('summernote.change', function(we, contents, $editable) {
          userData.value.comment = contents
        })
      });
    });

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
