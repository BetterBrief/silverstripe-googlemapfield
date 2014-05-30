silverstripe-googlemapfield
==============

Lets you record a precise location using latitude/longitude fields to a DataObject.

Displays a map using the Google Maps API. The user may then choose where to place the marker; the landing coordinates are then saved.

You can also search for locations using the search box, which uses the Google Maps Geocoding API.

Use the 2.4 branch for SilverStripe 2.4.


### Usage

##### `__construct` options

|Option|Default|Description|
|------|-------|-----------|
|`fieldNames`|See `GoogleMapField::$defaults`|A map of what your object's latitude/longitude fields are. Defaults to `Lat` for `lat` and `Lng` for `lng`.|

##### `Field` options

|Option|Default|Description|
|------|-------|-----------|
|`coords`|Your object's latitude and longitude|The intial coordinates of the map - note: this is not the default value if no object exists|
|`map`|Zoom of 8, map type of ROADMAP|A [google.maps.MapOptions](https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapOptions) object|
