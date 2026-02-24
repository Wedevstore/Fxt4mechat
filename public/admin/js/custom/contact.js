$(document).on("click", ".__js_delete", function () {
  confirmationAlert($(this).data("url"), $(this).data("id"), "Yes, Delete It!");
});
const confirmationAlert = (
  url,
  data_id,
  button_test = "Yes, Confirmed it!"
) => {
  Swal.fire({
    title: "Are you sure?",
    text: "You won't be able to revert this!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: button_test,
  }).then((confirmed) => {
    if (confirmed.isConfirmed) {
      axios
        .delete(url, { data_id: data_id })
        .then((response) => {
          refreshDataTable();
          console.log(response);
          Swal.fire(
            response.data.msg,
            response.data.status == true ? "Deleted Successfully" : "error",
            response.data.status == true ? "success" : "error"
          );
        })
        .catch((error) => {
          console.log(error);
          Swal.fire(error.response.data);
          refreshDataTable();
        });
    }
  });
};
const refreshDataTable = () => {
  $("#dataTableBuilder").DataTable().ajax.reload();
};

var contactId; // Define contactId variable globally
$(document).on("click", ".__template_modal", function () {
  // Get the 'data-id' attribute value
  contactId = $(this).data("id");
  $("#template_modal").modal("show");
});

// When sending template link is clicked
$(document).on("click", ".send-template-link", function () {
  // Get the contactId from the globally defined variable
  var templateContactId = contactId;
  // Get the templateId from the clicked link's data attribute
  var templateId = $(this).data("template");
  // Get the base URL from the href attribute
  var baseUrl = $(this).attr("href");
  // Construct the full URL with both template_id and contact_id
  var fullUrl = baseUrl + "&contact_id=" + templateContactId;
  // Set the updated URL to the href attribute
  $(this).attr("href", fullUrl);
});

$(document).ready(function () {
  $(".dropdown-submenu").on("click", function (event) {
    $(".dropdown-submenu ul").removeClass("show");
    $(this).find("ul").toggleClass("show");
    event.stopPropagation();
  });
  $(document).on("click", function (event) {
    if (!$(event.target).closest(".dropdown-submenu").length) {
      $(".dropdown-submenu ul").removeClass("show");
    }
  });
});

$(document).ready(function () {
  $("#filterBTN").click(function () {
    $("#filterSection").toggleClass("show");
  });

  const advancedSearchMapping = (attribute) => {
    $("#dataTableBuilder").on("preXhr.dt", function (e, settings, data) {
      data[attribute.key] = attribute.value;
    });
  };

  $(document).on("change", ".filterable", function () {
    advancedSearchMapping({
      key: $(this).attr("id"),
      value: $(this).val(),
    });
  });

  $(document).on("click", "#reset", () => {
    $(".filterable").val("").trigger("change");
    $("#dataTableBuilder").DataTable().ajax.reload();
  });

  $(document).on("click", "#filter", () => {
    $("#checkAll").prop("checked", false).trigger("change");
    $("#dataTableBuilder").DataTable().ajax.reload();
  });
});

$(document).on("click", ".common-key", function () {
  var anyChecked = false;

  $(".custom-control-input").each(function () {
    if ($(this).prop("checked")) {
      anyChecked = true;
      return false;
    }
  });

  if (anyChecked) {
    $(".custom-dropdown").removeClass("d-none");
  } else {
    $(".custom-dropdown").addClass("d-none");
  }
});

$(document).ready(function () {
  $(document).on("change", "#checkAll", function () {
    $(".all-item-input").prop("checked", this.checked);
    var check = $(this).prop("checked");

    $(".custom-control-input").each(function () {
      if (check) {
        anyChecked = true;
        return false;
      }
    });

    if (check) {
      $(".custom-dropdown").removeClass("d-none");
    } else {
      $(".custom-dropdown").addClass("d-none");
    }
  });
});

$(document).on("click", ".blacklist", function () {
  // Show SweetAlert2 confirmation dialog
  Swal.fire({
    title: "Are you sure?",
    text: "You are about to blacklist the selected contacts!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, blacklist them!",
  }).then((result) => {
    if (result.isConfirmed) {
      let selector = $(".common-key:checked");
      let ids = [];
      $.each(selector, function () {
        let val = $(this).val();
        ids.push(val);
      });

      $.ajax({
        url: blacklistUrl,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
          ids: ids,
          is_blacklist: 1,
        },
        success: function (response) {
          if (response.status === 200) {
            refreshDataTable();
            toastr.success(response.message);
            $(".dropdown-menu").removeClass("show");
            $("#checkAll").prop("checked", false);
          } else {
            toastr.error(response.message);
          }
        },
        error: function (xhr) {
          console.log(xhr.responseText);
        },
      });
    }
  });
});

$(document).on("click", ".remove_blacklist", function () {
  // Show SweetAlert2 confirmation dialog
  Swal.fire({
    title: "Are you sure?",
    text: "You are about to remove the selected contacts from the blacklist!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, remove them!",
  }).then((result) => {
    if (result.isConfirmed) {
      let selector = $(".common-key:checked");
      let ids = [];
      $.each(selector, function () {
        let val = $(this).val();
        ids.push(val);
      });

      $.ajax({
        url: removeBlacklistUrl,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
          ids: ids,
          is_blacklist: 0,
        },
        success: function (response) {
          if (response.status === 200) {
            refreshDataTable();
            toastr.success(response.message);
            $(".dropdown-menu").removeClass("show");
            $("#checkAll").prop("checked", false);
          } else {
            toastr.error(response.message);
          }
        },
        error: function (xhr) {
          refreshDataTable();
          console.log(xhr.responseText);
        },
      });
    }
  });
});

$(document).on("click", ".remove_list", function () {
  // Show SweetAlert2 confirmation dialog
  Swal.fire({
    title: "Are you sure?",
    text: "You are about to remove the selected items from the list!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, remove them!",
  }).then((result) => {
    if (result.isConfirmed) {
      let selector = $(".common-key:checked");
      let ids = [];
      $.each(selector, function () {
        let val = $(this).val();
        ids.push(val);
      });

      $.ajax({
        url: removelistUrl,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
          ids: ids,
        },
        success: function (response) {
          refreshDataTable();

          if (response.status === 200) {
            toastr.success(response.message);
            $(".dropdown-menu").removeClass("show");
            $("#checkAll").prop("checked", false);
          } else {
            toastr.error(response.message);
          }
        },
        error: function (xhr) {
          refreshDataTable();
          console.log(xhr.responseText);
        },
      });
    }
  });
});

$(document).ready(function () {
  $(".dropdown-menu").on("click", ".add_list", function () {
    let listId = $(this).data("list-id");
    let selector = $(".common-key:checked");
    let ids = [];
    $.each(selector, function () {
      let val = $(this).val();
      ids.push(val);
    });

    // Show SweetAlert2 confirmation dialog
    Swal.fire({
      title: "Are you sure?",
      text: "You are about to add the selected items to this list.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, add them!",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: addListUrl,
          type: "POST",
          headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          data: {
            ids: ids,
            contact_list_id: listId,
          },
          success: function (response) {
            refreshDataTable();

            if (response.status === 200) {
              toastr.success(response.message);
              $(".dropdown-menu").removeClass("show");
              $("#checkAll").prop("checked", false);
            } else {
              toastr.error(response.message);
            }
          },
          error: function (xhr) {
            console.log(xhr.responseText);
            refreshDataTable();
            toastr.error("An error occurred while processing your request.");
          },
        });
      }
    });
  });
});

$(document).ready(function () {
  $(".dropdown-menu").on("click", ".add_segment", function () {
    let segmentId = $(this).data("segment-id");
    let selector = $(".common-key:checked");
    let ids = [];
    $.each(selector, function () {
      let val = $(this).val();
      ids.push(val);
    });

    // Show SweetAlert2 confirmation dialog
    Swal.fire({
      title: "Are you sure?",
      text: "You are about to add the selected items to this segment.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, add them!",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: addSegmentUrl,
          type: "POST",
          headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          data: {
            ids: ids,
            segment_id: segmentId,
          },
          success: function (response) {
            refreshDataTable();

            if (response.status === 200) {
              toastr.success(response.message);
              $(".dropdown-menu").removeClass("show");
              $("#checkAll").prop("checked", false);
            } else {
              toastr.error(response.message);
            }
          },
          error: function (xhr) {
            console.log(xhr.responseText);
            refreshDataTable();
            toastr.error("An error occurred while processing your request.");
          },
        });
      }
    });
  });
});

$(document).on("click", ".remove_segment", function () {
  let selector = $(".common-key:checked");
  let ids = [];
  $.each(selector, function () {
    let val = $(this).val();
    ids.push(val);
  });

  // Show SweetAlert2 confirmation dialog
  Swal.fire({
    title: "Are you sure?",
    text: "You are about to remove the selected items from this segment.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, remove them!",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: removeSegmentUrl,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
          ids: ids,
        },
        success: function (response) {
          refreshDataTable();

          if (response.status === 200) {
            toastr.success(response.message);
            $(".dropdown-menu").removeClass("show");
            $("#checkAll").prop("checked", false);
          } else {
            toastr.error(response.message);
          }
        },
        error: function (xhr) {
          console.log(xhr.responseText);
          refreshDataTable();
          toastr.error("An error occurred while processing your request.");
        },
      });
    }
  });
});

$(document).on("click", ".__view_details", (e) => {
  let contact_id = $(e.currentTarget).data("id").toString(); // Convert to string
  if (contact_id.trim() !== "") {
    let url = get_contact.replace("__contact_id__", contact_id); // Ensure placeholder matches
    axios
      .get(url, {
        params: {
          id: contact_id,
        },
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      })
      .then((response) => {
        console.log(response);
        if (response.data.status) {
          $("#contactViewModalBody").html(response.data.data);
          $("#contactViewModal").modal("show");
          $(".form-select").each(function () {
            $(this).select2({ dropdownParent: $(this).parent() });
          });
          $("#birthdate").flatpickr({
            dateFormat: "Y-m-d",
            static: true,
            allowInput: true,
          });
        } else {
          toastr.error(response.data.message);
        }
      })
      .catch((error) => {
        toastr.error(error.message);
      });
  } else {
    console.log("Contact ID is empty");
  }
});

$(document).on("submit", "#contact-details-update", async (event) => {
  event.preventDefault();
  debounceButton();
  try {
    const url = $("#contact-details-update").attr("action");
    const data = new FormData($("#contact-details-update")[0]);
    const response = await axios.post(url, data, {
      processData: false,
      contentType: false,
    });
    console.log(response);
    if (response.data.status) {
      toastr.success(response.data.message);
      $("#contact-details-update")[0].reset(); // Reset the form
      $("#contactViewModal").modal("hide");
      refreshDataTable();
    } else {
      toastr.error(response.data.message);
    }
  } catch (error) {
    console.log(error);
    if (typeof error.response.data === "string") {
      toastr.error(error.response.data.message);
    } else {
      const errors = error.response.data.errors || {};
      for (const key in errors) {
        const id = `#${key}`;
        console.log(id);
        $(id).addClass("is-invalid");
        $(id).siblings(".invalid-feedback").html(errors[key][0]);
        $(id).siblings(".invalid-feedback").show();
      }
      toastr.error(error.response.data.message);
    }
  } finally {
    debounceButton();
  }
});

$(document).on("click", ".delete_contacts", function () {
  let selector = $(".common-key:checked");
  let ids = [];
  $.each(selector, function () {
    let val = $(this).val();
    ids.push(val);
  });
  Swal.fire({
    title: "Are you sure?",
    text: "You won't be able to revert this!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: deleteUrl,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
          ids: ids,
        },
        success: function (response) {
          refreshDataTable();
          if (response.status) {
            Swal.fire("Deleted!", response.message, "success");
            $(".dropdown-menu").removeClass("show");
            $("#checkAll").prop("checked", false);
          } else {
            Swal.fire("Error!", response.message, "error");
          }
        },
        error: function (xhr) {
          refreshDataTable();
          console.log(xhr.responseText);
          Swal.fire("Error!", "Something went wrong.", "error");
        },
      });
    }
  });
});

$(document).on("click", "#download", function () {
  $("body").css("cursor", "progress");
  const csrfToken = $('meta[name="csrf-token"]').attr("content");
  const name = $("#name").val();
  const phone = $("#phone").val();
  const contact_list_id = $("#contact_list_id").val();
  const segments_id = $("#segments_id").val();
  const country_id = $("#country_id").val();
  const status = $("#status").val();
  const is_blacklist = $("#is_blacklist").val();
  const dataset = {
    name: name,
    phone: phone,
    contact_list_id: contact_list_id,
    segments_id: segments_id,
    country_id: country_id,
    status: status,
    is_blacklist: is_blacklist,
  };

  axios
    .post(download_url, dataset, {
      responseType: "blob",
    })
    .then((response) => {
      const blob = new Blob([response.data], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      const link = document.createElement("a");
      link.href = window.URL.createObjectURL(blob);
      link.setAttribute("download", "contacts.xlsx");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      $("body").css("cursor", "default");
    })
    .catch((error) => {
      console.error("Error downloading file:", error);
      alert("Failed to download the report. Please try again.");
      $("body").css("cursor", "default");
    });
});
