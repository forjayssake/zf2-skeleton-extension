/**
 * Leaflet maps wrapper
 */

var Mapper = Mapper || {};

function Mapper(mapDiv, options)
{
	// map vars
	this.map;
	this.mapdiv 			= mapDiv;				// div id to hold map tiles
	this.layers 			= [];					// array of layers
	this.bounds 			= new L.LatLngBounds();	// 'primary' map bounds - the bounds we want to display on init 
	this.associatedBounds 	= new L.LatLngBounds();	// 'max' map bounds - contains all associated points - for zomm/pan
	this.defaultZoom 		= 16;					// zoom level if none specified - should display roughly UK
	this.singleMarkerZoom 	= 17;					// default zoom level for displaying a single primary marker 
	this.defaultCentreZoom 	= 12; 					// default zoom when centreing map to specific lat/lng 
	this.hasMapControls 	= false;				// where the map displays zoom controls
	
	// marker vars
	this.markers 			= [];					// array of all markers on the map
	this.primaryMarker;
	this.allowAddOnClick 	= false;				// override for allowing markers to be added on map click
	this.maxAddOnClick 		= 1;					// maximum markers a user can add by clicking on the map
	this.showPopups 		= false;				// whether to show popups on markers 
	this.canDragSingle 		= false; 				// allow markers created outside of addMarker to be draggable - set before creating
	this.defaultPrimaryIcon;
	this.defaultSecondaryIcon;
	this.defaultRemovedIcon;
	
	/**
	 * Generate the initial map image
	 */
	this._renderMap = function()
	{
		this.defineCustomMarkers();
		
		this.map = new L.Map(this.mapdiv);
		
		var streetTile = 'http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png';
		var mapQuestUrl = streetTile,
			mapQuestSubDomains = ['otile1','otile2','otile3','otile4'],
			mapQuestAttrib = 'Data, imagery and map information provided by <a href="http://open.mapquest.co.uk" target="_blank">MapQuest</a>, <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> and contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC-BY-SA</a>  - Search powered by <a href="http://geonames.org" target="_BLANK">geonames.org</a>. Reverse geocoding service by <a href="http://nominatim.openstreetmap.org/" target="_blank">Nominatim</a>';

		var mapQuest = new L.TileLayer(mapQuestUrl, {maxZoom: 18, attribution: mapQuestAttrib, subdomains: mapQuestSubDomains});
		var centre = new L.LatLng(50.73577, -3.53527); 

		this.map.setView(centre, this.defaultZoom).addLayer(mapQuest);
	};
	
	// define custom map markers and defaults
	this.defineCustomMarkers = function()
	{
		this.defaultPrimaryIcon = L.AwesomeMarkers.icon({
			icon: 'tree',
			markerColor: 'darkgreen'
		});
		
		this.defaultSecondaryIcon = L.AwesomeMarkers.icon({
			icon: 'tree',
			markerColor: 'orange'
		});
		
		this.defaultRemovedIcon = L.AwesomeMarkers.icon({
			icon: 'ban',
			markerColor: 'darkred'
		});
	};
	
	// set whether markers shoudl display popups - content currently defined from  this.getPopupContents
	this.setShowMarkerPopups = function(showPopups)
	{
		this.showPopups = Boolean(showPopups) === false ? false : true;
	};
	
	// add a marker name to the markers array (also used for searching)
	this.addLayerReference = function()
	{
		this.markers.push(marker);
	};
	
	// add a layers control to the map
	this.addMapControls = function()
	{
		var layersControl = new L.Control.Layers(null, this.overlaysObj);
		this.map.addControl(layersControl);
		this.hasMapControls = true;
	};
	
	// centre the map to a specific lat/lng location
	this.center = function(lat, lng, zoomlevel){
		var location = new L.LatLng(lat, lng);
		
		if (zoomlevel == undefined) {
			zoomlevel = this.defaultCentreZoom;
		}
		
		this.map.setView(location, zoomlevel, true);
	};
	
	// add a marker to the map
	this.addMarker = function(markerLatLon, draggable, icon, isPrimary, popupContent)
	{
		var marker = null;
		
		if (icon  == null)
		{
			icon = this.defaultPrimaryIcon;
		}
		
		if (isPrimary == null)
		{
			isPrimary = false;
		}
		
		if (markerLatLon != null) {
			
			marker = new L.Marker(markerLatLon, {'bounceOnAdd' : true, 'draggable' : draggable, 'icon' : icon});
			
			if (popupContent != null && popupContent !== undefined)
			{
				this.bindPopup(marker, popupContent);
			}
			
			if (isPrimary)
			{
				this.primaryMarker = marker;
			}
			
			if (draggable) {
		    	var obj = this;
		    	marker.on('dragend', function(e) {
		    		var dragLatLng = this.getLatLng();
		    		obj.updateLatLngInputs(dragLatLng.lat, dragLatLng.lng);
		    	});
			}
			
			this.markers.push(marker);
			this.map.addLayer(marker);
			this.layers.push(marker);
		}
		
		return marker;
	};
	
	// bind a popup with content to a marker
	this.bindPopup = function(marker, contentJson)
	{
		var $content = this.formatPopupContent(contentJson);
		
		marker.bindPopup($content);
		return marker;
	};
	
	this.formatPopupContent = function(contentJson)
	{
		var $content = '<div class="map-popup-content">';
		
		var lat = contentJson.lat.substr(0, 10);
		var lng = contentJson.lng.substr(0, 10);
		$content = $content + '<div class="alert alert-info map-popup-gridref"><i class="fa fa-globe"></i> ' + lat + '&deg; N ' + lng + '&deg; W</div>';
		
		$content = $content + '<dl class="dl-horizontal dl-map-popup">';
		$content = $content + '<dt><i class="fa fa-compass"></i> Location</dt><dd>' + contentJson.locationName + '</dd>';
		
		var planted = contentJson.datePlanted == null ? contentJson.yearPlanted : contentJson.datePlanted.substr(0,10);
		$content = $content + '<dt><i class="fa fa-calendar"></i> Planted</dt><dd>' + planted + '</dd>';
		
		if (contentJson.yearDied != null)
		{
			$content = $content + '<dt class="danger"><i class="fa fa-calendar"></i> Died/Removed</dt><dd class="danger">' + contentJson.yearDied + '</dd>';
		}
		
		$content = $content + '</dl>';
		
		$content = $content + '<a class="btn btn-xs btn-primary" href="' + contentJson.url + '">View Location</a>';
		
		$content = $content + '</div>';
		
		return $content
	}
	
	// set the bounds of the map to a single marker
	this.setBoundsToMarker = function(marker, zoomLevel)
	{
		if (zoomLevel == undefined) {
			zoomLevel = this.singleMarkerZoom;
		}
		this.map.setView(marker.getLatLng(), zoomLevel, true);
	};
	
	// set whether marker(s) can be added by clicking the map  
	this.setAllowAddOnClick = function(allowed, max)
	{
		this.allowAddOnClick = Boolean(allowed) === true ? true : false;
		this.maxAddOnClick = (max != undefined ? parseInt(max) : this.maxAddOnClick) ; 
		
		var mapObj = this;
		
		if (this.allowAddOnClick)
		{
			this.map.on('click', function(e) {mapObj.addMarkerOnClick(e);});
		} else {
			this.map.unbind('click');
		};
		this.map.invalidateSize();
	};

	// add a map marker when the map is clicked
	this.addMarkerOnClick = function(event)
	{
		if (this.allowAddOnClick && this.markers.length < this.maxAddOnClick)
		{
	    	var clickLatLng = event.latlng;
	    	var marker = this.addMarker(clickLatLng, {'draggable' : true});
	    	
	    	// update page lat/lng inputs (if present) when the marker is created
	    	this.updateLatLngInputs(clickLatLng.lat, clickLatLng.lng);
	    	
	    	// bind the update of lat/lng inputs to the marker drag end event as well
	    	var obj = this;
	    	marker.on('dragend', function(e) {
	    		var dragLatLng = this.getLatLng();
	    		obj.updateLatLngInputs(dragLatLng.lat, dragLatLng.lng);
	    	});
	    	
	    	return true;
		};
		
		return false;
	};
	
	// update the values for `lat` and `lng` input elements are found on the page 
	this.updateLatLngInputs = function(lat, lng)
	{
	    if ($('#lat').length > 0) {	$('#lat').val(lat);  }
	    if ($('#lng').length > 0) {	$('#lng').val(lng);  }
	};
	
	// known bug - layers control not initializing with all layers checked
	$('.leaflet-control-layers-overlays input:checkbox').prop('checked', true);
	
	
	this._renderMap();					// create the map

}