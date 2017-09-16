var ShortCut = {};

ShortCut.getEventCode = function(evt) {
	//IE browsers don't pass the event object as an argument, so get them from the window object
	if (!evt)
	        var evt = window.event;
	        
	if (evt.keyCode) //on IE use keycode
	        var code = evt.keyCode;
	else if (evt.which) //on mozilla use wich
	        var code = evt.which;
	        
	if (code >= 65 && code <= 90) //let's just use lower case codes
	        code = code + 32;
	        
	var ctrl = evt.ctrlKey ? 'c' : ''; //is ctrl pressed
	var alt = evt.altKey ? 'a' : ''; //is alt pressed
	var shift = evt.shiftKey ? 's' : '';  //is shift pressed
	
	return ctrl + alt + shift + code;        //put all the pieces together and return the string
}

//a hash to store the actions in code / action pairs
ShortCut.keyShortcuts = {};

ShortCut.registerShortcut = function(code, action) {
	//if the code is not in the correct form, do nothing
	if (!/^c?a?s?\d{1,3}$/.test(code))
	        return;
	
	//store the action in the keyshortcut hash
	ShortCut.keyShortcuts[code] = action;
}

ShortCut.readShortcut = function(evt) {
	//convert the event object in a keyboard shortcut code
	
	var code = ShortCut.getEventCode(evt);
	alert(code);
	//if there is an action associated with that keystroke
	if (typeof(ShortCut.keyShortcuts[code]) == 'function') {
	        //execute it
	        ShortCut.keyShortcuts[code]();
	        //and override the browser default behaviour
	        document.defaultAction = false;
	} else //otherwise just tell the browser to keep on what hes doing
	        document.defaultAction = true;
	        
	return document.defaultAction;
}




var shortcut_onkeypress_previous	= document.onkeypress;

function shortcut_onkeypress(evt) {
	var evt = (evt) ? evt : ((event) ? event : null); 
	if (shortcut_onkeypress_previous)
		shortcut_onkeypress_previous(evt);
	ShortCut.readShortcut(evt);
}

document.onkeypress = shortcut_onkeypress;