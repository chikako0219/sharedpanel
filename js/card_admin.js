$(".app-style0").resizable({
    resize : function(event, ui) {
    },
    handles: "all"
});

jsPlumb.ready(function() {
    jsPlumb.draggable($(".card"), {
        stop: function(event) {
            if ($(event.target).find("select").length == 0) {  saveState(event.target);  }
        }
    });
});

var allPositions = function() {
    var blocks = [];
    $("#diagramContainer .card").each(function (idx, elem) {
        var $elem = $(elem);
        blocks.push({
            cardid: $elem.attr("id"),
            positionx: parseInt($elem.css("left"), 10),
            positiony: parseInt($elem.css("top"), 10)
        });
    });
    var serializedData = JSON.stringify(blocks);
    return serializedData;
};

var saveState = function() {
    $.post('cardxy/',{data:allPositions()} );
};