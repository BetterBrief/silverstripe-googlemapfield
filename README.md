silverstripe-googlemapfield
==============

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/BetterBrief/silverstripe-googlemapfield/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/BetterBrief/silverstripe-googlemapfield/?branch=master)

Lets you record a precise location using latitude/longitude/zoom fields to a DataObject.

Displays a map using the Google Maps API. The user may then choose where to place the marker; the landing coordinates are then saved.

You can also search for locations using the search box, which uses the Google Maps Geocoding API.

Supports SilverStripe 3.1

## Usage

### Minimal configuration

Given your DataObject uses the field names `Latitude` and `Longitude` for storing the latitude and longitude
respectively then the following is a minimal setup to have the map show in the CMS:

```php
class Store extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)',
        'Latitude' => 'Varchar',
        'Longitude' => 'Varchar',
    );
    
    public function getCMSFields() {
        $fields = parent::getCMSFiels();
        
        // add the map field
        $fields->addFieldToTab('Root.Main', new GoogleMapField(
            $this,
            'Location'
        ));
        
        // remove the lat / lng fields from the CMS
        $fields->removeFieldFromTab('Root.Main', 'Latitude');
        $fields->removeFieldFromTab('Root.Main', 'Longitude');
        
        return $fields;
    }
}
```

Remember to set your API key in your site's `config.yml`

```yml
GoogleMapField:
  default_options:
    api_key: '[google-api-key]'
```

## Optional configuration

### Configuration options

You can either set the default options in your yaml file (see [_config/googlemapfield.yml](_config/googlemapfield.yml)
for a complete list) or at run time on each instance of the `GoogleMapField` object.

#### Setting at run time

To set options at run time pass through an array of options (3rd construct parameter):

```php
$field = new GoogleMapField(
    $dataObject,
    'FieldName',
    array(
        'api_key' => 'my-api-key',
        'show_search_box' => false,
        'map' => array(
            'zoom' => 10,
        ),
        ...
    )
);
```

#### Customising the map appearance

You can customise the map's appearance by passing through settings into the `map` key of the `$options` (shown above).
The `map` settings take a literal representation of the [google.maps.MapOptions](https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapOptions)

For example if we wanted to change the map type from a road map to satellite imagery we could do the following:

```php
$field = new GoogleMapField(
    $object,
    'Location',
    array(
        'map' => array(
            'mapTypeId' => 'SATELLITE',
        ),
    )
);
```

# Getting an API key

## Google Maps API key

To get a Google Maps JS API key please see [the official docs](https://developers.google.com/maps/documentation/javascript/get-api-key)

## Geocoding access - enabling the search box

To use the search box to find locations on the map, you'll need to have enabled the Geocoding API as well. Please see
[the official docs](https://developers.google.com/maps/documentation/javascript/geocoding#GetStarted)