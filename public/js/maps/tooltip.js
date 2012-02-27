
// Original from Beginning Google Maps Applications with PHP and Ajax book by M Purvis, J Sambells and C Turner
// Modified for Fez by Marko Tsoi 2009

// ========================
// set up the tooltip class
// ========================
function ToolTip(marker, html, width) {
	this.html_ = html;
	this.width_ = (width ? width+'px' : 'auto');
	this.marker_ = marker;
}

ToolTip.prototype = new GOverlay();

ToolTip.prototype.initialize = function(map) {
	var div = document.createElement("div");
	div.style.display = 'none';
	map.getPane(G_MAP_FLOAT_PANE).appendChild(div);
	this.map_ = map;
	this.container_ = div;
}

ToolTip.prototype.remove = function() {
	this.container_.parentNode.removeChild(this.container_);
}

ToolTip.prototype.copy = function() {
	return new Tooltip(this.html_);
}

ToolTip.prototype.redraw = function(force) {
	if (!force) return;

	var pixelLocation = this.map_.fromLatLngToDivPixel(this.marker_.getPoint());
	this.container_.innerHTML = this.html_;
	this.container_.style.position = 'absolute';
	this.container_.style.left = pixelLocation.x + "px";
	this.container_.style.top = pixelLocation.y + "px";
	this.container_.style.width = this.width_;
	this.container_.style.font = 'bold 10px/10px verdana, arial, sans';
	this.container_.style.border = '1px solid black';
	this.container_.style.background = '#ffffff';
	this.container_.style.padding = '4px';

	//one line to desired width
	this.container_.style.whiteSpace = 'nowrap';
	if(this.width_ != 'auto') this.container_.style.overflow = 'hidden';
	this.container_.style.display = 'block';
}

GMarker.prototype.ToolTipInstance = null;

GMarker.prototype.openToolTip = function(content) {
	//don't show the tool tip if there is acustom info window
	if(this.ToolTipInstance == null) {
		this.ToolTipInstance = new ToolTip(this,content)
		map.addOverlay(this.ToolTipInstance);
	}
}

GMarker.prototype.closeToolTip = function() {
	if(this.ToolTipInstance != null) {
		map.removeOverlay(this.ToolTipInstance);
		this.ToolTipInstance = null;
	}
}
