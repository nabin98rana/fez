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

function drawMap(){//drawing the map on the left side
    if($('#spatial_coverage_map').length > 0){//if there is a coverage
        var latlng = new google.maps.LatLng(-25.397, 133.644);
        var myOptions = {
            zoom: 2,disableDefaultUI: true,center:latlng,panControl: true,zoomControl: true,mapTypeControl: true,scaleControl: true,
            streetViewControl: false,overviewMapControl: true,mapTypeId: google.maps.MapTypeId.TERRAIN
        };
        var map2 = new google.maps.Map(document.getElementById("spatial_coverage_map"),myOptions);
        var bounds = new google.maps.LatLngBounds();

        //draw coverages
        var coverages = $('p.coverage');
        var mapContainsOnlyMarkers = true; // if there is only marker, then zoom out to a default depth (markers get "bounded" at max zoom level)
        var locationText = [];

        $.each(coverages, function(){
            setTimeout('500');
            coverage = $(this).text();
            split = coverage.split(' ');
            if(split.length>1)
            {
                mapContainsOnlyMarkers = false;
                coords = [];
                $.each(split, function(){
                    coord = stringToLatLng(this);
                    coords.push(coord);
                    bounds.extend(coord);
                });
                poly = new google.maps.Polygon({
                    paths: coords,
                    strokeColor: "#FF0000",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: "#FF0000",
                    fillOpacity: 0.35
                });
                poly.setMap(map2);
            }else{
                var marker = new google.maps.Marker({
                    map: map2,
                    position: stringToLatLng($(this).html()),
                    draggable: false,
                    raiseOnDrag:false,
                    visible:true
                });
                bounds.extend(stringToLatLng($(this).html()));
            }
        });

        //draw centers
        var centers = $('p.spatial_coverage_center');
        $.each(centers, function(){
            drawable = true;
            var marker = new google.maps.Marker({
                map: map2,
                position: stringToLatLng($(this).html()),
                draggable: false,
                raiseOnDrag:false,
                visible:true
            });
        });

        map2.fitBounds(bounds);

        if (mapContainsOnlyMarkers)
        {
            // CC-197/CC-304 - Center map on markers
            // fitBounds tends to wrap to max zoom level on markers
            // we still want a "good" fit if there are multiple markers, but
            // if we're zoomed to close, lets zoom out once the map loads!
            var listener = google.maps.event.addListenerOnce(map2, "idle", function() {
                if (map2.getZoom() > 3) map2.setZoom(3);
            });
        }
    }
}

function stringToLatLng(str){
    var word = str.split(',');
    var lat = word[1];
    var lon = word[0];
    var coord = new google.maps.LatLng(parseFloat(lat), parseFloat(lon));
    return coord;
}

{/literal}
