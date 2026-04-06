(function () {
  'use strict';
  var forms = document.querySelectorAll('.needs-validation');
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();   // stop the POST
        event.stopPropagation();
      }
      form.classList.add('was-validated'); // trigger Bootstrap styles
    }, false);
  });

  var form = document.querySelector('.needs-validation');
  form.addEventListener('submit', function (event) {
    var password = document.getElementById('password');
    var confirmPassword = document.getElementById('confirmPassword');

    if (password.value !== confirmPassword.value) {
      event.preventDefault();
      event.stopPropagation();
      confirmPassword.setCustomValidity("Passwords do not match");
    } else {
      confirmPassword.setCustomValidity(""); // clear error
    }

    form.classList.add('was-validated');
  }, false);
})();
