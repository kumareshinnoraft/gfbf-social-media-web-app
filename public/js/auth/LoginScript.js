/**
 * Ajax call to identify if login credentials are available in db.
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