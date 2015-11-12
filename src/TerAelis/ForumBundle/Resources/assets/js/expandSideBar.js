$(document).ready(function() {
    var sideBar = JSON.parse(localStorage.getItem('expandSidebar'));
    if(sideBar === null || typeof sideBar !== "object") {
        sideBar = {};
    }

    var expandables = $(".sub a.expand");
    expandables.each(function() {
        var classe = $(this).attr("href");
        if(sideBar.hasOwnProperty(classe) && !sideBar[classe]) {
            updateExpandable.call(this, classe);
        }
    });


  function updateExpandable(classe) {
      var display = "block";
      var val = "<img src='" + $(this).attr("src-moins") + "' alt='minimiser' title='Minimiser' />";
      if (!sideBar[classe]) {
          display = "none";
          val = "<img src='" + $(this).attr("src-plus") + "' alt='Maximiser' title='Maximiser' />";
      }
      $(".js-" + classe).css("display", display);
      $(this).html(val);
  }

    expandables.click(function(event) {
        event.preventDefault();
        var classe = $(this).attr("href");
        if(sideBar.hasOwnProperty(classe)) {
          sideBar[classe] = !sideBar[classe];
        } else {
          sideBar[classe] = false;
        }
        updateExpandable.call(this, classe);
        localStorage.setItem('expandSidebar', JSON.stringify(sideBar));
    });
});