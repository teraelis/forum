$(document).ready(function() {
    var cat = {};

    function toggle($this) {
        var id = $this.attr("href");
        cat[id] = !cat[id];

        var display = "table";
        var displayhr = "none";
        var val = "<img src='"+$this.attr("src-moins")+"' alt='minimiser' title='Minimiser' />";
        if(!cat[id]) {
            display = "none";
            displayhr = "block";
            val = "<img src='"+$this.attr("src-plus")+"' alt='Maximiser' title='Maximiser' />";
        }
        $("#"+id+" .change").css("display", display);
        $this.html(val);
        $($("#"+id).children()[3]).css("display", displayhr);
    }

    $(".expandable > a.expand").click(function(event) {
        event.preventDefault();
        toggle($(this));
    });

    $(".expandable").each(function() {
        cat[$(this).attr("id")] = true;
        if($(this).hasClass('closed')) {
            toggle($(this).children('a.expand').first());
        }
    });
});