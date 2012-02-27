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

// ==================================================================
// Shows the details div if necessary (called by 'show details' link)
// ==================================================================
function showOutstandingItemDetails(pid) {

	var containingDiv = dojo.byId("outstandingWorkflowsDiv");

	// get the counts
	var xhrArgs = {
		url: "{/literal}{$smarty.const.APP_RELATIVE_URL}{literal}ajax_pid_outstanding_events.php?pid="+pid+"&type=COUNT",
		handleAs: "json",
		load: function(data) {

			// if all counts are zero, let the user know and hide the enitre containing div
			if (data.statusString == '') {
				containingDiv.innerHTML = "All outstanding items have been finished";
				setTimeout("fadeOutOutstandingWorkflowsDiv()", 4000);
			} else {
			
				// otherwise, update the status
				var statusSpan = dojo.byId('outstandingStatus');
				statusSpan.innerHTML = data.statusString;
			
				// show the details div
				showElement('outstandingDetailsDiv');
			
				// now show the details
				updateOutstandingWorkflows(data.outstandingWorkflows, pid);
				updateBackgroundJobs(data.backgroundJobs, pid);
			}
		},
		error: function(error) {
			containingDiv.innerHTML = "An unexpected error occurred";
		}
	}

	//Call the asynchronous xhrGet
	var deferred = dojo.xhrGet(xhrArgs);
	
}


// ================================================================
// update the outstanding items div (called via 'check again' link)
// ================================================================
function updateOutstandingDiv(pid) {

	var containingDiv = dojo.byId("outstandingWorkflowsDiv");
	
	var spinner = dojo.byId('outstandingSpinner');
	spinner.style.display = "inline";
	
	// get the counts
	var xhrArgs = {
		url: "{/literal}{$smarty.const.APP_RELATIVE_URL}{literal}ajax_pid_outstanding_events.php?pid="+pid+"&type=COUNT",
		handleAs: "json",
		load: function(data) {

			// if all counts are zero, let the user know and hide the enitre containing div
			if (data.statusString.length == 0) {
				containingDiv.innerHTML = "All outstanding items have been finished";
				setTimeout("fadeOutOutstandingWorkflowsDiv()", 4000);
			} else {

				// otherwise, update the status
				var statusSpan = dojo.byId('outstandingStatus');
				statusSpan.innerHTML = data.statusString;
				
				// and if necessary, update each bit
				var detailsDiv = dojo.byId('outstandingDetailsDiv');
				if (detailsDiv.style.display != 'none') {
					updateOutstandingWorkflows(data.outstandingWorkflows, pid);
					updateBackgroundJobs(data.backgroundJobs, pid);
				}

				// hide the spinner
				spinner.style.display = 'none';
			}
			
		},
		error: function(error) {
			var detailsDiv = dojo.byId('outstandingDetailsDiv');
			detailsDiv.innerHTML = "An unexpected error occurred";
		}
	}

	//Call the asynchronous xhrGet
	var deferred = dojo.xhrGet(xhrArgs);
	
}

// ====================================
// update the outstanding workflows div
// ====================================
function updateOutstandingWorkflows(count, pid) {
	
	var outstandingWorkflowsDiv = dojo.byId('outstandingWorkflowsDetails');
	
	if (count > 0) {
		
		// generate the table with details
		var xhrArgs = {
			url: "{/literal}{$smarty.const.APP_RELATIVE_URL}{literal}ajax_pid_outstanding_events.php?pid="+pid+"&type=WORKFLOW",
			handleAs: "json",
			load: function(data) {

				var str = '';

				if (data.length > 0) {
					str = "<br /><strong>Workflows</strong><br /><table id='outstandingWorkflowsDetailsTable' class='outstandingDetailsTable'><tr><th>Workflow ID</th><th>Who</th><th>What</th><th>Time started</th><th>Last time user touched system</th></tr>";
					for (var i = 0; i < data.length; i++) {
						str = str+"<tr><td>"+data[i].workflowId+"</td><td>"+data[i].username+"</td><td>"+data[i].workflowTitle+"</td><td>"+data[i].dateStarted+"</td><td>"+data[i].sessionLastUpdated+"</td></tr>";
					}
					str = str+"</table>";
				}

				outstandingWorkflowsDiv.innerHTML = str;

				showElement('outstandingWorkflowsDetails', 500);
			},
			error: function(error) {
				targetNode.innerHTML = "An unexpected error occurred";
			}
		}

		//Call the asynchronous xhrGet
		var deferred = dojo.xhrGet(xhrArgs);
	} else {
		hideElement('outstandingWorkflowsDetails');
	}
}

// =================================
// update the background details div
// =================================
function updateBackgroundJobs(count, pid) {

	var outstandingBgpDetails = dojo.byId('outstandingBackgroundProcessDetails');
	
	if (count > 0) {
		
		// generate the table with details
		var xhrArgs = {
			url: "{/literal}{$smarty.const.APP_RELATIVE_URL}{literal}ajax_pid_outstanding_events.php?pid="+pid+"&type=BACKGROUND",
			handleAs: "json",
			load: function(data) {

				var str = '';

				if (data.length > 0) {
					str = "<br /><strong>Background Processes</strong><br /><table id='outstandingBackgroundProcessDetailsTable' class='outstandingDetailsTable' ><tr><th>Process ID</th><th>Who</th><th>What</th><th>Time started</th></tr>";
					for (var i = 0; i < data.length; i++) {
						str = str+"<tr><td>"+data[i].bgpId+"</td><td>"+data[i].username+"</td><td>"+data[i].statusMessage+"</td><td>"+data[i].dateStarted+"</td></tr>";
					}
					str = str+"</table>";
				}

				outstandingBgpDetails.innerHTML = str;

				// if necessary, show the details div
				showElement('outstandingBackgroundProcessDetails', 500);
			},
			error: function(error) {
				targetNode.innerHTML = "An unexpected error occurred";
			}
		}

		//Call the asynchronous xhrGet
		var deferred = dojo.xhrGet(xhrArgs);
	} else {
		hideElement('outstandingBackgroundProcessDetails');
	}
}

// ====================================================================
// fade out the whole div (as we don't have any outstanding items left)
// ====================================================================
function fadeOutOutstandingWorkflowsDiv() {
	var fadeArgs = { node: "outstandingWorkflowsDiv" };
	dojo.fadeOut(fadeArgs).play();
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
