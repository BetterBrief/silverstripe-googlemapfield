/**
 * GoogleMapField.js
 * @author <@willmorgan>
 */
(function($) {

	var gmapsAPILoaded = false;

	// Run this code for every googlemapfield
	function initField() {
		var field = $(this);
		if(field.data('gmapfield-inited') === true) {
			return;
		}
		field.data('gmapfield-inited', true);
		var settings = JSON.parse(field.attr('data-settings')),
			centre = new google.maps.LatLng(settings.coords[0], settings.coords[1]),
			mapSettings = {
				streetViewControl: false,
				zoom: settings.map.zoom * 1,
				center: centre,
				mapTypeId: google.maps.MapTypeId[settings.map.mapTypeId]
			},
			mapElement = field.find('.googlemapfield-map'),
			map = new google.maps.Map(mapElement[0], mapSettings),
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

			mapSettings.coords = [latCoord, lngCoord];

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

	$.fn.gmapfield = function() {
		return this.each(function() {
			initField.call(this);
		});
	}

	function init() {
		var mapFields = $('.googlemapfield:visible').gmapfield();
		mapFields.each(initField);
	}

	// Export the init function
	window.googlemapfieldInit = function() {
		gmapsAPILoaded = true;
		init();
	}

	// CMS stuff: set the init method to re-run if the page is saved or pjaxed
	// there are no docs for the CMS implementation of entwine

	if(!!$.fn.entwine && $(document.body).hasClass('cms')) {
		$.entwine('ss', function($){
			// init
		});
		$('.googlemapfield').entwine({
			onmatch: function() {
				console.log('match');
				initField();
				initField.call(this);
			},
			onunmatch: function() {
				console.log('unmatch');
			}
		});
	}

}(jQuery));
