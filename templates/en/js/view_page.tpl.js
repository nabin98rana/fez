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

function checkOutstandingWorkflows(pid) {
	//Look up the node we'll stick the text under.
	var targetNode = dojo.byId("outstandingWorkflowsText");
	
	// hide the check me link
	var fadeArgs = { node: "outstandingWorkflowsLink", duration: 200 };
	dojo.fadeOut(fadeArgs).play();

	//The parameters to pass to xhrGet, the url, how to handle it, and the callbacks.
	var xhrArgs = {
		url: "{/literal}{$smarty.const.APP_RELATIVE_URL}{literal}ajax_outstanding_workflows.php?pid="+pid,
		handleAs: "text",
		load: function(data) {

			var str = '';
			if (data == 0){
				str = 'There are currently no workflows ';
			} else if (data == 1) {
				str = 'There is currently 1 workflow ';
			} else {
				str = 'There are currently '+data+' workflows ';
			}
			str = str+'outstanding on this item.';

			targetNode.innerHTML = str;

			// if no data required then fade this out, then fade this out
			if (data == 0) {
				if (targetNode.style.display != 'none') {
					setTimeout("fadeOutOutstandingWorkflowsDiv()", 2000);
				}
			} else {
				// show the check me link
				dojo.fadeIn(fadeArgs).play();
			}
		},
		error: function(error) {
			targetNode.innerHTML = "An unexpected error occurred";
			dojo.fadeIn(fadeArgs).play();
		}
	}

	//Call the asynchronous xhrGet
	var deferred = dojo.xhrGet(xhrArgs);
}

function fadeOutOutstandingWorkflowsDiv() {
	var fadeArgs = { node: "outstandingWorkflowsDiv" };
	dojo.fadeOut(fadeArgs).play();
}
{/literal}