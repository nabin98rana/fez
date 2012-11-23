{literal}
function openHistory(pid)
{
    var features = 'width=650,height=500,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
    var popupWin = window.open('{/literal}{$rel_url}{literal}history.php?pid=' + pid, '_impact', features);
    popupWin.focus();
}

function showDiv(p)
{
    if( document.getElementById(p).style.display == "block" )
    {
        document.getElementById(p).style.display = "none";
    }
    else
    {
        document.getElementById(p).style.display = "block";
    }
}

// ======================================
// helper functions to show/hide elements
// ======================================
function showElement(elementName, duration, displayStyle) {

	// set some defaults
	duration = typeof(duration) != 'undefined' ? duration : 200;
	displayStyle = typeof(displayStyle) != 'undefined' ? displayStyle : 'block';

	var checkNode = dojo.byId(elementName);
	if (checkNode.style.display == 'none' || checkNode.style.display == '') {
		var fadeArgs = {
			node: elementName,
			duration: duration,
			beforeBegin: function() {
				var node = dojo.byId(elementName);
				if (node.style.display != displayStyle) {
					dojo.style(node, "opacity", 0);
					dojo.style(node, "display", displayStyle);
				}
			}
		};
		x = dojo.fadeIn(fadeArgs);
		x.delay = 350;
		x.play();
	}
}
function hideElement(elementName, duration) {

	// set defaults
	duration = typeof(duration) != 'undefined' ? duration : 200;

	hideThisElement = dojo.byId(elementName);
	if (hideThisElement.style.display != 'none') {
		var fadeArgs = {
			node: elementName,
			duration: duration,
			onEnd: function() {
				var node = dojo.byId(elementName);
				node.style.display = 'none';
			}
		};
		dojo.fadeOut(fadeArgs).play();
	}
}

{/literal}
