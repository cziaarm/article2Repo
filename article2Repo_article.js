	$(document).ready(function(){
		$("#articleAbstractBody a").each(function(){
			$(this).attr("href").match(/discovery\.ucl\.ac\.uk\/(\d+)(|\/)$/);
			var id = RegExp.$1;
			if(id!=undefined && id !=""){

				$("div#content").append('<div class="separator"></div>'+
				'<div id="articleTitle"><h3>Downloads in last 12 months for "'+title+'" from '+repository+'</h3></div>'+
				'<img src="'+repo_base_url+'/cgi/irstats.cgi?page=get_view_raw&amp;IRS_epchoice=EPrint&amp;eprint='+id+'&amp;IRS_datechoice=period&amp;period=-12m&amp;start_day=1&amp;start_month=1&amp;start_year=2005&amp;end_day=31&amp;end_month=1&amp;end_year=2005&amp;view=MonthlyDownloadsGraph"/>');
			}else{
				if(console != undefined){
					console.log("No ID found from "+$(this).attr("href"));
				}
			}
		});
	});
