var inputFullName = document.getElementById("fullName");
var inputEmail = document.getElementById("email");
var inputPassword = document.getElementById("password");
var postArea = document.getElementById("postText");

var regexFullName = /^[a-z]+( [a-z]+)+/i;
var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
var regexPassword = /^(?=.*\d)(?=.*[a-zA-Z])(?=.*[\W_])(?!.*\s)[a-zA-Z0-9\W_]+$/;

if (inputFullName != null) {
  inputFullName.addEventListener("input", () => {
    const value = inputFullName.value.trim();
    const errorFullName = document.getElementById("errorFullName");
    if (value.length == 0) {
      errorFullName.textContent = "Full name should not be empty.";
      $('#submit').attr('disabled', 'disabled');
    } else if (value.length > 25 || value.length < 5) {
      errorFullName.textContent = "Full name can have maximum 25 and minimum 5 character.";
      $('#submit').attr('disabled', 'disabled');
    } else if (!regexFullName.test(value)) {
      errorFullName.textContent = "Input must contain only letters from a to z and only one space.";
      $('#submit').attr('disabled', 'disabled');
    } else {
      errorFullName.textContent = "";
      $('#submit').removeAttr('disabled');      
    }
  });
}

if (inputEmail != null) {
  inputEmail.addEventListener("input", () => {
    const value = inputEmail.value.trim();
    const errorEmail = document.getElementById("errorEmail");
    if (value.length == 0) {
      errorEmail.textContent = "Email should not be empty.";
      $('#submit').attr('disabled', 'disabled');
    } else if (value.length > 40) {
      errorEmail.textContent = "Email must be less than 25 character.";
      $('#submit').attr('disabled', 'disabled');
    } else if (!regexEmail.test(value)) {
      errorEmail.textContent = "Not a valid email address.";
      $('#submit').attr('disabled', 'disabled');
    } else {
      errorEmail.textContent = "";
      $('#submit').removeAttr('disabled');
    }
  });
}

if (inputPassword != null) {
  inputPassword.addEventListener("input", () => {
    const value = inputPassword.value.trim();
    const errorPassword = document.getElementById("errorPassword");
    if (value.length == 0) {
      errorPassword.textContent = "Password should not be empty.";
      $('#submit').attr('disabled', 'disabled');
    } else if (value.length > 25) {
      errorPassword.textContent = "Password must be less than 25 character.";
      $('#submit').attr('disabled', 'disabled');
    } else if (!regexPassword.test(value)) {
      errorPassword.textContent = "Password should contain a special character, number, alphabet without space.";
      $('#submit').attr('disabled', 'disabled');
    } else {
      errorPassword.textContent = "";
      $('#submit').removeAttr('disabled');
    }
  });
}

if (postArea != null) {
  postArea.addEventListener("input", () => {
    const value = postArea.value.trim();
    const errorPostArea = document.getElementById("postArea");
    if (value.length == 0) {
      errorPostArea.textContent = "Post should not be empty.";
      $('#postBtn').attr('disabled', 'disabled');
    } else if (value.length > 100 || value.length < 10) {
      errorPostArea.textContent = "Maximum 100 characters and minimum 10 characters allowed";
      $('#postBtn').attr('disabled', 'disabled');
    } else {
      errorPostArea.textContent = "";
      $('#postBtn').removeAttr('disabled');
    }
  });
}

function fetchFormData(formInsertedData) {

  var formData = new FormData();
  $.each(formInsertedData, function (key, input) {
    formData.append(input.name, input.value);
  });

  return formData;
}

/**
 * Loader
 */
function showLoader() {
  document.getElementById("loader").style.display = "block";
}

function hideLoader() {
  document.getElementById("loader").style.display = "none";
}

/**
 * Loader for Home Page
 */
function showLeftLoader() {
  document.getElementById("leftLoader").style.display = "block";
}

function hideLeftLoader() {
  document.getElementById("leftLoader").style.display = "none";
}

function showRightLoader() {
  document.getElementById("rightLoader").style.display = "block";
}

function hideRightLoader() {
  document.getElementById("rightLoader").style.display = "none";
}