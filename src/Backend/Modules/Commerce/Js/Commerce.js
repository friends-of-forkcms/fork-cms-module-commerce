$(document).ready(function() {
  // Add support for the Fork CMS datepicker class on our Symfony Forms fields and re-init the datepicker
  // A DateType field only adds the class "inputDatefield" but that does not trigger the datepicker lib.
  $('.inputDatefield').addClass('inputDatefieldNormal');
  jsBackend.forms.datefields();
});
