$(document).ready(function() {
  var opened = false;

  var modalColor, modalFontSize, modalWidth;

  function getLocalstorage() {
    var savedData = window.localStorage.getItem('ta.book');
    if(savedData !== null) {
      try {
        savedData = JSON.parse(savedData);
        modalColor = savedData.modalColor;
        modalFontSize = savedData.modalFontSize;
        modalWidth = savedData.modalWidth;
      } catch(e) {
        modalColor = 'light';
        modalFontSize = 13;
        modalWidth = 550;
      }
    }
  }
  getLocalstorage();

  function updateLocalstorage() {
    window.localStorage.setItem('ta.book', JSON.stringify({
      modalColor: modalColor,
      modalFontSize: modalFontSize,
      modalWidth: modalWidth
    }));
  }

  var $jsBookModal = $('.js-book-modal');
  $jsBookModal
    .on('open', function(event, contentElement, styles) {
      $(this).data('styles', styles);

      var savedIndex = styles.indexOf(modalFontSize);
      if(savedIndex === -1) {
        savedIndex = 0;
      }
      $(this).data('current-style', savedIndex);

      var modalContent = $(this).find('.js-book-modal-content');
      modalContent
        .empty()
        .append(
          $(contentElement).clone()
        );

      $(this).show();
      $('body').scrollTop(0);
      $(this).trigger('updateView');
    })
    .on('close', function() {
      $(this).hide();
    })
    .on('updateView', function() {
      $(this).find('.js-book-modal-content')
        .css('font-size', modalFontSize+'px')
        .css('width', modalWidth+'px');

      var styles = $(this).data('styles');
      if(styles.length > 1) {
        styles.forEach((function (value) {
          $(this).removeClass('book-modal-' + value);
        }).bind(this));

        var currentStyle = styles[$(this).data('current-style')];
        $(this).addClass('book-modal-' + currentStyle);
      } else if(styles.length == 1) {
        $(this).addClass('book-modal-'+styles[0]);
        $(this).find('.js-book-toggle-style').hide();
      } else {
        $(this).addClass('book-modal-light');
        $(this).find('.js-book-toggle-style').hide();
      }

      var width = $(this).find('.js-modal-container').width();
      var windowWidth = $('body').width();
      $(this).find('.js-modal-container').css('left', ((windowWidth - width) / 2) + 'px');

      updateLocalstorage();
    })
    .on('toggle-style', function() {
      var styles = $(this).data('styles');
      var newStyleIndex = ($(this).data('currentStyle') + 1) % styles.length;

      $(this).data('current-style', newStyleIndex);
      modalColor = styles[newStyleIndex];

      $(this).trigger('updateView');
    })
    .on('smaller-font', function() {
      modalFontSize--;
      $(this).trigger('updateView');
    })
    .on('bigger-font', function() {
      modalFontSize++;
      $(this).trigger('updateView');
    })
    .on('smaller-width', function() {
      modalWidth -= 10;
      $(this).trigger('updateView');
    })
    .on('bigger-width', function() {
      modalWidth += 10;
      $(this).trigger('updateView');
    });

  /* Enable buttons */
  [
    'close',
    'toggle-style',
    'smaller-font',
    'bigger-font',
    'smaller-width',
    'bigger-width'
  ].forEach(function(value) {
      $jsBookModal.find('.js-book-'+value).on('click', function() {
        $jsBookModal.trigger(value);
      });
    });

  function open(styles) {
    $('.js-book-modal').trigger('open', [
      this,
      styles
    ]);
  }

  $(window).resize(function() {
    $('.js-book-modal').trigger('updateView');
  });

  $('.js-book').each(function() {
    var openFunc = open.bind($(this).find('.js-book-content')[0]);

    var $jsBook = $(this);
    $jsBook.on('open', openFunc);

    $jsBook.find('.js-book-button-open').on('click', function() {
      var styles = $jsBook.data('styles');
      styles = styles.split(',');
      openFunc(styles);
    });
  });
});