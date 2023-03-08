$('.profile-pic').click(() => {
  $('.file-upload').click();
});

$(".file-upload").on('change', function () {
  readURL(this);
});

function readURL(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    
    reader.onload = function (e) {
      $('.profile-pic').attr('src', e.target.result);
    }
    reader.readAsDataURL(input.files[0]);
  }
}

// OTP timer is the variables of timer.
var otpTimer;

// Start the OTP timer
function startOTPTimer() {

  var countdown = 10;
  otpTimer = setInterval(function() {

    $('#otpTimer').text(countdown + ' seconds remaining');

    countdown--;

    if (countdown < 0) {
      clearInterval(otpTimer);
      $('#resendOTP').prop('disabled', false);
      $('#otpTimer').text('');
    }
  }, 1000);
}

/**
 * Ajax call to identify if OTP is sent then we would show the OTP box
 */
$('#register-form').submit(function (event) {
  // Preventing default form submission.
  event.preventDefault();

  // Creating the object of the form data and inserting the files.
  var formData = fetchFormData($(this).serializeArray());

  var fileData = $('input[type="file"]')[0].files[0];
  formData.append("image", fileData);

  // Calling server and sending values with 
  $.ajax({
    url: '/register',
    type: "POST",
    data: formData,
    async: true,
    processData: false,
    contentType: false,
    beforeSend: function () {
      showLoader();
    },
    success: function (data) {

      if (data.mail) {
        $(".registerFormBox").css("display", "none");
        $(".otpFormBox").css({ "display": "flex" });
        if (!otpTimer) {
          $('#resendOTP').prop('disabled', true);
          startOTPTimer();
        }
      } else if(data.msg) {
        $("#registerServerError").text(data.msg);
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      $('#registerServerError').text('An error occurred while processing your request.');
      hideLoader();
    },
    complete: function () {
      hideLoader();
    }
  });
});

/**
 * Send the response to the OTP without Page refresh.
 */
$('#otpForm').submit(function (event) {
  event.preventDefault();
  // Insert other values of the form data.
  var formData = fetchFormData($(this).serializeArray())

  checkOTP(formData);
});

function checkOTP(formData){
  $.ajax({
    url: '/otp',
    type: "POST",
    data: formData,
    async: true,
    processData: false,
    contentType: false,
    beforeSend: function () {
      showLoader();
    },
    success: function (data) {
      if (data.otp) {
        window.location = "/login";
        hideLoader();
      } else if(data.msg) {
        $("#otpServerError").text(data.msg);
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      $('#otpServerError').text('An error occurred while processing your request.');
      hideLoader();
    },
    complete: function () {
      hideLoader();
    }
  });
}

function resendOTP(){
  $.ajax({
    url: '/resendOTP',
    type: "POST",
    beforeSend: function () {
      showLoader();
    },
    success: function (data) {
      if(data.msg){
        $('#otpServerError').text('Mail sent');
        startOTPTimer();
        $('#resendOTP').prop('disabled', true);
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      $('#otpServerError').text('An error occurred while processing your request.');
      hideLoader();
    },
    complete: function () {
      hideLoader();
    }
  });
}

/* OTP Text Field */
let digitValidate = function (element) {
  element.value = element.value.replace(/[^0-9]/g, '');
}

let tabChange = function (value) {

  let element = document.querySelectorAll('input');

  if (element[value - 1].value != '') {

    element[value].focus()

  } else if (ele[val - 1].value == '') {

    element[value - 2].focus()
  }
}

