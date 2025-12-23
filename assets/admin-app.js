function initDeleteButtons() {
  const deleteModal = $("#delete-modal");
  const deleteForm = $("#form-delete-id");

  $(document).on("click", ".btn-delete", function (e) {
    deleteModal.modal("show");

    const button = $(this);
    const deleteUrl = button.data("url");
    const id = button.data("id");

    deleteModal.find("#id").val(id);
    deleteModal.find("#btn-delete").attr("data-url", deleteUrl);
  });

  $(document).on("click", "#btn-delete", function (e) {
    const id = deleteModal.find("#id").val();
    const deleteUrl = $(this).attr("data-url");
    const typeId = $("#typed-id");
    const typeIdValue = typeId.val();
    if (id !== typeIdValue) {
      typeId[0].classList.add("is-invalid");
    } else {
      axios.delete(deleteUrl).then(() => {
        deleteModal.modal("hide");
        document.dispatchEvent(
          new CustomEvent("deletedEvent", {
            detail: { deleted: true },
          })
        );
      });
    }
  });

  deleteModal.on("hidden.bs.modal", function () {
    const typeId = $("#typed-id");

    deleteModal.find("#id").val("");
    deleteModal.find("#btn-delete").attr("data-url", "");
    deleteForm[0].classList.remove("was-validated");
    typeId[0].classList.remove("is-invalid");
    typeId.val("");
  });
}

function initToggleSidebar() {
  $('body').on('collapsed.lte.pushmenu', () => {
    updateSidebarState(true);
  });

  $('body').on('shown.lte.pushmenu', () => {
    updateSidebarState(false);
  });

  function updateSidebarState(collapsed) {
    const saveUrl = document.body.dataset.sidebarUrlSave;

    axios.put(
      saveUrl,
      new URLSearchParams({
          collapsed: collapsed ? 1 : 0
      })
    );
  }
  
}

window.getCleanUrl = (url, id) =>
{
  let result = url;
  if (result) {
    result = result.replace('0000000000', id);
  }
  return result;
}

window.formatDate = (data, withHour = true) => {
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

document.addEventListener('DOMContentLoaded', () => {
  initDeleteButtons();

  initToggleSidebar();
})
