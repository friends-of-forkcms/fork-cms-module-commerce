$(function () {
  $("[data-toggle]").on("change", function () {
    $($(this).data("toggle")).toggleClass($(this).data("toggleClass"));
  });

  $(".card.order-address").click(function () {
    $('[data-type="' + $(this).data("type") + '"]')
      .removeClass("order-address-checked")
      .find("input[type=radio]")
      .prop("checked", false);

    $(this)
      .addClass("order-address-checked")
      .find("input[type=radio]")
      .prop("checked", true);
  });
});

// $(function () {
//   $(document).on('submit', 'form[name=account_customer]', function (e) {
//     e.preventDefault()
//
//     var requestData = $(this).serializeArray()
//
//     requestData.push({
//       name: 'fork[module]',
//       value: 'Commerce'
//     })
//     requestData.push({
//       name: 'fork[action]',
//       value: 'CheckoutAccount'
//     })
//
//     $(this).find('.is-invalid').removeClass('is-invalid')
//     $(this).find('.invalid-feedback').remove()
//
//     $.ajax(
//       {
//         method: 'POST',
//         data: requestData,
//         cache: false
//       }
//     ).done(function (response) {
//       showStep('#stepTwo', response.data.html, true)
//     }).fail(function (response) {
//       $.each(response.responseJSON.data.errors, function (field, error) {
//         var element = $('[name="account_customer[' + field + ']"]')
//
//         element.addClass('is-invalid')
//
//         $('<div>').addClass('invalid-feedback').html(error).insertAfter(element)
//       })
//     })
//   })
//
//   $(document).on('submit', 'form[name=account_register]', function (e) {
//     e.preventDefault()
//
//     // Build request data
//     var requestData = $(this).serializeArray()
//     requestData.push({
//       name: 'fork[module]',
//       value: 'Commerce'
//     })
//     requestData.push({
//       name: 'fork[action]',
//       value: 'CheckoutAccount'
//     })
//     requestData.push({
//       name: 'type',
//       value: 'register'
//     })
//
//     $.ajax(
//       {
//         method: 'POST',
//         data: requestData,
//         cache: false
//       }
//     ).done(function (response) {
//       // If our form has an error
//       var stepTwo = $('#stepTwo')
//
//       stepTwo.find('.panel-body').html(response.data.html)
//
//       if (response.data.hasErrors) {
//         $('html,body').animate({
//           scrollTop: stepTwo.closest('.card').offset().top - 100
//         }, 500)
//       } else {
//         switch (response.data.nextStep) {
//           case 'shipmentAddress':
//             loadGuestShipmentAddress(true)
//             break
//           case 'shipmentMethods':
//             loadGuestShipmentAddress(false)
//             loadShipmentMethods(true)
//             break
//         }
//       }
//     })
//   })
//
//   $(document).on('submit', 'form[name=account_guest]', function (e) {
//     e.preventDefault()
//
//     // Build request data
//     var requestData = $(this).serializeArray()
//     requestData.push({
//       name: 'fork[module]',
//       value: 'Commerce'
//     })
//     requestData.push({
//       name: 'fork[action]',
//       value: 'CheckoutAccount'
//     })
//     requestData.push({
//       name: 'type',
//       value: 'guest'
//     })
//
//     $.ajax(
//       {
//         method: 'POST',
//         data: requestData,
//         cache: false
//       }
//     ).done(function (response) {
//       // If our form has an error
//       var stepTwo = $('#stepTwo')
//
//       stepTwo.find('.panel-body').html(response.data.html)
//
//       if (response.data.hasErrors) {
//         $('html,body').animate({
//           scrollTop: stepTwo.closest('.card').offset().top - 100
//         }, 500)
//       } else {
//         switch (response.data.nextStep) {
//           case 'shipmentAddress':
//             loadGuestShipmentAddress(true)
//             break
//           case 'shipmentMethods':
//             loadGuestShipmentAddress(false)
//             loadShipmentMethods(true)
//             break
//         }
//       }
//     })
//   })
//
//   $(document).on('submit', 'form[name=account_shipment_address]', function (e) {
//     e.preventDefault()
//
//     // Build request data
//     var requestData = $(this).serializeArray()
//     requestData.push({
//       name: 'fork[module]',
//       value: 'Commerce'
//     })
//     requestData.push({
//       name: 'fork[action]',
//       value: 'GuestShipmentAddress'
//     })
//
//     $.ajax(
//       {
//         method: 'POST',
//         data: requestData,
//         cache: false
//       }
//     ).done(function (response) {
//       // If our form has an error
//       var stepThree = $('#stepThree')
//
//       stepThree.find('.panel-body').html(response.data.html)
//
//       if (response.data.hasErrors) {
//         $('html,body').animate({
//           scrollTop: stepThree.closest('.card').offset().top - 100
//         }, 500)
//       } else {
//         loadShipmentMethods(true)
//       }
//     })
//   })
//
//   $(document).on('submit', 'form[name=checkout_shipment_method]', function (e) {
//     e.preventDefault()
//
//     // Build request data
//     var requestData = $(this).serializeArray()
//     requestData.push({
//       name: 'fork[module]',
//       value: 'Commerce'
//     })
//     requestData.push({
//       name: 'fork[action]',
//       value: 'ShipmentMethods'
//     })
//
//     $.ajax(
//       {
//         method: 'POST',
//         data: requestData,
//         cache: false
//       }
//     ).done(function (response) {
//       // If our form has an error
//       var stepFour = $('#stepFour')
//
//       stepFour.find('.panel-body').html(response.data.html)
//
//       if (response.data.hasErrors) {
//         $('html,body').animate({
//           scrollTop: stepFour.closest('.card').offset().top - 100
//         }, 500)
//       } else {
//         loadPaymentMethods(true)
//       }
//     })
//   })
//
//   $(document).on('submit', 'form[name=checkout_payment_method]', function (e) {
//     e.preventDefault()
//
//     // Build request data
//     var requestData = $(this).serializeArray()
//     requestData.push({
//       name: 'fork[module]',
//       value: 'Commerce'
//     })
//     requestData.push({
//       name: 'fork[action]',
//       value: 'PaymentMethods'
//     })
//
//     $.ajax(
//       {
//         method: 'POST',
//         data: requestData,
//         cache: false
//       }
//     ).done(function (response) {
//       // If our form has an error
//       var step = $('#stepFive')
//
//       step.find('.panel-body').html(response.data.html)
//
//       if (response.data.hasErrors) {
//         $('html,body').animate({
//           scrollTop: step.closest('.card').offset().top - 100
//         }, 500)
//       } else {
//         loadConfirmOrder(true)
//       }
//     })
//   })
//
//   $(document).on('submit', 'form[name=checkout_confirm_order]', function (e) {
//     e.preventDefault()
//
//     // Build request data
//     var requestData = $(this).serializeArray()
//     requestData.push({
//       name: 'fork[module]',
//       value: 'Commerce'
//     })
//     requestData.push({
//       name: 'fork[action]',
//       value: 'ConfirmOrder'
//     })
//
//     $.ajax(
//       {
//         method: 'POST',
//         data: requestData,
//         cache: false
//       }
//     ).done(function (response) {
//       // If our form has an error
//       var step = $('#stepSix')
//
//       step.find('.panel-body').html(response.data.html)
//
//       if (response.data.hasErrors) {
//         $('html,body').animate({
//           scrollTop: step.closest('.card').offset().top - 100
//         }, 500)
//       } else {
//         window.location = response.data.nextStep
//       }
//     })
//   })
//
//   $(document).on('change', 'form[name=checkout_payment_method] input[name*=payment_method][type=radio]', function (e) {
//     e.preventDefault()
//
//     // Build request data
//     var requestData = $(this).serializeArray()
//     requestData.push({
//       name: 'fork[module]',
//       value: 'Commerce'
//     })
//     requestData.push({
//       name: 'fork[action]',
//       value: 'PaymentMethods'
//     })
//
//     $.ajax(
//       {
//         method: 'POST',
//         data: requestData,
//         cache: false
//       }
//     ).done(function (response) {
//       // If our form has an error
//       var step = $('#stepFive')
//
//       step.find('.panel-body').html(response.data.html)
//     })
//   })
//
//   function loadGuestShipmentAddress (show) {
//     $.ajax(
//       {
//         method: 'POST',
//         data: {
//           fork: {
//             module: 'Commerce',
//             action: 'GuestShipmentAddress'
//           }
//         },
//         cache: false
//       }
//     ).done(function (response) {
//       showStep('#stepThree', response.data.html, show)
//
//       // loadStep(
//       //   response.data.html,
//       //   '#headingThree',
//       //   '#stepThree',
//       //   show
//       // )
//     })
//   }
//
//   function loadShipmentMethods (show) {
//     $.ajax(
//       {
//         method: 'POST',
//         data: {
//           fork: {
//             module: 'Commerce',
//             action: 'ShipmentMethods'
//           }
//         },
//         cache: false
//       }
//     ).done(function (response) {
//       showStep('#stepFour', response.data.html, show)
//       // loadStep(
//       //   response.data.html,
//       //   '#headingFour',
//       //   '#stepFour',
//       //   show
//       // )
//     })
//   }
//
//   function loadPaymentMethods (show) {
//     $.ajax(
//       {
//         method: 'POST',
//         data: {
//           fork: {
//             module: 'Commerce',
//             action: 'PaymentMethods'
//           }
//         },
//         cache: false
//       }
//     ).done(function (response) {
//       showStep('#stepFive', response.data.html, show)
//       // loadStep(
//       //   response.data.html,
//       //   '#headingFive',
//       //   '#stepFive',
//       //   show
//       // )
//     })
//   }
//
//   function loadConfirmOrder (show) {
//     $.ajax(
//       {
//         method: 'POST',
//         data: {
//           fork: {
//             module: 'Commerce',
//             action: 'ConfirmOrder'
//           }
//         },
//         cache: false
//       }
//     ).done(function (response) {
//       showStep('#stepSix', response.data.html, show)
//       // loadStep(
//       //   response.data.html,
//       //   '#headingSix',
//       //   '#stepSix',
//       //   show
//       // )
//     })
//   }
//
//   // function loadStep (html, headerSelector, contentSelector, show) {
//   //   var stepHeader = $(headerSelector).find('h4'),
//   //     step = $(contentSelector)
//   //
//   //   // Store our text
//   //   if (!stepHeader.data('text')) {
//   //     stepHeader.data('text', stepHeader.html())
//   //   }
//   //
//   //   stepHeader.html(
//   //     $('<a>').attr(
//   //       {
//   //         role: 'button',
//   //         'data-toggle': 'collapse',
//   //         'data-parent': '#checkoutAccordion',
//   //         href: contentSelector,
//   //         'aria-expanded': true,
//   //         'aria-controls': contentSelector
//   //       })
//   //       .html(stepHeader.data('text'))
//   //   )
//   //
//   //   step.find('.panel-body').html(html)
//   //
//   //   if (show) {
//   //     step.collapse('show')
//   //
//   //     $('html,body').animate({
//   //       scrollTop: step.offset().top - 100
//   //     }, 500)
//   //   }
//   // }
//
//   function showStep (id, content, show) {
//     var cardContent = $(id)
//     var card = cardContent.closest('.card')
//     var cardHeader = card.find('.card-header')
//
//     if (!cardHeader.data('text')) {
//       cardHeader.data('text', cardHeader.html())
//     }
//
//     cardHeader.html(
//       $('<a>').attr({
//         'data-toggle': 'collapse',
//         'href': id,
//         'class': 'card-link'
//       })
//         .html(cardHeader.data('text'))
//     )
//
//     cardContent.find('.card-body').html(content)
//
//     if (show) {
//       cardContent.collapse('show')
//
//       $('html,body').animate({
//         scrollTop: cardContent.offset().top - 100
//       }, 500)
//     }
//   }
// })
