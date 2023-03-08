/**
 * Ajax call to identify if login credentials are available in db.
 */
$('.forgetPasswordForm').submit(function (event) {
  event.preventDefault();
  var formData = fetchFormData($(this).serializeArray());

  $.ajax({
    url: '/forgetPassword',
    type: "POST",
    data: formData,
    async: true,
    processData: false,
    contentType: false,
    beforeSend: function () {
      showLoader();
    },
    success: function (response) {
      $("#errorEmail").text(response.msg);
      hideLoader();
    },
    error: function (jqXHR, textStatus, errorThrown) {
      hideLoader();
    },
    complete: function () {
      hideLoader();
    }
  });
});