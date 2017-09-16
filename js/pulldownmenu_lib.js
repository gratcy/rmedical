// JavaScript Document
function Output(ObjectID, HTML_Code) {
	if (document.getElementById && ((obj=document.getElementById(ObjectID))!=null))
		obj.innerHTML = unescape(HTML_Code);
}

String.prototype.trim=function() {
	return this.replace(/(^\s*)|(\s*$)/g, "");
}

function InputFilter(Event, Filter) {
	if (window.event) {
		var Key = Event.keyCode;
		if (Key==13) return true;
		else {
			Filter = new RegExp('[' + Filter + ']');
			return Filter.test(String.fromCharCode(Key));
		}
	} else {
		var Key = Event.which;
		if (Key==13 || Key==0 || Key==8) return true;
		else {
			Filter = new RegExp('[' + Filter + ']');
			return Filter.test(String.fromCharCode(Key));
		}
	}
}

function InputUpperCase(Event) {
	if (window.event) {
		if (Event.keyCode>=97 && Event.keyCode<=122) Event.keyCode = Event.keyCode-32;
	} else {
		if (Event.which>=97 && Event.which<=122) {
			var newEvent = document.createEvent("KeyEvents");
			newEvent.initKeyEvent("keypress", true, true, document.defaultView, Event.ctrlKey, Event.altKey, Event.shiftKey, Event.metaKey, 0, Event.which-32);
			Event.preventDefault();
			Event.target.dispatchEvent(newEvent);	
		}
	}
}

function InputLowerCase(Event) {
	if (window.event) {
		if (Event.keyCode>=65 && Event.keyCode<=90) Event.keyCode = Event.keyCode+32;
	} else {
		if (Event.which>=665 && Event.which<=90) {
			var newEvent = document.createEvent("KeyEvents");
			newEvent.initKeyEvent("keypress", true, true, document.defaultView, Event.ctrlKey, Event.altKey, Event.shiftKey, Event.metaKey, 0, Event.which+32);
			Event.preventDefault();
			Event.target.dispatchEvent(newEvent);
		}
	}
}

function GetX(myObject) {
	if (document.layers) {
		if (myObject.x) X = myObject.x;
	} else {
		X = 0;
		while(myObject) {
			X += parseInt(myObject.offsetLeft);
			myObject = myObject.offsetParent;
		}
	}
	return X;
}

function GetY(myObject) {
	if (document.layers) {
		if (myObject.y) Y = myObject.y;
	} else {
		Y = 0;
		while(myObject) {
			Y += parseInt(myObject.offsetTop);
			myObject = myObject.offsetParent;
		}
	}
	return Y;
}

function GetMouseX(Event) {
	if (document.all) return event.clientX + document.body.scrollLeft + (navigator.appName.indexOf('Internet Explorer')==-1 ? 0 : -2);
	else return Event.pageX;
}

function GetMouseY(Event) {
	if (document.all) return event.clientY + document.body.scrollTop + (navigator.appName.indexOf('Internet Explorer')==-1 ? 0 : -2);
	else return Event.pageY;
}