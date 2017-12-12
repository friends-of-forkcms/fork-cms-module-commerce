$(function(){
    $('#checkout-account').click(function(e){
        e.preventDefault();

        var checkoutType = $('[name="checkout_type"]:checked').val();

        // Only run when checkout type is set
        if (!checkoutType) {
            return;
        }

        $.ajax(
            {
                method : "POST",
                data : {
                    fork : {
                        module : 'Catalog',
                        action : 'CheckoutAccount'
                    },
                    type : checkoutType
                }
            }
        ).done(function(response){
            var stepTwoHeader = $('#headingTwo').find('h4'),
                stepTwo = $('#stepTwo');

            // Store our text
            if (!stepTwoHeader.data('text')) {
                stepTwoHeader.data('text', stepTwoHeader.html());
            }

            stepTwoHeader.html(
                $('<a>').attr(
                        {
                            role : 'button',
                            'data-toggle' : 'collapse',
                            'data-parent' : '#checkoutAccordion',
                            href : '#stepTwo',
                            'aria-expanded' : true,
                            'aria-controls' : 'stepTwo'
                        }
                    )
                    .html(stepTwoHeader.data('text'))
            );

            stepTwo.collapse('show');

            $('html,body').animate({
                scrollTop : stepTwoHeader.offset().top - 100
            }, 500);

            stepTwo.find('.panel-body').html(response.data.html);
        });
    });

    $(document).on('submit', 'form[name=account_guest]', function(e){
        e.preventDefault();

        // Build request data
        var requestData = $(this).serializeArray();
        requestData.push({
            name : 'fork[module]',
            value : 'Catalog'
        });
        requestData.push({
            name : 'fork[action]',
            value : 'CheckoutAccount'
        });
        requestData.push({
            name : 'type',
            value : 'guest'
        });

        $.ajax(
            {
                method : "POST",
                data : requestData
            }
        ).done(function(response){
            // If our form has an error
            var stepTwoHeader = $('#headingTwo').find('h4'),
                stepTwo = $('#stepTwo');

            stepTwo.find('.panel-body').html(response.data.html);

            if (response.data.hasErrors) {
                $('html,body').animate({
                    scrollTop: stepTwoHeader.offset().top - 100
                }, 500);
            } else {
                switch (response.data.nextStep) {
                    case 'shipmentAddress':
                        loadGuestShipmentAddress(true);
                        break;
                    case 'shipmentMethods':
                        loadGuestShipmentAddress(false);
                        loadShipmentMethods(true);
                        break;
                }
            }
        });
    });

    $(document).on('submit', 'form[name=account_shipment_address]', function(e) {
        e.preventDefault();

        // Build request data
        var requestData = $(this).serializeArray();
        requestData.push({
            name: 'fork[module]',
            value: 'Catalog'
        });
        requestData.push({
            name: 'fork[action]',
            value: 'GuestShipmentAddress'
        });

        $.ajax(
            {
                method: "POST",
                data: requestData
            }
        ).done(function (response) {
            // If our form has an error
            var stepThreeHeader = $('#headingThree').find('h4'),
                stepThree = $('#stepThree');

            stepThree.find('.panel-body').html(response.data.html);

            if (response.data.hasErrors) {
                $('html,body').animate({
                    scrollTop: stepThreeHeader.offset().top - 100
                }, 500);
            } else {
                loadShipmentMethods(true)
            }
        });
    });

    $(document).on('submit', 'form[name=checkout_shipment_method]', function (e) {
        e.preventDefault();

        // Build request data
        var requestData = $(this).serializeArray();
        requestData.push({
            name: 'fork[module]',
            value: 'Catalog'
        });
        requestData.push({
            name: 'fork[action]',
            value: 'ShipmentMethods'
        });

        $.ajax(
            {
                method: "POST",
                data: requestData
            }
        ).done(function (response) {
            // If our form has an error
            var stepFourHeader = $('#headingFour').find('h4'),
                stepFour = $('#stepFour');

            stepFour.find('.panel-body').html(response.data.html);

            if (response.data.hasErrors) {
                $('html,body').animate({
                    scrollTop: stepFourHeader.offset().top - 100
                }, 500);
            } else {
                loadPaymentMethods(true)
            }
        });
    });

    $(document).on('submit', 'form[name=checkout_payment_method]', function (e) {
        e.preventDefault();

        // Build request data
        var requestData = $(this).serializeArray();
        requestData.push({
            name: 'fork[module]',
            value: 'Catalog'
        });
        requestData.push({
            name: 'fork[action]',
            value: 'PaymentMethods'
        });

        $.ajax(
            {
                method: "POST",
                data: requestData
            }
        ).done(function (response) {
            // If our form has an error
            var stepHeader = $('#headingFive').find('h4'),
                step = $('#stepFive');

            step.find('.panel-body').html(response.data.html);

            if (response.data.hasErrors) {
                $('html,body').animate({
                    scrollTop: stepHeader.offset().top - 100
                }, 500);
            } else {
                loadConfirmOrder(true)
            }
        });
    });

    $(document).on('submit', 'form[name=checkout_confirm_order]', function (e) {
        e.preventDefault();

        // Build request data
        var requestData = $(this).serializeArray();
        requestData.push({
            name: 'fork[module]',
            value: 'Catalog'
        });
        requestData.push({
            name: 'fork[action]',
            value: 'ConfirmOrder'
        });

        $.ajax(
            {
                method: "POST",
                data: requestData
            }
        ).done(function (response) {
            // If our form has an error
            var stepHeader = $('#headingSix').find('h4'),
                step = $('#stepSix');

            step.find('.panel-body').html(response.data.html);

            if (response.data.hasErrors) {
                $('html,body').animate({
                    scrollTop: stepHeader.offset().top - 100
                }, 500);
            } else {
                window.location = response.data.nextStep;
            }
        });
    });

    $(document).on('change', 'form[name=checkout_payment_method] input[name*=payment_method][type=radio]', function(e){
        e.preventDefault();

        // Build request data
        var requestData = $(this).serializeArray();
        requestData.push({
            name: 'fork[module]',
            value: 'Catalog'
        });
        requestData.push({
            name: 'fork[action]',
            value: 'PaymentMethods'
        });

        $.ajax(
            {
                method: "POST",
                data: requestData
            }
        ).done(function (response) {
            // If our form has an error
            var stepHeader = $('#headingFive').find('h4'),
                step = $('#stepFive');

            step.find('.panel-body').html(response.data.html);
        });
    });

    function loadGuestShipmentAddress(show) {
        $.ajax(
            {
                method : "POST",
                data : {
                    fork : {
                        module : 'Catalog',
                        action : 'GuestShipmentAddress'
                    }
                }
            }
        ).done(function(response){
            loadStep(
                response.data.html,
                '#headingThree',
                '#stepThree',
                show
            );
        });
    }

    function loadShipmentMethods(show) {
        $.ajax(
            {
                method : "POST",
                data : {
                    fork : {
                        module : 'Catalog',
                        action : 'ShipmentMethods'
                    }
                }
            }
        ).done(function(response){
            loadStep(
                response.data.html,
                '#headingFour',
                '#stepFour',
                show
            );
        });
    }

    function loadPaymentMethods(show) {
        $.ajax(
            {
                method : "POST",
                data : {
                    fork : {
                        module : 'Catalog',
                        action : 'PaymentMethods'
                    }
                }
            }
        ).done(function(response){
            loadStep(
                response.data.html,
                '#headingFive',
                '#stepFive',
                show
            );
        });
    }

    function loadConfirmOrder(show) {
        $.ajax(
            {
                method : "POST",
                data : {
                    fork : {
                        module : 'Catalog',
                        action : 'ConfirmOrder'
                    }
                }
            }
        ).done(function(response){
            loadStep(
                response.data.html,
                '#headingSix',
                '#stepSix',
                show
            );
        });
    }

    function loadStep(html, headerSelector, contentSelector, show) {
        var stepHeader = $(headerSelector).find('h4'),
            step = $(contentSelector);

        // Store our text
        if (!stepHeader.data('text')) {
            stepHeader.data('text', stepHeader.html());
        }

        stepHeader.html(
            $('<a>').attr(
                {
                    role : 'button',
                    'data-toggle' : 'collapse',
                    'data-parent' : '#checkoutAccordion',
                    href : contentSelector,
                    'aria-expanded' : true,
                    'aria-controls' : contentSelector
                })
                .html(stepHeader.data('text'))
        );

        step.find('.panel-body').html(html);

        if (show) {
            step.collapse('show');

            $('html,body').animate({
                scrollTop: step.offset().top - 100
            }, 500);
        }
    }
});
