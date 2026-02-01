$(function () {
  const tblDom = $("#tbl-requests");

  // search params
  const requestFiltres = initSearchFilters();

  const requestDatatable = tblDom.DataTable({
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
        width: '3%',
        orderable: false,
        render: function (data, type, row, meta) {
          return `
            <div class="custom-control custom-checkbox">
              <input class="custom-control-input custom-control-input-secondary request-selection" type="checkbox" id="request-selection-${data}" value="${data}">
              <label for="request-selection-${data}" class="custom-control-label"></label>
            </div>
          `
        }
      },
      {
        targets: 1,
        data: "id",
        width: "5%",
      },
      {
        targets: 2,
        data: "status",
        width: "5%",
        render: function (data, type, row, meta) {
          const statuses = ["A valider", "En cours", "Archivé"];
          return statuses[data];
        },
      },
      {
        targets: 3,
        data: "requestType",
        width: "10%",
        render: function (data, type, row, meta) {
          if (!data) {
            return "";
          }

          const requestType =
            data.toUpperCase() == "INSTALLATION"
              ? "Proposition d'installation"
              : "Demande de remplaçement";

          return "<div>" + requestType + "</div>";
        },
      },
      {
        targets: 4,
        data: "applicant",
        width: "12%",
        render: function (data, type, row, meta) {
          if (!data) {
            return "";
          }
          const clinicUrl = getCleanUrl(tblDom.data("clinic-url"), data["id"]);
          const doctorUrl = getCleanUrl(tblDom.data("doctor-url"), data["id"]);

          const role = data.roles;

          const applicantName = data.establishment?.name ? data.establishment.name : `${data.name} ${data.surname}`

          let href = "#";
          if (role && role.length) {
            href = role[0].id == 6 ? doctorUrl : clinicUrl;
          }

          return (
            `<div><a href="${href}">${applicantName}</a></div>`
          );
        },
      },
      {
        targets: 5,
        data: "speciality",
        width: "15%",
        render: function (data, type, row, meta) {
          if (!data) {
            return "";
          }

          return "<div>" + data.name + "</div>";
        },
      },
      {
        targets: 6,
        data: "createdAt",
        width: "10%",
        render: function (data, type, row, meta) {
          if (!data) {
            return "";
          }

          return window.formatDate(data, false);
        },
      },
      {
        targets: 7,
        data: "startedAt",
        width: "8%",
        render: function (data, type, row, meta) {
          const dates = [];
          if (data) {
            dates.push(window.formatDate(data, false));
          }
          if (row.requestType !== 'installation' && row.endAt) {
            dates.push(window.formatDate(row.endAt, false));
          }

          return dates.join(' - ');
        },
      },
      {
        targets: 8,
        data: "lastSentAt",
        width: "8%",
        render: function (data, type, row, meta) {
          if (!data) {
            return "";
          }

          return window.formatDate(data, false);
        },
      },
      {
        targets: 9,
        data: "responseCount",
        width: "5%",
      },
      {
        targets: 10,
        data: "id",
        orderable: false,
        className: "text-right",
        width: "19%",
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(
            tblDom.data("delete-url"),
            data
          );
          const detailUrl = getCleanUrl(
            tblDom.data("detail-url"),
            data
          );
          const resendUrl = getCleanUrl(
            tblDom.data("resend-url"),
            data
          );
          const resendApplicantUrl = getCleanUrl(
            tblDom.data("resend-applicant-url"),
            data
          );
          const validateUrl = getCleanUrl(
            tblDom.data("validate-url"),
            data
          );
          const closeUrl = getCleanUrl(
            tblDom.data("close-url"),
            data
          );
          const actions = [];
          actions.push(
            '<a class="btn btn-sm btn-outline-info btn-edit" href="' +
              detailUrl +
              '" title="Editer"><i class="fas fa-edit"></i></a>'
          );
          
          if (row.status === 0) {
            actions.push(
              '<a class="btn btn-sm btn-outline-secondary ml-2 btn-edit" href="' +
                validateUrl +
                '" title="Valider"><i class="fas fa-check-double"></i></a>'
            );
          } else {
            actions.push(
              '<a class="btn btn-sm btn-outline-secondary ml-2 btn-edit" href="' +
                resendUrl +
                '" title="Relancer la demande"><i class="fas fa-paper-plane"></i></a>'
            );
          }

          actions.push(
            '<a class="btn btn-sm btn-outline-secondary ml-2 btn-edit" href="' +
              resendApplicantUrl +
              '" title="Relancer le demandeur"><i class="fas fa-sync-alt"></i></a>'
          );
          actions.push(
            '<a class="btn btn-sm btn-outline-secondary ml-2 btn-edit" href="' +
              closeUrl +
              '" title="Cloturer la demande"><i class="fas fa-times"></i></a>'
          );
          actions.push(
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="' +
              deleteUrl +
              '" data-id="' +
              data +
              '"><i class="fas fa-trash"></i></a>'
          );

          return "<div>" + actions.join("") + "</div>";
        },
      },
    ],
    serverSide: true,
    ajax: function (data, callback) {
      axios.get(tblDom.data("url"), { params: { filters: requestFiltres.getFilters(), ...data }})
          .then(response => callback(response.data))
          .catch(() => callback({ data: [] }))
    },
  });

  // delete
  $(document).on("deletedEvent", function () {
    requestDatatable.draw();
  });

  // filtres
  requestFiltres.addSearchElementEventListener(() => {
    requestDatatable.draw();
  });
});

/****************************/
/**** FILTRE MANAGEMENT *****/
/****************************/
function initSearchFilters() {
  const requestFiltres = {
    applicant: [],
    created_from: '',
    created_to: '',
    regions: [],
    specialities: [],
    status: '',
    started_from: '',
    started_to: '',
    end_from: '',
    end_to: '',
    sent_from: '',
    sent_to: '',
    response_count_min: '',
    response_count_max: '',
    count: 0,
  };

  function isEmptyValue(filterValue) {
    return filterValue === '' || filterValue === null || filterValue === undefined || (Array.isArray(filterValue) && filterValue.length === 0);
  }

  function loadFromUrl() {
    const urlQueryParams = new URLSearchParams(window.location.href.indexOf('?') >= 0 ? window.location.href.substring(window.location.href.indexOf('?')) : '');
    urlQueryParams.forEach((value, key) => {
      if (Object.hasOwn(requestFiltres, key)) {
        requestFiltres[key] = value;
      } else if (key.startsWith('regions')) {
        requestFiltres.regions.push(parseInt(value));
      } else if (key.startsWith('specialities')) {
        requestFiltres.specialities.push(parseInt(value));
      } else if (key.startsWith('applicant')) {
        requestFiltres.applicant.push(parseInt(value));
      }
    })

    for (const name in requestFiltres) {
      if (name === 'count' || !Object.hasOwn(requestFiltres, name)) continue;
      
      const filterValue = requestFiltres[name];
      
      if (!isEmptyValue(filterValue)) {
        requestFiltres.count++
      }
    }
    $('#filtre-request-counter').text(requestFiltres.count)
  }

  function addSearchElementEventListener (searchFn) {
    $('.select2-input').select2({
      theme: "bootstrap4",
      allowClear: true,
      placeholder: "- Tous -",
    });

    $("#filtre-request-demandeur").select2({
      theme: "bootstrap4",
      allowClear: true,
      placeholder: "- Tous -",
      ajax: {
        beforeSend: null,
        url: $('#filtre-request-demandeur').data("applicantUrl"),
        type: "get",
        dataType: "json",
        delay: 200,
        data: (params) => {
          return {
            search: params.term,
            roles: [5, 6]
          };
        },
        processResults: function(data) {
          return {
            results: data.map(user => ({ id: user.id, text: user.establishmentName ? user.establishmentName : `${user.name} ${user.surname}` }))
          };
        }
      },
    });

    $(".datepicker-wrapper").datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
    });

    $('#btn-request-search').on('click', function (e) {
      e.preventDefault();

      const filtreElement = document.querySelectorAll('.filtre-request-element')

      filtreElement.forEach((el) => {
        const value = Object.hasOwn(requestFiltres, el.name) ? requestFiltres[el.name] : '';
        $(el).val(value).trigger('change')
      });

      $('#filtre-request-modal').modal('show');
    });

    $('#filtre-request-reset').on('click', function (e) {
      e.preventDefault();

      Object.assign(requestFiltres, {
        applicant: [],
        created_from: '',
        created_to: '',
        regions: [],
        specialities: [],
        status: '',
        started_from: '',
        started_to: '',
        end_from: '',
        end_to: '',
        sent_from: '',
        sent_to: '',
        response_count_min: '',
        response_count_max: '',
        count: 0,  
      });

      $('#filtre-request-modal').modal('hide');
      $('#filtre-request-counter').text(requestFiltres.count)
      searchFn()
    });

    $('#filtre-request-validate').on('click', function (e) {
      e.preventDefault();

      const filtreElement = document.querySelectorAll('.filtre-request-element');
      const filtreData = {};
      let filtreCount = 0;

      filtreElement.forEach((el) => {
        let value = $(el).val();

        if (Array.isArray(value)) {
          value = value.filter(v => !isEmptyValue(v))
        }

        filtreData[el.name] = value;

        if (!isEmptyValue(value)) {
          filtreCount++;
        }
      });

      filtreData.count = filtreCount;

      Object.assign(requestFiltres, filtreData);

      $('#filtre-request-modal').modal('hide');
      $('#filtre-request-counter').text(filtreCount)

      searchFn()
    });
  }

  // initialize filters
  loadFromUrl()
  
  return {
    getFilters: () => {
      const result = {};

      for (const filterName in requestFiltres) {
        if (filterName === 'count' || !Object.hasOwn(requestFiltres, filterName)) continue;
        
        const filterValue = requestFiltres[filterName];
        
        if (!isEmptyValue(filterValue)) {
          result[filterName] = filterValue;
        }
      }

      return result;
    },
    addSearchElementEventListener: addSearchElementEventListener
  }
}