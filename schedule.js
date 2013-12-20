$(document).ready(function() {
    var selected_day = "Fri";
    var selected_type = "Events";
    $(".day_selectors").find("td").each( function () {	
	$(this).click( function () {
	selected_day =  $(this).attr("id");
	    var selected_table="table." + selected_day + "." + selected_type;
	        $("table.schedule_grid").hide();
	        $(".day_selectors").children().removeClass("highlighted");
		    $(selected_table).show();
                    $(this).addClass("highlighted");
	    });			
    });
    $(".event_type_selectors").children().each( function() {
	$(this).click(function() {
	    selected_type = $(this).attr("id");
   	    var selected_table="table." + selected_day + "." + selected_type;
	    $("table.schedule_grid").hide();
	    $(this).siblings().removeClass("highlighted");
	    $(selected_table).show();
	    $(this).addClass("highlighted");
	    });
	});
    $("table.schedule_grid").hide();
//    $("table.schedule_grid.Fri.Events").show();
    $("td#Fri").click();    //addClass("highlighted");
//    $("td#Events").addClass("highlighted");

});



