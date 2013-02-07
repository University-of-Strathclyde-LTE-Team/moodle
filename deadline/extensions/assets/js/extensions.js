/* Functions for extensions where required. */
function checkUncheckAll(theElement) {

var theForm = theElement.form, z = 0;

	for(z=0; z<theForm.length;z++){
		if(theForm[z].disabled == false){
			if(theForm[z].type == 'checkbox' && theForm[z].checked == false){
				theForm[z].checked = true;
            } else{
                theForm[z].checked = false;
            }
        }
    }
}

function popup() {
	var txt = 'Are you sure you would like to approve these extensions?';
	return confirm(txt);
}