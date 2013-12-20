$(document).ready(function() {
    $(".day_selectors").find("td").each( function () {	
	var day = "table." + $(this).attr("id");
	
	$(this).click( function () {
	    
	        $("table.schedule_grid").hide();
	        $(".day_selectors").children().removeClass("highlighted");
		    $(day).show();
                    $(this).addClass("highlighted");
	    });			
    });
    $(".event_type_selectors").children().each( function() {
	var day = "table." + $(".day_selectors").find(".highlighted").first().attr("id")+"."+$(this).attr("id");
	$(this).click(function() {
	    alert (day);
	    $("table.schedule_grid").hide();
	    $(this).siblings().removeClass("highlighted");
	    $(day).show();
	    $(this).addClass("highlighted");
	    });
	});
    $("table.schedule_grid").hide();
    $(".day_selectors").find("td#Fri").addClass("highlighted");
    $(".event_type_selectors").find("td#Events").addClass("highlighted");


});



