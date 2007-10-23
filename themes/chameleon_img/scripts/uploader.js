var updater;  

// main function for upload monitoring
function submitPostUsingAjax() {                
try {
// get upload status
alert("trying");
updater = new Ajax.PeriodicalUpdater('uploadStatusDiv','testupload', {
asynchronous:true, 
frequency:1, 
method:'get',
onSuccess:function(request) {
if (request.responseText.length > 1) {         
$('progressBar').style.visibility = 'visible';
$('progressBar').style.width = request.responseText + '%';
}
}                                                                     
});
} catch(e) {
alert('submitPostUsingAjax() failed, reason: ' + e);
} finally {
}                                       
return false;
}                                                                       

// executes function after page loaded
function addLoadEvent(func) {
var oldonload = window.onload;       
if (typeof window.onload != 'function') {
window.onload = func;
} else {
window.onload = function() {
oldonload();
func();
}
}
}                                       

// this function will be executed after "target" IFRAME content changed
function handleUploadFinished() {
// stop updater manually
if (typeof updater != 'undefined') {
updater.stop();
updater = null;
}                                                                   
}

// observe IFrame object for "load" event to stop AJAX updater
function observeFormSubmit() {
// add event to observe upload target
Event.observe('trgID', 'load', handleUploadFinished);
}                               

// add event handler only after page loaded
addLoadEvent(observeFormSubmit);        