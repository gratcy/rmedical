

document.writeln("<div id=dialog_mask style='position:absolute; top:0px; left:0px; width:0px; height:0px; background-color:white; z-index:100; display:none;' class=opacity70></div>");


var dialog_toggled	= null;


function dialog_floatPosition() {
	
	if (dialog_toggled == null)			return;

	var ns = (navigator.appName.indexOf("Netscape") != -1);
	
	var mask			= document.getElementById("dialog_mask");

	mask.style.width	= document.body.clientWidth + "px";
	mask.style.height	= document.body.scrollHeight + "px";

	var windowHeight	= document.documentElement.clientHeight;

	var dialog			= dialog_toggled;

	dialog.style.top	= Math.round(document.body.scrollTop + (windowHeight - parseInt(dialog.style.height) ) /2 + 300) + "px";
	dialog.style.left	= Math.round(document.body.clientWidth / 2 - (parseInt(dialog.style.width)/2)) + "px";



}

/*
dialog_floatPosition();

onscroll=dialog_floatPosition;
onresize=dialog_floatPosition;
*/


function dialog_popup(dialog) {

	var dialog				= document.getElementById(dialog);
	var mask				= document.getElementById("dialog_mask");

	dialog_toggled			= dialog;
	dialog_floatPosition();
	
	dialog.style.display	= '';
	mask.style.display		= '';
	
}

function dialog_close(dialog) {

	var dialog				= dialog_toggled;
	var mask				= document.getElementById("dialog_mask");
	
	dialog.style.display	= 'none';
	mask.style.display		= 'none';
	
	dialog_toggled			= null;
	
	//removeListener(window, 'scroll', dialog_floatPosition);
	//removeListener(window, 'resize', dialog_floatPosition);

}






var dialog_onscroll_previous	= document.onscroll;

function dialog_onscroll(evt) {
	var evt = (evt) ? evt : ((event) ? event : null); 
	if (dialog_onscroll_previous)
		dialog_onscroll_previous(evt);
	dialog_floatPosition();
}

document.onscroll = dialog_onscroll;



var dialog_onresize_previous	= document.onresize;

function dialog_onresize(evt) {
	var evt = (evt) ? evt : ((event) ? event : null); 
	if (dialog_onresize_previous)
		dialog_onresize_previous(evt);
	dialog_floatPosition();
}

document.onresize = dialog_onresize;
