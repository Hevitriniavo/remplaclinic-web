import { initDataTable, getCleanUrl, formatDate, isEmptyValue, initSelect2, initDatepicker } from 'admin-app'

$(function () {
  const tblDom = $("#tbl-directors");

  // search params
  const userFiltres = initSearchFilters();

  const directorDatatable = initDataTable('', tblDom, null, {
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
              <input class="custom-control-input custom-control-input-secondary director-selection" type="checkbox" id="director-selection-${data}" value="${data}">
              <label for="director-selection-${data}" class="custom-control-label"></label>
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
        width: '35%',
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
        width: '25%',
      },
      {
        targets: 5,
        data: 'createAt',
        width: '17%',
        render: function(data, type, row, meta) {
          if (!data) {
            return '';
          }

          return formatDate(data);
        }
      },
      {
        targets: 6,
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
    ajax: function (data, callback) {
      axios.get(tblDom.data("url"), { params: { filters: userFiltres.getFilters(), ...data }})
        .then(response => callback(response.data))
        .catch((err) => {
          console.error('DATATABLE ERROR: ', err)
          callback({ data: [] })
        })
    },
  })

  // delete
  $(document).on('deletedEvent', function() {
    directorDatatable.draw();
  });

  // filtres
  userFiltres.addSearchElementEventListener(() => {
    directorDatatable.draw();
  });
});

/****************************/
/**** FILTRE MANAGEMENT *****/
/****************************/
function initSearchFilters() {
  const userFiltres = {
    civility: '',
    status: '',
    clinic: [],
    created_from: '',
    created_to: '',
    count: 0,
  };

  function loadFromUrl() {
    const urlQueryParams = new URLSearchParams(window.location.href.indexOf('?') >= 0 ? window.location.href.substring(window.location.href.indexOf('?')) : '');
    urlQueryParams.forEach((value, key) => {
      if (Object.hasOwn(userFiltres, key)) {
        userFiltres[key] = value;
      } else if (key.startsWith('clinic')) {
        userFiltres.clinic.push(parseInt(value))
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
    initSelect2('#filtre-user-clinic', {
      placeholder: "- Tous -",
      ajax: {
        beforeSend: null,
        url: $('#filtre-user-clinic').data('clinicUrl'),
        type: "get",
        dataType: "json",
        delay: 200,
        data: (params) => {
          return {
            search: params.term,
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

    initDatepicker('.datepicker-wrapper')

    $('#btn-director-search').on('click', function (e) {
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
        clinic: [],
        created_from: '',
        created_to: '',
        count: 0,  
      });

      $('#filtre-user-modal').modal('hide');
      $('#filtre-user-counter').text(userFiltres.count)
      searchFn();
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

      searchFn();
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
