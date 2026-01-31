$(function () {
  const tblDom = $("#tbl-clinics");

  // search params
  const userFiltres = initSearchFilters();

  const clinicDatatable = tblDom.DataTable({
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
              <input class="custom-control-input custom-control-input-secondary clinic-selection" type="checkbox" id="clinic-selection-${data}" value="${data}">
              <label for="clinic-selection-${data}" class="custom-control-label"></label>
            </div>
          `
        }
      },
      {
        targets: 1,
        data: "id",
        width: '5%',
      },
      {
        targets: 2,
        data: "status",
        width: '5%',
        render: function (data, type, row, meta) {
          return data == 1 ? 'Actif' : 'Bloqué';
        }
      },
      {
        targets: 3,
        data: "name",
        width: '15%',
        render: function (data, type, row, meta) {
          if (!data) {
            return '';
          }
          
          return (
            "<div>" + data + " " + row['surname'] + "</div>"
          );
        }
      },
      {
        targets: 4,
        data: 'establishment',
        width: '15%',
        render: function (data, type, row, meta) {
          if (!data) {
            return '';
          }

          return (
            "<div>" + data.name + "</div>"
          );
        }
      },
      {
        targets: 5,
        data: "email",
        width: '10%',
      },
      {
        targets: 6,
        data: 'createAt',
        width: '12%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }

          return formatDate(data);
        }
      },
      {
        targets: 7,
        data: 'speciality',
        width: '12%',
        render: function (data, type, row, meta) {
          if (!data) {
            return '';
          }

          return (
            "<div>" + data.name + "</div>"
          );
        }
      },
      {
        targets: 8,
        data: 'subscription',
        width: '10%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }
          return formatDate(data.endAt, false);
        }
      },
      {
        targets: 9,
        data: 'subscription',
        width: '5%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }
          return data.installationCount ? data.installationCount : 0;
        }
      },
      {
        targets: 10,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '8%',
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id']);
          const detailUrl = getCleanUrl(tblDom.data('detail-url'), row['id']);
          return (
            "<div>" +
            '<a class="btn btn-sm btn-outline-info btn-edit mb-2" href="'+ detailUrl +'"><i class="fas fa-edit"></i></a>' +
            '<a class="btn btn-sm btn-outline-danger mb-2 ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
            "</div>"
          );
        },
      },
    ],
    serverSide: true,
    ajax: function (data, callback) {
      axios.get(tblDom.data("url"), { params: { filters: userFiltres.getFilters(), ...data }})
        .then(response => callback(response.data))
        .catch(() => callback({ data: [] }))
    },
  });

  // delete
  $(document).on('deletedEvent', function() {
    clinicDatatable.draw();
  });

  // filtres
  userFiltres.addSearchElementEventListener(() => {
    clinicDatatable.draw();
  });
});

/****************************/
/**** FILTRE MANAGEMENT *****/
/****************************/
function initSearchFilters() {
  const userFiltres = {
    civility: '',
    status: '',
    specialities: [],
    director: '',
    installation_count_min: '',
    installation_count_max: '',
    created_from: '',
    created_to: '',
    abonnement_from: '',
    abonnement_to: '',
    count: 0,
  };

  function loadFromUrl() {
    const urlQueryParams = new URLSearchParams(window.location.href.indexOf('?') >= 0 ? window.location.href.substring(window.location.href.indexOf('?')) : '');
    urlQueryParams.forEach((value, key) => {
      if (Object.hasOwn(userFiltres, key)) {
        userFiltres[key] = value;
      } else if (key.startsWith('mobilities')) {
        userFiltres.mobilities.push(parseInt(value));
      } else if (key.startsWith('specialities')) {
        userFiltres.specialities.push(parseInt(value));
      }
    })

    for (const name in userFiltres) {
      if (name === 'count' || !Object.hasOwn(userFiltres, name)) continue;
      
      const filterValue = userFiltres[name];
      
      if ((filterValue !== '' && !Array.isArray(filterValue)) || filterValue.length > 0) {
        userFiltres.count++
      }
    }
    $('#filtre-user-counter').text(userFiltres.count)
  }

  function addSearchElementEventListener (searchFn) {
    $('.select2-input').select2({
      theme: "bootstrap4",
      allowClear: true,
      placeholder: "- Tous -",
    });

    $('#filtre-user-directeur').select2({
      theme: "bootstrap4",
      allowClear: true,
      placeholder: "- Choisir une option -",
      ajax: {
        beforeSend: null,
        url: $('#filtre-user-directeur').data('directorUrl'),
        type: "get",
        dataType: "json",
        delay: 200,
        data: (params) => {
          return {
            search: params.term,
            role: 7
          }
        },
        processResults: function(data) {
          return {
            results: data.map(user => ({ id: user.id, text: user.establishmentName ? user.establishmentName : `${user.name} ${user.surname}` }))
          }
        }
      },
    });

    $(".datepicker-wrapper").datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
    });

    $('#btn-clinic-search').on('click', function (e) {
      e.preventDefault();

      const filtreElement = document.querySelectorAll('.filtre-user-element')

      filtreElement.forEach((el) => {
        const value = Object.hasOwn(userFiltres, el.name) ? userFiltres[el.name] : '';
        $(el).val(value).trigger('change')
      });

      $('#filtre-user-modal').modal('show');
    });

    $('#filtre-user-reset').on('click', function (e) {
      e.preventDefault();

      Object.assign(userFiltres, {
        civility: '',
        status: '',
        specialities: [],
        director: '',
        installation_count_min: '',
        installation_count_max: '',
        created_from: '',
        created_to: '',
        abonnement_from: '',
        abonnement_to: '',
        count: 0,  
      });

      $('#filtre-user-modal').modal('hide');
      $('#filtre-user-counter').text(userFiltres.count)
      searchFn()
    });

    $('#filtre-user-validate').on('click', function (e) {
      e.preventDefault();

      const filtreElement = document.querySelectorAll('.filtre-user-element');
      const filtreData = {};
      let filtreCount = 0;

      filtreElement.forEach((el) => {
        const value = $(el).val();

        filtreData[el.name] = value;

        if (value) {
          filtreCount++;
        }
      });

      filtreData.count = filtreCount;

      Object.assign(userFiltres, filtreData);

      $('#filtre-user-modal').modal('hide');
      $('#filtre-user-counter').text(filtreCount)

      searchFn()
    });
  }

  // initialize filters
  loadFromUrl()
  
  return {
    getFilters: () => userFiltres,
    addSearchElementEventListener: addSearchElementEventListener
  }
}