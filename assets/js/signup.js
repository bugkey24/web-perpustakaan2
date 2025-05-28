// Bootstrap validation
(function () {
  "use strict";
  window.addEventListener(
    "load",
    function () {
      var forms = document.getElementsByTagName("form");
      Array.prototype.filter.call(forms, function (form) {
        form.addEventListener(
          "submit",
          function (event) {
            // Validasi konfirmasi password sama dengan password
            var pass = form.querySelector("#password");
            var passConfirm = form.querySelector("#password_confirm");
            if (pass.value !== passConfirm.value) {
              passConfirm.setCustomValidity("Konfirmasi password tidak cocok.");
            } else {
              passConfirm.setCustomValidity("");
            }

            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add("was-validated");
          },
          false
        );
      });
    },
    false
  );
})();
