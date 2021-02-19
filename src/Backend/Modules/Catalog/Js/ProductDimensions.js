$(function () {
  // Table controls
  var table = $('#dimensionsTable')
  var form = table.closest('form')
  var addColumnButton = table.find('[data-add-column]')
  var addRowButton = table.find('[data-add-row]')

  addColumnButton.click(function () {
    var headColumn = $('<th>').append(createHeaderElement('width'))
    var prototypeName = $(this).data('prototypeName')

    table.find('tbody tr:not(:last-child)').each(function () {
      getCellElements(prototypeName).insertBefore($(this).find('td:last-child'))
    })

    table.find('thead').find('th:last-child').before(headColumn)
    table.find('tbody tr:last-child').append(getDeleteColumnElement())

    table.find('[data-reference]').change()
  })

  addRowButton.click(function () {
    var columns = table.find('thead th')
    var row = $('<tr>').insertBefore(table.find('tbody').find('tr:last-child'))
    var prototypeName = $(this).data('prototypeName')

    $('<th>').append(createHeaderElement('height')).appendTo(row)

    for (var i = 1; i < (columns.length - 1); i++) {
      getCellElements(prototypeName).appendTo(row)
    }

    getDeleteRowElement().appendTo(row)

    table.find('[data-reference]').change()
  })

  $(table).on('click', '[data-remove-row]', function () {
    $(this).closest('tr').remove()
  })

  $(table).on('click', '[data-remove-col]', function () {
    var index = $(this).closest('td').index()
    table.find('tbody tr')
      .find('td:nth(' + (index - 1) + ')').remove()

    table.find('thead tr th:nth(' + (index) + ')').remove()
  })

  $(document).on('change', '[data-reference="height"]', function () {
    var exists = false
    var elementValue = $(this).val()
    var _this = $(this)

    _this.removeClass('error')

    table.find('[data-reference="height"]').not($(this)).each(function () {
      if ($(this).val() === elementValue) {
        exists = true
        _this.addClass('error')
      }
    })

    if (!exists) {
      $(this).closest('tr').find('[data-height]').val($(this).val())
    }
  })

  $(document).on('change', '[data-reference="width"]', function () {
    var index = $(this).closest('th').index()
    var exists = false
    var elementValue = $(this).val()
    var _this = $(this)

    _this.removeClass('error')

    table.find('[data-reference="width"]').not($(this)).each(function () {
      if ($(this).val() === elementValue) {
        exists = true
        _this.addClass('error')
      }
    })

    if (!exists) {
      table.find('tbody tr:not(:last-child)')
        .find('td:nth(' + (index - 1) + ') [data-width]')
        .val($(this).val())
    }
  })

  form.submit(function (e) {
    var valid = true

    table.find('[data-reference]').each(function () {
      $(this).removeClass('error')

      if ($(this).val() === '' || !$(this).val().match(/^([0-9]+)$/)) {
        valid = false

        $(this).addClass('error')
      }
    })

    if (!valid) {
      e.preventDefault()
      alert('Vul alle afmetingen in!')
    }
  })

  function createHeaderElement (reference) {
    return $('<div>').addClass('input-group')
      .append(
        $('<input>').attr({
          'data-reference': reference,
          'type': 'text'
        }).addClass('form-control')
      )
      .append($('<span>').addClass('input-group-addon').html('mm'))
  }

  function getCellElements (name) {
    var count = table.find('[id^="prototype_dimensions"]').length
    var newWidget = table.attr('data-prototype')

    // Check if an element with this ID already exists.
    // If it does, increase the count by one and try again
    var newName = newWidget.match(/id="(.*?)"/)
    var re = new RegExp(name, 'g')
    while ($('#' + newName[1].replace(re, count)).length > 0) {
      count++
    }
    newWidget = newWidget.replace(re, count)
    newWidget = newWidget.replace(/__id__/g, newName[1].replace(re, count))

    return $('<td>').append(newWidget)
  }

  function getDeleteRowElement () {
    return $('<td>').append(
      $('<a>').attr({
        'href': 'javascript:void(0);',
        'class': 'btn btn-danger',
        'data-remove-row': ''
      }).append(
        $('<span>').addClass('fa fa-trash-o')
      )
    )
  }

  function getDeleteColumnElement () {
    return $('<td>').append(
      $('<a>').attr({
        'href': 'javascript:void(0);',
        'class': 'btn btn-danger',
        'data-remove-col': ''
      }).append(
        $('<span>').addClass('fa fa-trash-o')
      )
    )
  }

  // Form controls
  $('[name="product[type]"]').change(function (e) {
    var types = jsBackend.data.get('Catalog.types')
    var dimensionsTab = $('a[href="#tabDimensions"]')
    var value = parseInt($(this).val(), 10)

    if (value === types.dimensions) {
      dimensionsTab.closest('li').removeClass('hidden')
      return
    }

    dimensionsTab.closest('li').addClass('hidden')
  }).change()
})
