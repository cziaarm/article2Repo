

/**
 *  Selectively enable EJME form fields
 */
function disable_all(){

	document.getElementById("ejme_access0").disabled = true;;
	document.getElementById("ejme_access1").disabled = true;
	document.getElementById("ejme_date_available").disabled = true;
	document.getElementById("ejme_agree").disabled = true;
	document.getElementById("ejme_upload_btn").disabled = true;


}
function evalFieldSettings () {
	var ejme_dans = document.getElementById("ejme_dans");

	if (ejme_dans.checked) {
		var ejme_status = document.getElementById("ejme_status");
		var ejme_access = [];
		ejme_access[0] = document.getElementById("ejme_access0");
		ejme_access[1] = document.getElementById("ejme_access1");
		var ejme_date_available = document.getElementById("ejme_date_available");
		var ejme_agree = document.getElementById("ejme_agree");
		var ejme_fileid = document.getElementById("ejme_fileid");

		ejme_access[0].disabled = false;
		ejme_access[1].disabled = false;
		// ejme_access[2].disabled = false;
		ejme_date_available.disabled = false;
		ejme_agree.disabled = false;
		console.log(ejme_fileid.value+" != 0 && "+ejme_agree.checked);
		if (ejme_fileid.value != 0 && ejme_agree.checked) ejme_upload_btn.disabled = false; 
    	}
  	return false;
}

/**
 *  Set ejme_status to "upload request from user"
 *  when the Deposit button is clicked
 */
function setEjmeStatus (msgText) {
	var ejme_status = document.getElementById("ejme_status");
	if (ejme_status.value != 0 || !confirm(msgText)) return false;
	ejme_status.value = 1;  //EJME_SUPPFILE_UPLOAD_REQ
	return true;
}

function scrape_id(abody_elems){
	for(i in abody_elems) { 
		if(abody_elems[i].tagName === "A"){ 
			console.log("ERE");		
		}
	}
}

disable_all();
