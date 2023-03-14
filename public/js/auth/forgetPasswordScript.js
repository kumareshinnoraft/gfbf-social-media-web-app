// On the forget password this ajax call will be made and send the mail to
// the server.
$('.forgetPasswordForm').submit(function (event) {
  event.preventDefault();

  // Creating the form data.
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