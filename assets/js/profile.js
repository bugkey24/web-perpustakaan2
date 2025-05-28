$(document).ready(function () {
  $("#foto").on("change", function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        $("#previewFoto").html(
          '<img src="' +
            e.target.result +
            '" alt="Preview Foto" class="profile-img mb-3">'
        );
        $("#btnClearFoto").show();
        $("#currentFotoProfil").hide();
      };
      reader.readAsDataURL(file);
    } else {
      $("#previewFoto").html("");
      $("#btnClearFoto").hide();
      $("#currentFotoProfil").show();
    }
  });

  $("#btnClearFoto").on("click", function () {
    $("#foto").val("");
    $("#previewFoto").html("");
    $(this).hide();
    $("#currentFotoProfil").show();
  });

  if ($("#previewFoto img").length) {
    $("#btnClearFoto").show();
  }
});
