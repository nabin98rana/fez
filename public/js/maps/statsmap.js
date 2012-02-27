
// global variables for these functions
var map = null;
var currentMarkerList = {};


// ===================
// Initialises the map
// ===================
function initialiseMap()
{
	map = new GMap2(document.getElementById("map_canvas"));
	map.setCenter(new GLatLng(0,0),0);

	markerManager = new MarkerManager(map);
	map.addControl(new GSmallZoomControl3D(), new GControlPosition(G_ANCHOR_TOP_LEFT));

	$('map_canvas').style.height = '400px';
	$('map_canvas').style.width = '572px';
	map.checkResize();
	updateMapMarkers();
	map.setCenter(new GLatLng(47.749481,16.683426), 1);

	GEvent.addListener(map, 'zoomend', function() { updateMapMarkers(); });
	GEvent.addListener(map, 'moveend', function() { updateMapMarkers(); });
}

// =======================
// create markers function
// =======================
function createMarker(point,html,icon) {
	var marker = new GMarker(point, {icon:icon});
	GEvent.addListener(marker, "mouseover", function() {
		marker.openToolTip(html);
	});
	GEvent.addListener(marker, 'mouseout', function() {
		marker.closeToolTip();
	})
	return marker;
}

// ==================
// Update the markers
// ==================
function updateMapMarkers() {

	changeBodyClass('standby', 'loadingAlert');

	var zoomLevel = map.getZoom();
	var bounds = map.getBounds();
	var ne = bounds.getNorthEast();
	var sw = bounds.getSouthWest();
	var neLat = ne.lat();
	var neLong = ne.lng();
	var swLat = sw.lat();
	var swLong = sw.lng();

	var dataType = $('map_selection_options').value;

	var urlParams = '?zoom='+zoomLevel+'&neLat='+neLat+'&neLong='+neLong+'&swLat='+swLat+'&swLong='+swLong+'&dataType='+dataType;

	new Ajax.Request('mapAjaxRequest.php'+urlParams, {
		method: 'get',
		onSuccess: function(transport) {
			var points;
			var sidebarDetails;
			eval(transport.responseText);
			map.clearOverlays();

			for (i in points) {
				if (!isNaN(points[i].lat)) {
					var html = "<strong>"+points[i].name+"</strong>";

					if (dataType == 'ABSTRACTS_ONLY' || dataType == 'BOTH')
					{
						html = html+"<br />Abstracts: "+points[i].abstracts;
					}

					if (dataType == 'DOWNLOADS_ONLY' || dataType == 'BOTH')
					{
						html = html+"<br />Downloads: "+points[i].downloads;
					}

					var icon = MapIconMaker.createMarkerIcon({width: points[i].width, height: points[i].height, primaryColor: points[i].colour});
					var point = new GLatLng(points[i].lat, points[i].lng);
					var marker = createMarker(point, html, icon);
					map.addOverlay(marker);
				}
			}

			var legendText = '';
			for (i in sidebarDetails) {
				if (sidebarDetails[i].level !== undefined) {
					var level = sidebarDetails[i].level
					var minName = sidebarDetails[i].min.name;
					var maxName = sidebarDetails[i].max.name;

					var levelText = '<strong class="levelName">'+sidebarDetails[i].levelName+'</strong>';
					levelText = levelText.concat('<dl class="map-legend">');

					levelText = levelText.concat('<dt><img src="/images/mapicon_'+level+'_large.png" /></dt>');
					levelText = levelText.concat('<dd><strong>'+maxName+'</strong>: ');

					// output the large icon and details
					if (dataType == 'ABSTRACTS_ONLY' || dataType == 'BOTH') {
						levelText = levelText.concat('<br />Abstracts:'+sidebarDetails[i].max.abstracts+' ');
					}
					if (dataType == 'DOWNLOADS_ONLY' || dataType == 'BOTH') {
						levelText = levelText.concat('<br />Downloads:'+sidebarDetails[i].max.downloads+' ');
					}
					levelText = levelText.concat('</dd>');

					// output the small icon and details if they're not the same as the large icon details
					if (minName != maxName) {
						levelText = levelText.concat('<dt><img src="/images/mapicon_'+level+'_small.png" /></dt>');
						levelText = levelText.concat('<dd><strong>'+minName+'</strong>: ');
						if (dataType == 'ABSTRACTS_ONLY' || dataType == 'BOTH') {
							levelText = levelText.concat('<br />Abstracts:'+sidebarDetails[i].min.abstracts+' ');
						}
						if (dataType == 'DOWNLOADS_ONLY' || dataType == 'BOTH') {
							levelText = levelText.concat('<br />Downloads:'+sidebarDetails[i].min.downloads+' ');
						}
						levelText = levelText.concat('</dd>');
					}
					levelText = levelText.concat('</dl>')

					legendText = legendText.concat(levelText);
				}
			}

			$('sidebar-info').innerHTML = legendText;

		},
		onComplete: function() {
			changeBodyClass('loadingAlert', 'standby');
		}
	});

}

// =====================
// change the body class
// =====================
function changeBodyClass(from, to) {
	document.body.className = document.body.className.replace(from, to);
	return false;
}

// =================================
// Toggles the maximising of the map
// =================================
function maxMapToggle() {
	var mapStyle = $('map_canvas').style.height;

	if (mapStyle == '400px') {
		var width = document.viewport.getWidth() - 20;
		width = width+'px';
		var height = document.viewport.getHeight()+'px';
		$('map_canvas').style.width = width;
		$('map_canvas').style.height = height;
		Element.scrollTo('map_canvas');
	}
	else {
		$('map_canvas').style.height = '400px';
		$('map_canvas').style.width = '572px';
	}

	map.checkResize();
	updateMapMarkers();
}


