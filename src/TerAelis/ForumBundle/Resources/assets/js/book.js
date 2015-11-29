$(document).ready(function() {
  var scrollTop = 0;

  var currentState = {
    modalFontColor: '#333333',
    modalBackgroundColor: '#eeeeee',
    modalFontFamily: 'Georgia, serif',
    modalLineHeight: 1.5,
    modalFontSize: 18,
    modalWidth: 550
  };
  var modalFontColor, modalBackgroundColor, modalFontFamily, modalFontSize, modalWidth;

  function initLocalStorage() {
    var savedData = window.localStorage.getItem('ta.book');
    if (savedData !== null) {
      try {
        savedData = JSON.parse(savedData);
      }
      catch (e) {
        savedData = {};
      }
    } else {
      savedData = {};
    }

    for (var key in currentState) {
      if (currentState.hasOwnProperty(key) && savedData.hasOwnProperty(key)) {
        currentState[key] = savedData[key];
      }
    }
  }

  function updateLocalstorage() {
    window.localStorage.setItem('ta.book', JSON.stringify(currentState));
  }

  var $jsBookModal = $('.js-book-modal');
  $jsBookModal
    .on('open', function(event, contentElement) {
      initLocalStorage();

      var modalContent = $(this).find('.js-book-modal-content');
      modalContent
        .empty()
        .append(
          $(contentElement).clone()
        );

      $(this).show();
      scrollTop = $('body').scrollTop();
      $('body').scrollTop(0);
      $(this).trigger('updateView');
      $(this).trigger('updateFields');
    })
    .on('close', function() {
      $(this).hide();
      $('body').scrollTop(scrollTop);
    })
    .on('updateView', function() {
      $(this).find('.js-book-modal-content')
        .css('font-family', currentState.modalFontFamily)
        .css('line-height', currentState.modalLineHeight+'em')
        .css('font-size', currentState.modalFontSize+'px')
        .css('width', currentState.modalWidth+'px');

      $(this).find('.modal-overlay')
        .css('opacity', 1)
        .css('background-color', currentState.modalBackgroundColor);

      $(this).css('color', currentState.modalFontColor);

      var width = $(this).find('.js-modal-container').width();
      var windowWidth = $('body').width();
      $(this).find('.js-modal-container').css('left', ((windowWidth - width) / 2) + 'px');

      updateLocalstorage();
    })
    .on('changeConfig', function(event, name, value) {
      if(['modalFontSize', 'modalWidth'].indexOf(name) > -1) {
        value = parseInt(value);
      } else if(['modalFontColor', 'modalBackgroundColor'].indexOf(name) > -1) {
        if(!(/^#[0-9A-F]{6}$/i).test(value)) {
          if(name === 'modalFontColor') {
            value = '#333333';
          } else {
            value = '#eeeeee';
          }
        }
      }
      currentState[name] = value;
    })
    .on('updateFields', function(event) {
      $('.js-book-config-input').each(function() {
        $(this).val(currentState[$(this).data('bind')])
      })
    });

  /* Enable buttons */
  function updateConfig(forceUpdateFields) {
    return function() {
      var name = $(this).data('bind');
      $jsBookModal.trigger('changeConfig', [name, $(this).val()]);
      $jsBookModal.trigger('updateView');
      if(forceUpdateFields) {
        $jsBookModal.trigger('updateFields');
      }
    }
  }

  $('.js-book-config-input')
    .on('change', updateConfig(true));

  $('.js-book-config-toggle').click(function() {
    $('.js-book-config').toggle();
  });

  function open() {
    $('.js-book-modal').trigger('open', [this]);
  }

  $(window).resize(function() {
    $('.js-book-modal').trigger('updateView');
  });

  $('.js-book').each(function() {
    var openFunc = open.bind($(this).find('.js-book-content')[0]);

    var $jsBook = $(this);
    $jsBook.on('open', openFunc);

    $jsBook.find('.js-book-button-open').on('click', function() {
      openFunc();
    });
  });
});