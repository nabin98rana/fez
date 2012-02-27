var Bgps = function(config)
{
	this.config = config || {pollInt:20};
	this.hovering = 0;
	this.polling = 0;
	this.pollTimer = null;
};
var bgProc = new Bgps();

Bgps.prototype.addEvt = function(element, evt, action)
{
	if(element !== null)
		{
		if(element.addEventListener)
		{
			element.addEventListener(evt, action, false);
		}
		else if(element.attachEvent)
		{
			element.attachEvent('on' + evt, action);
		}
	}
};

Bgps.prototype.bgpsStats = function()
{
	var bgpx = new NajaxBackgroundProcessList;
    var stats = bgpx.getUserBgProcs();
    var elStat = document.getElementById('bgpsstat');
    var countReceptacle = document.getElementById('bgpsCount');
    var statIcn = document.createElement('img');
    var popup = document.getElementById('bgpsPopup');
    statIcn.setAttribute('src', rel_url + 'images/icons/' + stats.hgImg);
    statIcn.setAttribute('style', 'margin-top:0;');
    statIcn.setAttribute('alt', 'Background processes running');
    if(elStat && countReceptacle)
    {
    	elStat.innerHTML = "";
    	countReceptacle.innerHTML = '(' + stats.bgpsCount + ')';
        elStat.appendChild(statIcn);
    }
    
    if(popup)
    {
    	bgProc.drawBgpsPopup(stats.proclist);
    }
    return stats.proclist;
};

Bgps.prototype.drawBgpsPopup = function(stats)
{
	var recept = document.getElementById('bgpsReceptacle');
	recept.innerHTML = "";
	var popup = document.createElement('div');
	popup.setAttribute('id', 'bgpsPopup');
	popup.style.height = '400px';
	popup.style.width = '700px';
	popup.style.backgroundColor = '#CCC';
	popup.style.position = 'absolute';
	popup.style.overflow = 'scroll';
	popup.style.border = '2px solid #000';
	popup.style.right = '210px';
	popup.style.top = '135px';
	popup.style.zIndex = '300';
	var tbl = document.createElement('table');
	var tblBody = document.createElement('tbody');
	var tblHead = document.createElement('thead');
	tbl.setAttribute('border', '2');
	tbl.style.borderCollapse = 'separate';
	tbl.style.borderSpacing = '5px';
	
	var trhead = document.createElement('tr');
	
	var thheart = document.createElement('th');
	var thdatheart  = document.createTextNode('Heartbeat');
	thheart.appendChild(thdatheart);
	trhead.appendChild(thheart);
	
	var thname = document.createElement('th');
	var thdatname  = document.createTextNode('Name');
	thname.appendChild(thdatname);
	trhead.appendChild(thname);
	
	var thmsg = document.createElement('th');
	var thdatmsg  = document.createTextNode('Message');
	thmsg.appendChild(thdatmsg);
	trhead.appendChild(thmsg);
	
	tblHead.appendChild(trhead);
	tbl.appendChild(tblHead);
	
	for(i=0;i<stats.length;i++)
	{
		var trow = document.createElement('tr');
		
		var tdheart = document.createElement('td');
		var datheart = document.createTextNode(stats[i].bgp_heartbeat);
		tdheart.appendChild(datheart);
		trow.appendChild(tdheart);
		
		var tdname = document.createElement('td');
		var datname = document.createTextNode(stats[i].bgp_name);
		tdname.appendChild(datname);
		trow.appendChild(tdname);
		
		var tdstatus = document.createElement('td');
		var datstatus = document.createTextNode(stats[i].bgp_status_message);
		tdstatus.appendChild(datstatus);
		trow.appendChild(tdstatus);
		
		tblBody.appendChild(trow);
	}
	var closeBtn = document.createElement('a');
	closeBtn.setAttribute('id', 'bgpsClose');
	closeTxt = document.createTextNode('Close');
	closeBtn.appendChild(closeTxt);
	popup.appendChild(closeBtn);
	tbl.appendChild(tblBody);
	popup.appendChild(tbl);
	var parentEl = document.getElementById('bgpsReceptacle');
	parentEl.appendChild(popup);
	bgProc.addEvt(closeBtn, 'click', bgProc.removePopup);
};

Bgps.prototype.showPopup = function()
{
	var popup = document.getElementById('bgpsPopup');
	if(bgProc.hovering === 0 && !popup)
	{
		stats = bgProc.bgpsStats();
		bgProc.drawBgpsPopup(stats);
		bgProc.hovering = 1;
	}
};

Bgps.prototype.removePopup = function()
{
	var bgpsReceptacle = document.getElementById('bgpsReceptacle');
	bgpsReceptacle.innerHTML = "&nbsp;";
	bgProc.hovering = 0;
};

Bgps.prototype.addBgClick = function()
{
	var elStat = document.getElementById('bgpsstat');
	bgProc.addEvt(elStat, 'click', bgProc.showPopup);
	bgProc.addEvt(elStat, 'click', bgProc.bgpsStats);
};

Bgps.prototype.addBgHover = function()
{
	popLi = document.getElementById('bgpsstat');
	bgProc.addEvt(popLi, 'mouseover', bgProc.showPopup);
};

Bgps.prototype.poll = function()
{
	bgProc.bgpsStats();
	bgProc.pollBgProcs();
};

Bgps.prototype.pollBgProcs = function()
{
	bgProc.pollTimer = setTimeout('bgProc.poll()', bgProc.config.pollInt * 1000);
	bgProc.polling = 1;
};

Bgps.prototype.killPoll = function()
{
	if(bgProc.polling === 1)
	{
		clearTimeout(bgProc.pollTimer);
		bgProc.polling = 0;
	}
};
