$(document).ready(function () {
 // Confirmation Alert Functionality
  const confirmationAlert = (url, dataId, buttonText = "Yes, Confirm!") => {
    Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: buttonText,
      confirmButtonColor: "#ff0000",
      preConfirm: () => {
        Swal.showLoading();
        return axios
          .get(url, { params: { data_id: dataId } })
          .then((response) => {
            Swal.fire({
              icon: response.data.status ? "success" : "error",
              title: response.data.message,
            }).then(() => {
              if (response.data.status) location.reload();
            });
          })
          .catch((error) => {
            console.error(error);
            Swal.fire(
              "Error",
              "An error occurred while processing the request."
            );
          });
      },
    });
  };

  // Event handler for delete confirmation
  $(document).on("click", ".__js_delete", function () {
    confirmationAlert(
      $(this).data("url"),
      $(this).data("id"),
      "Yes, Delete It!"
    );
  });

  // Toggle SMS Provider fields based on selection
  function toggleSMSFields(provider) {
    $(".sms-provider").addClass("d-none");
    $(`.sms-provider[data-provider="${provider}"]`).removeClass("d-none");
  }

  const selectedProvider = $('input[name="active_sms_provider"]:checked').val();
  toggleSMSFields(selectedProvider);

  $('input[name="active_sms_provider"]').on("change", function () {
    toggleSMSFields($(this).val());
  });

  // Copy text to clipboard functionality
  $(".copy-text").on("click", function () {
    const inputField = $(this).closest(".input-group").find("input");
    inputField.select();
    document.execCommand("copy");
    toastr.success("Copied!");
  });

  // Advanced search functionality
  const advancedSearchMapping = (attribute) => {
    $("#dataTableBuilder").on("preXhr.dt", function (e, settings, data) {
      data[attribute.name] = attribute.value;
    });
  };

  $(document).on("change", "#filterForm select", function () {
    advancedSearchMapping({ name: $(this).attr("name"), value: $(this).val() });
  });

  // Handle status updates
  $(document).on("click", ".__js_update_status", function () {
    confirmationAlert(
      $(this).data("url"),
      $(this).data("status"),
      "Yes, Update It!"
    );
  });

  // Flatpickr initialization for scheduling
  $("#schedule_time").flatpickr({
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    static: true,
    minuteIncrement: 5,
    allowInput: true,
  });

  // Handle SMS message preview and character count updates
  const $messageBody = $("#body");
  const $totalDisplay = $("#total-sms");
  const $currentCharCount = $("#current-char-count");
  const $messageBodyPreview = $("#message_body_text");
  let totalContacts = 0;

  // Ensure the message body exists
  if ($messageBody.length > 0) {
    const updateCharacterCount = () => {
      const messageLength = $messageBody.val().length;
      $currentCharCount.text(messageLength);
    };

    const updateTotalSMS = () => {
      const messageLength = $messageBody.val().length;
      const smsCount =
        messageLength <= 160
          ? totalContacts
          : Math.ceil(messageLength / 160) * totalContacts;
      $totalDisplay.text(smsCount);
    };

    const messageBodyPreview = () => {
      $messageBodyPreview.html($messageBody.val());
    };

    $messageBody.on("input", function () {
      updateCharacterCount();
      updateTotalSMS();
      messageBodyPreview();
    });
    // Count SMS contacts based on selection
    function countSMSContact(url) {
      const params = {
        contact_list_ids: $("#contact_list_ids").val(),
        segment_ids: $("#segment_ids").val(),
        country_id: $("#country_id").val(),
      };

      axios
        .get(url, { params })
        .then((response) => {
          totalContacts = response.data;
          $("#total-display").text(totalContacts);
          updateTotalSMS();
        })
        .catch((error) => {
          toastr.error(error.message);
        });
    }

    // Fetch SMS template and update message body
    $("#sms_template_id").on("change", function () {
      const templateId = $(this).val();
      if (templateId.trim()) {
        axios
          .get(sms_template.replace("__template_id__", templateId), {
            params: { id: templateId },
          })
          .then((response) => {
            $("#body").val(response.data.data[0].body);
            updateCharacterCount();
            updateTotalSMS();
            messageBodyPreview();
          })
          .catch((error) => {
            toastr.error(error.message);
          });
      } else {
        $("#body").val("");
        updateCharacterCount();
      }
    });

    countSMSContact(sms_contact_count_url);

    // Trigger initial updates
    updateCharacterCount();
    updateTotalSMS();
    messageBodyPreview();
  } else {
    // console.error("Message body element not found");
  }

  // Show/hide schedule time field based on checkbox status
  $("#send_scheduled").on("change", function () {
    const isChecked = $(this).prop("checked");
    $("#schedule_time_div").toggle(isChecked);
    $("#schedule_time").prop("required", isChecked);
  });

  // Validate scheduled time before form submission
  $("#campaign_store").on("submit", function (event) {
    if (
      $("#send_scheduled").prop("checked") &&
      !$("#schedule_time").val().trim()
    ) {
      event.preventDefault();
      toastr.error("Please select a schedule time.");
    }
  });

  // Live search initialization
  const getLiveSearch = (searchUrl, placeholder = "Select Value") => ({
    placeholder,
    minimumInputLength: 2,
    ajax: {
      type: "GET",
      dataType: "json",
      url: searchUrl,
      data: (params) => ({ search: params.term }),
      delay: 400,
      headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
      processResults: (data) => ({ results: data }),
      cache: true,
    },
  });

  $("#contact_id").select2(
    getLiveSearch($("#contact_id").data("url"), "Select contact")
  );
  // Handle contact form submission
  $("#__contact_modal_form").on("submit", function (event) {
    event.preventDefault();
    const form = $(this);
    $.ajax({
      type: "POST",
      url: form.attr("action"),
      data: form.serialize(),
      success: (response) => {
        toastr.success(response.message);
        form[0].reset();
        setTimeout(() => $("#storeContactModal").modal("hide"), 300);
      },
      error: (xhr) => {
        if (xhr.responseText) toastr.error(xhr.responseText);
        $.each(xhr.responseJSON.errors || [], (key, value) => {
          $(`#${key}`)
            .addClass("is-invalid")
            .siblings(".invalid-feedback")
            .html(value[0])
            .show();
        });
      },
    });
  });
});

$(document).on("change", ".status-change1", function () {
  const checkbox = $(this);
  const url = checkbox.data("url");
  const isChecked = checkbox.is(":checked");
  axios
    .post(url, {
      status: isChecked ? 1 : 0,
      id: checkbox.data("id"),
    })
    .then((response) => {
      if (response.data.status) {
        toastr.success(response.data.message || "Status updated successfully.");
      } else {
        toastr.error(response.data.message || "Failed to update status.");
      }
    })
    .catch((error) => {
      console.error(error);
      toastr.error("An error occurred while updating the status.");
    });
});
$(document).ready(function() {
  $('#filterBTN').click(function() {
      $('#filterSection').toggleClass('show');
  });

  const advancedSearchMapping = (attribute) => {
      $('#dataTableBuilder').on('preXhr.dt', function(e, settings, data) {
          data[attribute.key] = attribute.value;
      });
  }

  $(document).on('change', '.filterable', function() {
      advancedSearchMapping({
          key: $(this).attr('id'),
          value: $(this).val(),
      });
  });

  $(document).on('click', '#reset', () => {
      $('.filterable').val('').trigger('change');
      $('#dataTableBuilder').DataTable().ajax.reload();
  });

  $(document).on('click', '#filter', () => {
      $('#checkAll').prop('checked', false).trigger('change');
      $('#dataTableBuilder').DataTable().ajax.reload();
  });
});
flatpickr("#created_at", {
  mode: "range",
  dateFormat: "Y-m-d",
});

$(document).on('click', '.edit', function(e) {
  let template_id = $(this).data('id');
  if (template_id !== '') {
      let url = $(this).data('url');
      axios.get(url, {
              params: {
                  id: template_id
              },
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          })
          .then(response => {
              $('#SMSTemplateModalBody').html(response.data.data);
              $('#SMSTemplate').modal('show');
          })
          .catch(error => {
              toastr.error(error.message);
          });
  } else {}
});