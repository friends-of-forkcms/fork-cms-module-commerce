$(function(){
  $('#generateInvoiceNumber').click(function(){
    $.ajax({
      data : {
        fork : { module : 'Commerce', action : 'GenerateInvoiceNumber' },
        order : $(this).data('order')
      }
    }).done(function(response){
      $('#generateInvoiceNumber').replaceWith(response.data.invoiceNumber);
      $('#invoiceDate').replaceWith(response.data.invoiceDate);
    });
  });
});
