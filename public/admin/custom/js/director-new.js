const { createApp, ref, onMounted, computed, nextTick } = Vue;

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
      organism: null,
      email: null,
      password: null,
      passwordConfirmation: null,
      status: 1,
      roles: [7],
      thoroughfare: null,
      premise: null,
      postalCode: null,
      locality: null,
      subscriptionStatus: null,
      subscriptionEndAt: null,
      subscriptionEndNotification: null,
      comment: null,
      cliniques: []
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
      roles: (value) => !!value && value.length > 0,
    };
    const userErrors = ref({
      validated: false,
      error: false,
      details: {},
    });

    const formEl = ref(null);
    const shownCollapse = ref(null);

    const comp__allCliniquesValid = computed(() => {
      return userData.value.cliniques.every(clinique => !!clinique.id)
    })

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
      return window.toFormData({
        ...userData.value,
        cliniques: userData.value.cliniques.map(c => c.id).filter(id => !!id)
      })
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
              position: data.position,
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
              organism: data.organism,
              email: data.email,
              status: data.status ? 1 : 0,
              roles: data.roles ? data.roles.map((r) => r.id) : [],
              comment: data.comment,
              subscriptionStatus:  data.subscription ? (data.subscription.status ? 1 : 0) : null,
              subscriptionEndAt:  data.subscription ? formatDate(data.subscription.endAt, false) : null,
              subscriptionEndNotification:  data.subscription ? (data.subscription.endNotification ? 1 : 0) : null,
              cliniques: [],
            };
            requesting.value = false;
            jQuery("#director-comment").summernote("code", data.comment);

            if (data.clinics) {
              setUserClinics(data.clinics)
            }
          })
          .catch(() => {});
      }

      return Promise.resolve();
    }

    async function setUserClinics(clinics) {
      userData.value.cliniques = clinics.map(c => ({ id: c.id }))

      await nextTick()

      createUserClinicSelect2('.director-clinique')

      // trigger change
      userData.value.cliniques.forEach((id, index) => {
        const user = clinics[index]
        const nomLibelle = user.establishment?.name ? user.establishment.name : `${user.name} ${user.surname}`
        jQuery(`#director-clinique-${index}`).html(`<option value="${id.id}" selected="selected">${nomLibelle}</option>`)
      })
    }

    async function onAddClinic(id = null) {
      userData.value.cliniques.push({
        id: id,
      })

      await nextTick()

      createUserClinicSelect2(`#director-clinique-${userData.value.cliniques.length - 1}`)
    }

    function createUserClinicSelect2(selector) {
      // add user to a request
      jQuery(selector).select2({
        theme: "bootstrap4",
        allowClear: true,
        placeholder: "- Choisir une option -",
        ajax: {
          beforeSend: null,
          url: formEl.value.dataset.clinicUrl,
          type: "get",
          dataType: "json",
          delay: 200,
          data: (params) => {
            const cliniquesSelections = userData.value.cliniques.map(c => c.id).filter(id => !!id)
            return {
              search: params.term,
              exclus: cliniquesSelections,
              role: 5
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

    function onCreateUser() {
      userData.value.comment = jQuery("#director-comment").summernote(
        "code"
      );
      userData.value.speciality = jQuery("#director-speciality").val();

      const payload = toFormData();

      if (!validateFormData()) {
        requesting.value = true;
        const action = formEl.value.dataset.url;
        axios
          .post(action, payload)
          .then(() => {
            jQuery("#btn-director-list")[0].click();
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

      getUserDetail().then(() => {
        jQuery("#director-subscription-end").datepicker({
          format: "dd/mm/yyyy",
          autoclose: true,
        });
        jQuery("#director-subscription-end-input").on('change', function () {
          userData.value.subscriptionEndAt = jQuery(this).val();
        });

        jQuery(document).on('change', '.director-clinique', e => {
          const el = jQuery(e.target)
          userData.value.cliniques[el.data('keyIndex')] = { id: el.val() }
        })
      });
    });

    return {
      requesting,
      userData,
      formEl,
      shownCollapse,

      comp__allCliniquesValid,

      changeCollapse,
      getErrorClass,
      onAddClinic,
      onCreateUser,
    };
  },
});
app.config.compilerOptions.delimiters = ["$%", "%$"];
app.mount("#root");
