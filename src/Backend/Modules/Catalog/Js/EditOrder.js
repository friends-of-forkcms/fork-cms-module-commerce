$(function(){
  $('#generateInvoiceNumber').click(function(){
    $.ajax({
      data : {
        fork : { module : 'Catalog', action : 'GenerateInvoiceNumber' },
        order : $(this).data('order')
      }
    }).done(function(response){
      $('#generateInvoiceNumber').replaceWith(response.data.invoiceNumber);
      $('#invoiceDate').replaceWith(response.data.invoiceDate);
    });
  });
});
