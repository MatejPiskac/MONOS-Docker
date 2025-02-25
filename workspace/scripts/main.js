function hideLoad() {
    $("#loading").animate({ top: '-50%', opacity: 0 }, 1000, function() {
        $(this).fadeOut(1000);
    });
}

$("#net_chart").ready(() => {
    setTimeout(hideLoad, 1000);
});


function onLoad() {
    if (window.loaded) {
        loaded();
    }

    if ($(".input-fly input").val()) {
        $(this).addClass("default");
    }

    $(".input-fly input").focusout(() => {
        console.log("focusout");
        console.log($(this).val());
        console.log($(this).val().length);
        if ($(this).val().length > 0) {
            $(this).siblings("label").addClass("stay");
        } else {
            $(this).siblings("label").removeClass("stay");
        }
        
    });


    $("input").focusout(() => {
        console.log("focusout");
        console.log($(this).val());
        console.log($(this).val().length);
        if ($(this).val().length > 0) {
            $(this).siblings("label").addClass("stay");
        } else {
            $(this).siblings("label").removeClass("stay");
        }
        
    });

    $(".sidebar-content > div").click(function() {
        if ($(this).children(".title.up").length > 0) {
            $(this).children(".roll").slideToggle(200, () => {
                $(this).children(".title").removeClass("up");
            });
        } else {
            $(this).children(".roll").slideToggle(200);
            $(this).children(".title").addClass("up");
            $(this).children(".roll").css("display", "flex");
        }
    });
}


$(document).ready(onLoad);