/**
 * GoogleMapField.js
 * @author Will Morgan <will.morgan@betterbrief.co.uk>
 */
(function($) {

	var gmapsAPILoaded = false;

	// Run this code for every googlemapfield
	function initField() {
		var field = $(this),
			options = JSON.parse(field.attr('data-settings')),
			centre = new google.maps.LatLng(options.coords[0], options.coords[1]),
			options = {
				streetViewControl: false,
				zoom: options.map.zoom,
				center: centre,
				mapTypeId: google.maps.MapTypeId[options.map.mapTypeId]
			},
			mapElement = field.find('.googlemapfield-map'),
			map = new google.maps.Map(mapElement[0], options),
			marker = new google.maps.Marker({
				position: map.getCenter(),
				map: map,
				title: "Position",
				draggable: true
			}),
			latField = field.find('input.googlemapfield-latfield'),
			lngField = field.find('input.googlemapfield-lngfield'),
			search = field.find('input.googlemapfield-searchfield');

		// Update the hidden fields and mark as changed
		function updateField(latLng, init) {
			var latCoord = latLng.lat(),
				lngCoord = latLng.lng();

			options.coords = [latCoord, lngCoord];

			latField.val(latCoord);
			lngField.val(lngCoord);
		}

		function centreOnMarker() {
			var center = marker.getPosition();
			map.panTo(center);
			updateField(center);
		}

		function mapClicked(ev) {
			var center = ev.latLng;
			marker.setPosition(center);
			updateField(center);
		}

		function geoSearchComplete(result, status) {
			if(status !== google.maps.GeocoderStatus.OK) {
				console.warn('Geocoding search failed');
				return;
			}
			marker.setPosition(result[0].geometry.location);
			centreOnMarker();
		}

		function searchReady(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			var searchText = search.val(),
				geocoder;
			if(searchText) {
				geocoder = new google.maps.Geocoder();
				geocoder.geocode({ address: searchText }, geoSearchComplete);
			}
		}

		// Populate the fields to the current centre
		updateField(map.getCenter(), true);

		google.maps.event.addListener(marker, 'dragend', centreOnMarker);

		google.maps.event.addListener(map, 'click', mapClicked);

		search.bind({
			'change': searchReady,
			'keydown': function(ev) {
				if(ev.which == 13) {
					searchReady(ev);
				}
			}
		});

	}

	function init() {
		var mapFields = $('.googlemapfield');
		mapFields.each(initField);
	}

	// Export the init function
	window.googlemapfieldInit = function() {
		gmapsAPILoaded = true;
		$(function() {
			init();
		});
	}

	window.gmapsAPILoaded = gmapsAPILoaded;

}(jQuery));

Behaviour.register({
	'#Form_EditForm': {
		initialize: function() {
			this.observeMethod('PageLoaded', this.pageLoaded);
			//this.observeMethod('BeforeSave', this.beforeSave);
			this.pageLoaded();
		},
		pageLoaded: function() {
			try {
				googlemapfieldInit();
			}
			catch(e) { /* i am sorry JS hater community please forgive me */ }
		}
	}
});
