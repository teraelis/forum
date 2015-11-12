$(document).ready(function() {
    var sideBar = {};

    sideBar["salons"] = true;
    sideBar["contacts"] = true;

    $(".sub a.expand").click(function(event) {
        event.preventDefault();
        var classe = $(this).attr("href");
        if(sideBar.hasOwnProperty(classe)) {
          sideBar[classe] = !sideBar[classe];
        } else {
          sideBar[classe] = false;
        }

        var display = "block";
        var val = "<img src='"+$(this).attr("src-moins")+"' alt='minimiser' title='Minimiser' />";
        if(!sideBar[classe]) {
            display = "none";
            val = "<img src='"+$(this).attr("src-plus")+"' alt='Maximiser' title='Maximiser' />";
        }
        $(".js-"+classe).css("display", display);
        $(this).html(val);
    });
});