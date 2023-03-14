// This JS file identified if the new password and re-type password is matched.
const inputPassword2 = document.getElementById("password2");

inputPassword2.addEventListener("input", () => {
  const value = inputPassword2.value.trim();
  const errorPassword = document.getElementById("errorPassword2");
  if(inputPassword.value.trim() != value) {
    errorPassword.textContent = "Password not match.";
    $('#submit').attr('disabled','disabled');
  }else {
    errorPassword.textContent = "";
    $('#submit').removeAttr('disabled');
  }
});