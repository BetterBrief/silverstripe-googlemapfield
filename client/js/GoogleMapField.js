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
			boundsField = field.find('.googlemapfield-boundsfield'),
			search = field.find('.googlemapfield-searchfield');

		// Update the hidden fields and mark as changed
		function updateField(latLng, init) {
			var latCoord = latLng.lat(),
				lngCoord = latLng.lng();

			mapSettings.coords = [latCoord, lngCoord];

			latField.val(latCoord);
			lngField.val(lngCoord);
			updateBounds(init);

			// Mark form as changed if this isn't initialisation
			if (!init) {
				$('.cms-edit-form').addClass('changed');
			}
		}

		function updateZoom(init) {
			zoomField.val(map.getZoom());
			// Mark form as changed if this isn't initialisation
			if (!init) {
				$('.cms-edit-form').addClass('changed');
			}
		}

		function updateBounds() {
			var bounds = JSON.stringify(map.getBounds().toJSON());
			boundsField.val(bounds);
		}

		function zoomChanged() {
			updateZoom();
			updateBounds();
		}

		function centreOnMarker() {
			var center = marker.getPosition();
			map.panTo(center);
			updateField(center);
		}

		function mapClicked(ev) {
			var center = ev.latLng;
			marker.setPosition(center);
			centreOnMarker();
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
		google.maps.event.addListenerOnce(map, 'idle', function(){
			updateField(map.getCenter(), true);
			updateZoom(init);
		});

		google.maps.event.addListener(marker, 'dragend', centreOnMarker);

		google.maps.event.addListener(map, 'click', mapClicked);

		google.maps.event.addListener(map, 'zoom_changed', zoomChanged);

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
	// there are no docs for the CMS implementation of entwine, so this is hacky
	if(!!$.fn.entwine && $(document.body).hasClass('cms')) {
		(function setupCMS() {
			var matchFunction = function() {
				if(gmapsAPILoaded) {
					init();
				}
			};
			$.entwine('googlemapfield', function($) {
				$('.cms-tabset').entwine({
					onmatch: matchFunction
				});
				$('.cms-tabset-nav-primary li').entwine({
					onclick: matchFunction
				});
				$('.ss-tabset li').entwine({
					onclick: matchFunction
				});
				$('.cms-edit-form').entwine({
					onmatch: matchFunction
				});
			});
		}());
	}

}(jQuery));
