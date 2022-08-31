$(document).ready(function () {
  // Add support for the Fork CMS datepicker class on our Symfony Forms fields and re-init the datepicker
  // A DateType field only adds the class "inputDatefield" but that does not trigger the datepicker lib.
  $(".inputDatefield").addClass("inputDatefieldNormal");
  jsBackend.forms.datefields();

  // Hide stock fields from product when stock tracking is disabled
  $("#product_from_stock").on("click", function () {
    $(".js-stock-tracking").toggle(this.checked);
  });
  if ($("#product_from_stock").prop("checked") === false) {
    $(".js-stock-tracking").hide();
  }
});
