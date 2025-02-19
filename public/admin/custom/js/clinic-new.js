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
      serviceName: null,
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

    function formatDate(data, withHour = true) {
      const date = new Date(data);
      const options = {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      };
      if (withHour) {
        options['hour'] = '2-digit';
        options['minute'] = '2-digit';
      }
      const formatter = new Intl.DateTimeFormat("fr-FR", options);
      return formatter.format(date);
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
              serviceName: data.establishment ? data.establishment.serviceName : null,
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
      userData.value.comment = jQuery("#clinic-comment").summernote(
        "code"
      );

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
      }
    }

    onMounted(() => {
      jQuery(".editor").summernote({
        height: 200,
      });

      getUserDetail().then(() => {
        jQuery("#clinic-subscription-end").datepicker({
          format: "dd/mm/yyyy",
          autoclose: true,
        });
        jQuery(".select2-input").select2({
          theme: "bootstrap4",
          allowClear: true,
          placeholder: "- Choisir une option -",
        });
        jQuery("#clinic-subscription-end-input").on('change', function () {
          userData.value.subscriptionEndAt = jQuery(this).val();
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
      onCreateUser,
    };
  },
});
app.config.compilerOptions.delimiters = ["$%", "%$"];
app.mount("#root");
