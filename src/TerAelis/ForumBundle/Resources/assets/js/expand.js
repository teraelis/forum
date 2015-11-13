$(document).ready(function() {

    var cat = localStorage.getItem('expandCat');
    if(cat === null || typeof cat !== "string") {
        cat = {};
    } else {
        try {
            cat = JSON.parse(cat);
        } catch(e) {
            cat = {};
        }
    }

    var expandableLinks = $(".expandable > a.expand");
    expandableLinks.each(function(event) {
        var id = $(this).attr("href");
        if(cat.hasOwnProperty(id) && !cat[id]) {
            updateExpandable($(this), id);
        }
    });

    function updateExpandable($this, id) {
        var display = "table";
        var displayhr = "none";
        var val = "<img src='" + $this.attr("src-moins") + "' alt='minimiser' title='Minimiser' />";
        if (!cat[id]) {
            display = "none";
            displayhr = "block";
            val = "<img src='" + $this.attr("src-plus") + "' alt='Maximiser' title='Maximiser' />";
        }
        $("#" + id + " .change").css("display", display);
        $this.html(val);
        $($("#" + id).children()[3]).css("display", displayhr);
    }

    function toggle($this) {
        var id = $this.attr("href");
        cat[id] = !cat[id];

        updateExpandable($this, id);
    }

    expandableLinks.click(function(event) {
        event.preventDefault();
        toggle($(this));
        localStorage.setItem('expandCat', JSON.stringify(cat));
    });
});