$(function () {
  const tblDom = $("#tbl-replacements");

  // search params
  const userFiltres = initSearchFilters()

  const replacementDatatable = tblDom.DataTable({
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
              <input class="custom-control-input custom-control-input-secondary replacement-selection" type="checkbox" id="replacement-selection-${data}" value="${data}">
              <label for="replacement-selection-${data}" class="custom-control-label"></label>
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
        width: '27%',
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
        data: "email",
        width: '20%',
      },
      {
        targets: 5,
        data: 'createAt',
        width: '15%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }

          return window.formatDate(data);
        }
      },
      {
        targets: 6,
        data: 'speciality',
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
        targets: 7,
        data: "id",
        orderable: false,
        className: "text-right",
        width: '10%',
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(tblDom.data('delete-url'), row['id']);
          const detailUrl = getCleanUrl(tblDom.data('detail-url'), row['id']);
          return (
            "<div>" +
            '<a class="btn btn-sm btn-outline-info btn-edit" href="'+ detailUrl +'"><i class="fas fa-edit"></i></a>' +
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="'+ deleteUrl +'" data-id="'+ row['id'] +'"><i class="fas fa-trash"></i></a>' +
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
    replacementDatatable.draw();
  });

  // filtres
  userFiltres.addSearchElementEventListener(() => {
    replacementDatatable.draw();
  })
});

/****************************/
/**** FILTRE MANAGEMENT *****/
/****************************/
function initSearchFilters() {
  const userFiltres = {
    civility: '',
    created_from: '',
    created_to: '',
    current_speciality: '',
    mobilities: [],
    specialities: [],
    status: '',
    count: 0,
  };

  function isEmptyValue(filterValue) {
    return filterValue === '' || filterValue === null || filterValue === undefined || (Array.isArray(filterValue) && filterValue.length === 0);
  }

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
      
      if (!isEmptyValue(filterValue)) {
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

    $("#filtre-user-created-from, #filtre-user-created-to").datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
    });

    $('#btn-replacement-search').on('click', function (e) {
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
        created_from: '',
        created_to: '',
        current_speciality: '',
        mobilities: [],
        specialities: [],
        status: '',
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

      Object.assign(userFiltres, filtreData);

      $('#filtre-user-modal').modal('hide');
      $('#filtre-user-counter').text(filtreCount)

      searchFn()
    });
  }

  // initialize filters
  loadFromUrl()
  
  return {
    getFilters: () => {
      const result = {};

      for (const filterName in userFiltres) {
        if (filterName === 'count' || !Object.hasOwn(userFiltres, filterName)) continue;
        
        const filterValue = userFiltres[filterName];
        
        if (!isEmptyValue(filterValue)) {
          result[filterName] = filterValue;
        }
      }

      return result;
    },
    addSearchElementEventListener: addSearchElementEventListener
  }
}