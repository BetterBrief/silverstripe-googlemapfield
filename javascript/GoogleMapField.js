/**
 * GoogleMapField.js
 * @author <@willmorgan>
 */
(function($) {

	var gmapsAPILoaded = false;

	// Run this code for every googlemapfield
	function initField() {
		var field = $(this),
			fieldID = field.attr('data-field-id'), // identify its settings
			options = JSON.parse(field.attr('data-settings')),
			centre = new google.maps.LatLng(options.coords[0], options.coords[1]),
			options = {
				streetViewControl: false,
				zoom: options.map.zoom * 1,
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
			latField = field.find('.googlemapfield-latfield'),
			lngField = field.find('.googlemapfield-lngfield'),
			zoomField = field.find('.googlemapfield-zoomfield'),
			search = field.find('.googlemapfield-searchfield');

		// Update the hidden fields and mark as changed
		function updateField(latLng, init) {
			var latCoord = latLng.lat(),
				lngCoord = latLng.lng();

			options.coords = [latCoord, lngCoord];

			latField.val(latCoord);
			lngField.val(lngCoord);

			if (!init) {
				// Mark as changed(?)
				$('.cms-edit-form').addClass('changed');
			}
		}

		function updateZoom() {
			zoomField.val(map.getZoom());
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

		google.maps.event.addListener(map, 'zoom_changed', updateZoom);

		search.on({
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
		init();
	}

	// Set the init method to re-run if the page is saved or pjaxed
	if($.entwine){
		$.entwine('ss', function($) {
			$('.googlemapfield').entwine({
				onmatch: function() {
					if(gmapsAPILoaded) {
						init();
					}
				}
			});
			$('.cms-tabset-nav-primary li').entwine({
				onclick: function() {
					if(gmapsAPILoaded) {
						init();
					}
				}
			});
			$('.ss-tabset li').entwine({
				onclick: function() {
					if(gmapsAPILoaded) {
						init();
					}
				}
			});
		});
	}

}(jQuery));
