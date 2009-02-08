
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | This file was derived from http://www.quirksmode.org/ specifically at|
// | http://www.quirksmode.org/book/examplescripts/maxlength/             |
// | (Copyright Notice: http://www.quirksmode.org/about/copyright.html)   |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | NOTE: refers to 2 styles that need to be difined: counter & toomuch  |
// +----------------------------------------------------------------------+


var W3CDOM = document.createElement && document.getElementsByTagName;

function init_textarea_validation() { 
	setMaxLength();
}

function setMaxLength() {
	if (!W3CDOM) return;
	var textareas = document.getElementsByTagName('textarea');
	var counter = document.createElement('div');
	counter.className = 'maxlength';
	for (var i=0;i<textareas.length;i++) {
		if (textareas[i].getAttribute('maxlength')) {
			var counterClone = counter.cloneNode(true);
			counterClone.innerHTML = '<span>0</span>/'+textareas[i].getAttribute('maxlength');
			textareas[i].parentNode.insertBefore(counterClone,textareas[i].nextSibling);
			textareas[i].relatedElement = counterClone.getElementsByTagName('span')[0];
			textareas[i].onkeyup = textareas[i].onchange = checkMaxLength;
			textareas[i].onkeyup();
		}
	}
}

function checkMaxLength() {
	var maxLength = this.getAttribute('maxlength');
	var currentLength = this.value.length;
	if (currentLength > maxLength)
		this.relatedElement.className = 'maxlength_exceeded';
	else
		this.relatedElement.className = '';	
	this.relatedElement.firstChild.nodeValue = currentLength;
}
