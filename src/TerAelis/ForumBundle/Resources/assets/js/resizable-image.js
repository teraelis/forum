$(document).ready(function() {
  var images = [];
  var lightboxOpened = false;

  function pushToImages() {
    var src = $(this).attr('src');
    for(var key in images) {
      if(images.hasOwnProperty(key) && images[key].src == src) {
        return false;
      }
    }
    images.push({
      element: this,
      src: src
    });
    return true;
  }

  $('.js-modal')
    .on('open', function() {
      $(this).data('state', true);
      $(this).show();
      $(this).focus();
    })
    .on('close', function() {
      $(this).data('state', false);
      $(this).hide();
      $(this).blur();
    })
    .each(function() {
      $(this).find('.js-modal-close').click(function() {
        $(this).closest('.js-modal').trigger('close');
      });

      if($(this).data('initial-state') == 1) {
        $(this).trigger('open');
      } else {
        $(this).trigger('close');
      }
    });


  var $lightbox = $('.js-lightbox');
  $lightbox
    .on('open', function(event, top) {
      lightboxOpened = true;
      $(this).find('.js-modal-container').css('top', top+'px');
    })
    .on('close', function() {
      lightboxOpened = false;
    })
    .on('changeContent', function() {
      var ratio;
      var index = $(this).data('index');
      if(typeof index !== "undefined" && index < images.length && index >= 0) {
        var imageInfo = images[index];
        if(images[index].hasOwnProperty('src') && images[index].hasOwnProperty('height') && images[index].hasOwnProperty('width')) {
          var width = imageInfo.width;
          var height = imageInfo.height;
          var pageWidth = $(window).width();
          var pageHeight = $(window).height() - 60;

          if (width <= pageWidth && height <= pageHeight) {
            $(this).find('.js-lightbox-toggle-size').trigger('disable');
          } else {
            $(this).find('.js-lightbox-toggle-size').trigger('enable');
          }

          if (index <= 0) {
            $(this).find('.js-lightbox-prev').trigger('disable');
          } else {
            $(this).find('.js-lightbox-prev').trigger('enable');
          }

          if (index >= images.length - 1) {
            $(this).find('.js-lightbox-next').trigger('disable');
          } else {
            $(this).find('.js-lightbox-next').trigger('enable');
          }

          var image = $('<img src="' + imageInfo.src + '" />');
          // Fit image inside the screen
          var smaller = false;
          if (!$(this).data('fit-in-screen')) {
            if (width > pageWidth) {
              ratio = pageWidth / width;
              width = pageWidth;
              height = height * ratio;
              smaller = true;
            }
            if (height > pageHeight) {
              ratio = pageHeight / height;
              height = pageHeight;
              width = width * ratio;
              smaller = true;
            }
            image.height(height);
            image.width(width);
          } else {
            image.height(imageInfo.height);
            image.width(imageInfo.width);
          }
          if(smaller) {
            $(this).find('.js-lightbox-toggle-size').html('Agrandir');
          } else {
            $(this).find('.js-lightbox-toggle-size').html('Rétrécir');
          }

          var $content = $(this).find('.js-modal-content');
          $content
            .empty()
            .append(image);

          var $container = $(this).find('.js-modal-container');
          $container.css('left', Math.max(0, ((pageWidth - width) / 2)) + 'px');
        }
      }
    })
    .on('toggleSize', function() {
      var value = $(this).data('fit-in-screen');
      if(typeof value === 'undefined' || value === null) {
        value = false;
      }
      $(this).data('fit-in-screen', !value);
      $(this).trigger('changeContent');
    })
    .on('goTo', function(event, index) {
      if(index < images.length && index >= 0) {
        $(this).data('index', index);
        $(this).trigger('changeContent');
      }
    })
    .on('next', function() {
      var index = $(this).data('index');
      $(this).trigger('goTo', index + 1);
    })
    .on('prev', function() {
      var index = $(this).data('index');
      $(this).trigger('goTo', index - 1);
    })
    .on('last', function() {
      $(this).trigger('goTo', images.length - 1);
    })
    .on('first', function() {
      $(this).trigger('goTo', 0);
    })
    .each(function() {
      $(this).find('.js-lightbox-prev').click(function() {
        $(this).closest('.js-lightbox').trigger('prev');
      });

      $(this).find('.js-lightbox-next').click(function() {
        $(this).closest('.js-lightbox').trigger('next');
      });

      $(this).find('.js-lightbox-toggle-size').click(function() {
        $(this).closest('.js-lightbox').trigger('toggleSize');
      });
    });

  $('.js-lightbox-toggle-size')
    .on('disable', function() {
      $(this).addClass('btn-disabled').attr('disabled', 'disabled');
      $(this).parent().hide();
    })
    .on('enable', function() {
      $(this).removeClass('btn-disabled').removeAttr('disabled');
      $(this).parent().show();
    });

  $('.js-lightbox-prev, .js-lightbox-next')
    .on('disable', function() {
      $(this).addClass('btn-disabled').attr('disabled', 'disabled');
    })
    .on('enable', function() {
      $(this).removeClass('btn-disabled').removeAttr('disabled');
    });

  $(window).resize(function() {
    $('.js-lightbox').trigger('changeContent');
  });

  $(document).on('keydown', function(event) {
    var keyId = event.which;
    if(lightboxOpened) {
      var $lightbox = $('.js-lightbox');
      switch (keyId) {
        case 37:
        case 8:
          $lightbox.trigger('prev');
          event.preventDefault();
          break;
        case 39:
        case 32:
        case 13:
          $lightbox.trigger('next');
          event.preventDefault();
          break;
        case 27:
          $lightbox.trigger('close');
          event.preventDefault();
          break;
        case 34:
        case 35:
          $lightbox.trigger('last');
          event.preventDefault();
          break;
        case 33:
        case 36:
          $lightbox.trigger('first');
          event.preventDefault();
          break;
      }
    }
  });

  function openLightBox(index) {
    return function(scrollTop) {
      $('.js-lightbox')
        .trigger('goTo', index)
        .trigger('open', scrollTop);
    }
  }

    function changeImageSize(initialWidth) {
        return function(event, containerWidth) {
            if(typeof containerWidth === "undefined") {
                containerWidth = $(this).closest('.js-content').width();
            }

            if (initialWidth > containerWidth) {
                $(this).width(containerWidth);
            } else {
                $(this).width(initialWidth);
            }
        }
    };
    window.resizableImage = {
        changeImageSize: changeImageSize
    };

  var addImageToLightbox = function addImageToLightbox() {
    var shouldBeInLightbox = $(this).parents('a').length === 0;

    if (shouldBeInLightbox) {
      var index = images.length;
      var alreadyInLightbox = !pushToImages.call(this);
    }

    $(this).load(function () {
      var currentWidth = $(this).width();
      $(this).data('initial-width', currentWidth);
      var currentHeight = $(this).height();
      var containerWidth = $(this).closest('.js-content').width();

      if (currentWidth > containerWidth) {
        $(this).width(containerWidth);
      }

      $(this).on('changeImageSize', changeImageSize(currentWidth).bind(this));

      if (shouldBeInLightbox && !alreadyInLightbox) {
        images[index] = {
          src: $(this).attr('src'),
          width: currentWidth,
          height: currentHeight
        };

        var openLightBoxFunc = openLightBox(index).bind(this);
        $(this).on('click', function (event) {
          openLightBoxFunc($(window).scrollTop());
        });
        $(this).css('cursor', 'pointer');
      }
    }).each(function () {
      if (this.complete) $(this).load();
    });
  };

  $('.js-content-image').each(addImageToLightbox);

  $(window).on('resize', function() {
    $('.js-content-image').trigger('changeImageSize');
  });

  if(typeof window.TA === "undefined") {
    window.TA = {};
  }

  window.TA.addToLightbox = function addToLightbox($container) {
    $container.find('.js-content-image').each(addImageToLightbox)
  }
});