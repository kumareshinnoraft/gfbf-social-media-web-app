/**
 * This ajax call used to check the login credentials if credentials are correct
 * home page will be returned.
 */
$('#loginForm').submit(function (event) {
  event.preventDefault();
  var formData = fetchFormData($(this).serializeArray())
  $.ajax({
    url: '/login',
    type: "POST",
    data: formData,
    async: true,
    processData: false,
    contentType: false,
    beforeSend: function () {
      showLoader();
    },
    success: function (response) {
      $("#loginServerError").text(response.msg);
      if(response.result){
        window.location = "/home";
      }
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