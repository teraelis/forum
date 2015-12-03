$(document).ready(function() {

  function toggleFunc(display) {
    updateView.bind(this)(!display);
    return !display;
  }

  function updateView(display) {
    $(this).find('.js-choose-page').css('display', (display ? 'block' : 'none'));
  }

  $('.js-open-page').each(function() {
    var display = false;

    $(this).find('.js-choose-page').submit(function(event) {
      event.preventDefault();
      var url = $(this).data('url');
      if(typeof url !== "undefined")
        window.location.href = url.replace('%%page%%', $(this).find('.js-chosen-page').val());
    });

    var toggle = toggleFunc.bind(this);
    $(this).find('.js-toggle-button').click(function() {
      display = toggle(display);
    });

    updateView.bind(this)(display);
  });
});