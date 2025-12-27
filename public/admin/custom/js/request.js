$(function () {
  const tblDom = $("#tbl-requests");

  const specialityDatatable = tblDom.DataTable({
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
        data: "request.id",
        width: "5%",
      },
      {
        targets: 2,
        data: "request.status",
        width: "5%",
        render: function (data, type, row, meta) {
          const statuses = ["A valider", "En cours", "Archivé"];
          return statuses[data];
        },
      },
      {
        targets: 3,
        data: "request.requestType",
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
        data: "request.applicant",
        width: "12%",
        render: function (data, type, row, meta) {
          if (!data) {
            return "";
          }
          const clinicUrl = getCleanUrl(tblDom.data("clinic-url"), data["id"]);
          const doctorUrl = getCleanUrl(tblDom.data("doctor-url"), data["id"]);

          const role = row["request"]["applicant"]["roles"];

          let href = "#";
          if (role && role.length) {
            href = role[0].id == 6 ? doctorUrl : clinicUrl;
          }

          return (
            '<div><a href="' +
            href +
            '">' +
            data.name +
            " " +
            data.surname +
            "</a></div>"
          );
        },
      },
      {
        targets: 5,
        data: "request.speciality",
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
        data: "request.createdAt",
        width: "10%",
        render: function (data, type, row, meta) {
          if (!data) {
            return "";
          }

          return formatDate(data, false);
        },
      },
      {
        targets: 7,
        data: "request.startedAt",
        width: "8%",
        render: function (data, type, row, meta) {
          const dates = [];
          if (data) {
            dates.push(formatDate(data, false));
          }
          if (row["request"]["endAt"]) {
            dates.push(formatDate(row["request"]["endAt"], false));
          }

          return dates.join(' - ');
        },
      },
      {
        targets: 8,
        data: "request.lastSentAt",
        width: "8%",
        render: function (data, type, row, meta) {
          if (!data) {
            return "";
          }

          return formatDate(data, false);
        },
      },
      {
        targets: 9,
        data: "responseCount",
        width: "5%",
      },
      {
        targets: 10,
        data: "request.id",
        orderable: false,
        className: "text-right",
        width: "19%",
        render: function (data, type, row, meta) {
          const deleteUrl = getCleanUrl(
            tblDom.data("delete-url"),
            row["request"]["id"]
          );
          const detailUrl = getCleanUrl(
            tblDom.data("detail-url"),
            row["request"]["id"]
          );
          const resendUrl = getCleanUrl(
            tblDom.data("resend-url"),
            row["request"]["id"]
          );
          const resendApplicantUrl = getCleanUrl(
            tblDom.data("resend-applicant-url"),
            row["request"]["id"]
          );
          const validateUrl = getCleanUrl(
            tblDom.data("validate-url"),
            row["request"]["id"]
          );
          const closeUrl = getCleanUrl(
            tblDom.data("close-url"),
            row["request"]["id"]
          );
          const actions = [];
          actions.push(
            '<a class="btn btn-sm btn-outline-info btn-edit" href="' +
              detailUrl +
              '" title="Editer"><i class="fas fa-edit"></i></a>'
          );
          
          if (row['request']['status'] === 0) {
            actions.push(
              '<a class="btn btn-sm btn-outline-secondary ml-2 btn-edit" href="' +
                resendUrl +
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
              resendApplicantUrl +
              '" title="Cloturer la demande"><i class="fas fa-times"></i></a>'
          );
          actions.push(
            '<a class="btn btn-sm btn-outline-danger ml-2 btn-delete" data-url="' +
              deleteUrl +
              '" data-id="' +
              row["request"]["id"] +
              '"><i class="fas fa-trash"></i></a>'
          );

          return "<div>" + actions.join("") + "</div>";
        },
      },
    ],
    serverSide: true,
    ajax: {
      url: tblDom.data("url"),
      type: "GET",
    },
  });

  // delete
  $(document).on("deletedEvent", function () {
    specialityDatatable.draw();
  });
});
