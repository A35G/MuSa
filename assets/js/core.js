var path;
var detailsModal,loginModal;

String.prototype.trim = function() {
    return this.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

var nl2br = function(str, replaceMode, isXhtml) {
    var breakTag = (isXhtml) ? '<br />' : '<br>';
    var replaceStr = (replaceMode) ? '$1'+ breakTag : '$1'+ breakTag +'$2';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, replaceStr);
}

$("body").on("submit","form#meza",function(event) {
    var rs = $("input[name='q']").val();
    rs = rs.trim();
    if (typeof rs !== undefined && typeof rs !== null && rs.length > 0) {
        $("input[name='q']").val("");

        $("#homeli").removeAttr("aria-current");
        $("#qry").html(rs);
        $("#ricerca").removeClass("d-none");

        $.ajax({
            type: "POST",
            url: path + "index.php",
            data: {usaf: encodeURIComponent(rs)},
            cache: false
        }).done(function(data, textStatus, jqXHR) {
            var result = jqXHR.responseText;
            if (result.length > 0) {
                $("#boxd").html(result);
            } else {
                
            }
        }).fail(function() {
            console.log("Search error!");
        });
    } else {
        $("#boxd").load(path + " #boxd");
    }

    event.preventDefault();
});

$(document).ready(function() {
    path = window.location.origin;
    if (path.indexOf("localhost") !== -1) {
        path += "/";
    } else {
        path += "/";
    }

    if ($("#dataMedia").length > 0) {
        var rs = $("#dataMedia").html();
        $("#homeli").removeAttr("aria-current");
        $("#ttlv").html(rs);
        $("#viewli").removeClass("d-none");
    }
});
