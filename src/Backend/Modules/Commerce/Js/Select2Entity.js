(function ($) {
  $.fn.select2entity = function (options) {
    this.each(function () {
      // Keep a reference to the element so we can keep the cache local to this instance and so we can
      // fetch config settings since select2 doesn't expose its options to the transport method.
      var $s2 = $(this),
        limit = $s2.data('page-limit') || 0,
        scroll = $s2.data('scroll'),
        prefix = Date.now(),
        cache = []
      // Deep-merge the options
      $s2.select2($.extend(true, {
        // Tags support
        createTag: function (data) {
          if ($s2.data('tags') && data.term.length > 0) {
            var text = data.term + $s2.data('tags-text')
            return { id: $s2.data('new-tag-prefix') + data.term, text: text }
          }
        },
        ajax: {
          transport: function (params, success, failure) {
            // is caching enabled?
            if ($s2.data('ajax--cache')) {
              // try to make the key unique to make it less likely for a page+q to match a real query
              var key = prefix + ' page:' + (params.data.page || 1) + ' ' + params.data.q,
                cacheTimeout = $s2.data('ajax--cacheTimeout')
              // no cache entry for 'term' or the cache has timed out?
              if (typeof cache[key] == 'undefined' || (cacheTimeout && Date.now() >= cache[key].time)) {
                $.ajax(params).fail(failure).done(function (data) {
                  cache[key] = {
                    data: data,
                    time: cacheTimeout ? Date.now() + cacheTimeout : null
                  }
                  success(data)
                })
              } else {
                // return cached data with no ajax request
                success(cache[key].data)
              }
            } else {
              // no caching enabled. just do the ajax request
              $.ajax(params).fail(failure).done(success)
            }
          },
          data: function (params) {
            var data = {}

            data['q'] = params.term

            // set the fork module action
            if ($(this).data('action')) {
              data['fork'] = {}
              data['fork']['action'] = $(this).data('action')
            }

            if (options instanceof Object) {
              if (options.hasOwnProperty('parentElement')) {
                data['parent'] = $(options.parentElement).val()
              }
            }

            // only send the 'page' parameter if scrolling is enabled
            if (scroll) {
              data['page'] = params.page || 1
            }

            return data
          },
          processResults: function (data, params) {
            var results, more = false, response = {}
            params.page = params.page || 1

            if ($.isArray(data.data)) {
              results = data.data
            } else if (typeof data.data == 'object') {
              // assume remote result was proper object
              results = data.data.query_data
              more = data.data.more
            } else {
              // failsafe
              results = []
            }

            if (scroll) {
              response.pagination = { more: more }
            }
            response.results = results

            return response
          }
        }
      }, options || {}))
    })
    return this
  }
})(jQuery);

(function ($) {
  $(function () {
    $('.select2entity[data-autostart="true"]:not([data-action="AutoCompleteSpecificationValue"])').each(function () {
      $(this).select2entity($(this).data())
    })

    $('[data-collection="product_specification_values"]').on('collection-field-added', function () {
      var selectElement = $('[data-action="AutoCompleteSpecificationValue"]:last'),
        options = selectElement.data()

      options['parentElement'] = selectElement.closest('.list-group-item').find('[name*="product[specification_values]"]')

      selectElement.select2entity(options)
    })

    $('[data-collection="product_option_value_dependencies"]').on('collection-field-added', function () {
      var selectElement = $('[data-action="AutoCompleteProductOptionValue"]:last'),
        options = selectElement.data()

      options['parentElement'] = selectElement.closest('.list-group-item').find('[name*="product_option_value[dependencies]"]')

      selectElement.select2entity(options)
    })

    var addUpSellProductButton = $('[data-collection="product_up_sell_products"]'),
      upSellListGroup = addUpSellProductButton.closest('.panel').find('.list-group')
    addUpSellProductButton.on('collection-field-added', function () {
      var selectElement = $('[data-action="AutoCompleteProducts"]:last'),
        row = selectElement.closest('.list-group-item'),
        options = selectElement.data()

      options['parentElement'] = row.find('[name*="[upSellProduct]"]')

      row.find('[name*="[sequence]"]').val(row.index() + 1)

      selectElement.select2entity(options)
    })

    upSellListGroup.sortable(
      {
        items: '.list-group-item',
        // handle: 'td.dragAndDropHandle',
        placeholder: 'dragAndDropPlaceholder',
        forcePlaceholderSize: true,
        stop: function (e, ui) {
          upSellListGroup.find('.list-group-item').each(function () {
            $(this).find('[name*="[sequence]"]').val($(this).index() + 1)
          })
        }
      }
    )

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      activateAutoCompleteSpecificationValue()
    })

    if ($('a[href=#tabSpecifications]').closest('li').hasClass('active')) {
      activateAutoCompleteSpecificationValue()
    }

    if ($('a[href=#tabDependencies]').closest('li').hasClass('active')) {
      activateAutoCompleteProductOptionValue()
    }

    function activateAutoCompleteSpecificationValue () {
      $('[data-action="AutoCompleteSpecificationValue"]').each(function () {
        var options = $(this).data()
        options['parentElement'] = $(this).closest('.list-group-item').find('[name*="product[specification_values]"]')

        $(this).select2entity(options)
      })
    }

    function activateAutoCompleteProductOptionValue () {
      $('[data-action="AutoCompleteProductOptionValue"]').each(function () {
        var options = $(this).data()
        options['parentElement'] = $(this).closest('.list-group-item').find('[name*="product_option_value[dependencies]"]')

        $(this).select2entity(options)
      })
    }

    $('.select2simple').select2();
  })
})(jQuery)
