var selectItem		= false;

// JavaScript Document
function PulldownMenu() {
	if (!document.getElementById('PulldownMenu')) {
		document.writeln('<span id="PulldownMenu" style="display:none"></span>');
	}
	this.item	= new Array;
	this.value	= new Array;
	this.main;
	var originalValue	= false;
	var displaying		= false;
	var displayMenuItem;
	var onMenuSelect;

	selectItem		= false;

	this.addEventListener = function(Event, Listener) {
		switch (Event) {
			case 'onMenuSelect':
				onMenuSelect = Listener;
				break;
		}
	}

	this.changeSelectItem = function(Event, myObject) {
		var Key = window.event ? Event.keyCode : Event.which;
		if (Key == 13) {
			var found_item = false;
			for (i=0; i<this.item.length; i++) {
				if (myObject.value == this.item[i]) {
					document.getElementById(myObject.id + '_value').value = this.value[i];
					found_item		= true;
					break;
				}
			}
			if (found_item) {
				UnloadMenu();
				if (document.getElementById(myObject.id + '_value').onchange)
					document.getElementById(myObject.id + '_value').onchange();
			}
			return;
		}
		if (document.getElementById('PulldownMenu').style.display == 'none') {
			this.showMenu(myObject, "");
		}
		var Key = window.event ? Event.keyCode : Event.which;
		if (selectItem !== false) {
			document.getElementById('i' + selectItem).style.backgroundColor='';
			document.getElementById('i' + selectItem).style.color='';
		}
		switch (Key) {
			case 38:
				if (selectItem === false) {
					selectItem = displayMenuItem.length-1;
					if (originalValue === false) originalValue = myObject.value;
					myObject.value = displayMenuItem[selectItem].constructor == Array ? displayMenuItem[selectItem][this.main] : displayMenuItem[selectItem];
				} else if (selectItem > 0) {
					selectItem--;
					if (originalValue === false) originalValue = myObject.value;
					myObject.value = displayMenuItem[selectItem].constructor == Array ? displayMenuItem[selectItem][this.main] : displayMenuItem[selectItem];
				} else {
					selectItem = false;
					myObject.value = originalValue;
				}
				break;
			case 40:
				if (selectItem === false) {
					selectItem = 0;
					if (originalValue === false) originalValue = myObject.value;
					myObject.value = displayMenuItem[selectItem].constructor == Array ? displayMenuItem[selectItem][this.main] : displayMenuItem[selectItem];
				} else if (selectItem < displayMenuItem.length-1) {
					selectItem++;
					if (originalValue === false) originalValue = myObject.value;
					myObject.value = displayMenuItem[selectItem].constructor == Array ? displayMenuItem[selectItem][this.main] : displayMenuItem[selectItem];
				} else {
					selectItem = false;
					myObject.value = originalValue;
				}
				break;
			case 9:
				UnloadMenu();
				break;
		}
		if (selectItem !== false) {
			var menuObject = document.getElementById('PulldownMenu');
			var Y = selectItem * 18;
			if (menuObject.scrollTop > Y) {
				menuObject.scrollTop = Y;
			} else if (menuObject.scrollTop + menuObject.offsetHeight - 5 < Y) {
				menuObject.scrollTop = Y + 18 - menuObject.offsetHeight + 2;
			}
			document.getElementById('i' + selectItem).style.backgroundColor='#3399FF';
			document.getElementById('i' + selectItem).style.color='#FFFFFF';
		}
	}

	this.renewMenu = function(Event, myObject, Search) {
		var Key = window.event ? Event.keyCode : Event.which;
		if (Key == 8 || Key == 32 || Key == 46 || (Key >= 65 && Key <= 111) || (Key >= 186 && Key <= 222)) {
			originalValue = false;
			this.showMenu(myObject, Search);
		}

		if (Key == 8 || Key == 32 || Key == 46 || Key == 127 || Key == 13) {
			if (document.getElementById(myObject.id).value == '') {
				document.getElementById(myObject.id + '_value').value		= '';
				if (document.getElementById('PulldownMenu').style.display != 'none') {
					UnloadMenu();
				}
			}
			
			
		}
	}

	this.showMenu = function(myObject, Search) {
		selectItem = false;
		
		var current_length	= 0;
		
		var MenuHTML = '<table width="100%" cellpadding="1" cellspacing="0"';
		MenuHTML += ' onmouseover="if (selectItem) { document.getElementById(\'i\' + selectItem).style.backgroundColor=\'\';document.getElementById(\'i\' + selectItem).style.color=\'\'}" ';
		MenuHTML += ' onmouseout="if (selectItem) { document.getElementById(\'i\' + selectItem).style.backgroundColor=\'#3399FF\';document.getElementById(\'i\' + selectItem).style.color=\'#FFFFFF\';}" ';
		MenuHTML += '>';
		
		displayMenuItem = new Array;
		var j = -1;
		if (Search.charCodeAt(0) > 255 && / /.test(Search)) {
			Search = Search.split(" ");
			var test;
			var showname;
			for (i=0; i<this.item.length; i++) {
				test = true;
				for (j=0; j<Search.length; j++) {
					if (!RegExp(Search[j], 'i').test(this.item[i])) {
						test = false;
					} 
				}
				if (test) {
					j++;
					current_length++;
					displayMenuItem.push(this.item[i]);
					if (this.item[i].constructor == Array) {
						showname	= this.item[i].join('&nbsp; ');
					} else {
						showname	= this.item[i];
					}
					MenuHTML += '<tr height="18"><td id="i' + j + '" style="cursor:pointer" onclick="document.getElementById(\'' + myObject.id + '\').value = this.innerHTML; document.getElementById(\'' + myObject.id + '_value\').value = \'' + this.value[i] + '\'; UnloadMenu(); document.getElementById(\'' + myObject.id + '\').focus(); if (document.getElementById(\'' + myObject.id + '_value\').onchange) document.getElementById(\'' + myObject.id + '_value\').onchange();" onmouseover="this.style.backgroundColor=\'#3399FF\';this.style.color=\'#FFFFFF\'" onmouseout="this.style.backgroundColor=\'\';this.style.color=\'\'">' + showname + '</tr></td>';
				}
				if (current_length > 150) break;
			}
		} else {
			for (i=0; i<this.item.length; i++) {
//				if (Search == '' || (Search.charCodeAt(0) > 255 && RegExp(Search, 'i').test(this.item[i])) || RegExp('^' + Search, 'i').test(this.item[i])) {
				if (Search == '' || RegExp(Search, 'i').test(this.item[i])) {
					j++;
					current_length++;
					displayMenuItem.push(this.item[i]);
					if (this.item[i].constructor == Array) {
						showname	= this.item[i].join('&nbsp; ');
					} else {
						showname	= this.item[i];
					}
					MenuHTML += '<tr height="18"><td id="i' + j + '" style="cursor:pointer" onclick="document.getElementById(\'' + myObject.id + '\').value = this.innerHTML; document.getElementById(\'' + myObject.id + '_value\').value = \'' + this.value[i] + '\'; UnloadMenu(); document.getElementById(\'' + myObject.id + '\').focus(); if (document.getElementById(\'' + myObject.id + '_value\').onchange) document.getElementById(\'' + myObject.id + '_value\').onchange();" onmouseover="this.style.backgroundColor=\'#3399FF\';this.style.color=\'#FFFFFF\'" onmouseout="this.style.backgroundColor=\'\';this.style.color=\'\'">' + showname + '</tr></td>';
				} 
				if (current_length > 150) break;
			}
		}
		document.getElementById('PulldownMenu').innerHTML = MenuHTML + '</table>';
		var Total = Math.min(displayMenuItem.length, 20);
		if (Total > 0) {
			var Height = Total * 18 + 20;
			var X = GetX(myObject);
			var Y = GetY(myObject);
			document.getElementById('PulldownMenu').style.width = myObject.offsetWidth;
			document.getElementById('PulldownMenu').style.height = Height;
			document.getElementById('PulldownMenu').style.left = X;
//			if (document.body.scrollHeight - Y - myObject.offsetHeight > Height) {
			if (false && document.body.scrollHeight - Y - myObject.offsetHeight > Height) {
				document.getElementById('PulldownMenu').style.top = Y - Height + 3;
				document.body.onmousedown = function(Event) {
					if (GetMouseX(Event) < X || GetMouseX(Event) > X + myObject.offsetWidth || GetMouseY(Event) < Y - Height || GetMouseY(Event) > Y + myObject.offsetHeight) {
						UnloadMenu();
					}	
				}
			} else {
				document.getElementById('PulldownMenu').style.top = Y + myObject.offsetHeight - 1;
				document.body.onmousedown = function(Event) {
					if (GetMouseX(Event) < X || GetMouseX(Event) > X + myObject.offsetWidth || GetMouseY(Event) < Y || GetMouseY(Event) > Y + myObject.offsetHeight + Height) {
						UnloadMenu();
					}	
				}
			}
			document.getElementById('PulldownMenu').style.display = '';
			
			this.displaying		= true;
			
		} else {
			UnloadMenu();
		}
	}
}

function UnloadMenu() {
	document.body.onmousedown = null;
	document.getElementById('PulldownMenu').style.display = 'none';
	this.displaying		= false;
}