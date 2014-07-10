<?php

/**
 * GoogleMapField
 * Lets you record a precise location using latitude/longitude fields to a
 * DataObject. Displays a map using the Google Maps API. The user may then
 * choose where to place the marker; the landing coordinates are then saved.
 * You can also search for locations using the search box, which uses the Google
 * Maps Geocoding API.
 * @author <@willmorgan>
 */
class GoogleMapField extends FormField {

	protected $data;

	/**
	 * @var FormField
	 */
	protected $latField;

	/**
	 * @var FormField
	 */
	protected $lngField;

	/**
	 * The merged version of the $defaults and the user specified options
	 * @var array
	 */
	protected $options = array();

	/**
	 * These are the defaults for __construct
	 */
	static protected $defaults = array(
		// lat and lng will map to the field names on your DataObject
		'fieldNames' => array(
			'lat' => 'Lat',
			'lng' => 'Lng',
		),
		'showSearchBox' => true,
		'apiKey' => null,
	);

	/**
	 * @var boolean
	 */
	static protected $js_inserted = false;

	/**
	 * @param DataObject $data The controlling dataobject
	 * @param string $title The title of the field
	 * @param array $options Various settings for the field
	 */
	public function __construct(DataObject $data, $title, $options = array()) {
		$this->data = $data;

		// Set up fieldnames
		$this->options = array_merge(self::$defaults, $options);

		$fieldNames = $this->getOption('fieldNames');

		// Auto generate a name
		$name = sprintf('%s_%s_%s', $data->class, $fieldNames['lat'], $fieldNames['lng']);

		// Create the latitude/longitude hidden fields
		$this->children = new FieldList(
			$this->latField = HiddenField::create($name . '[' . $fieldNames['lat'] . ']', 'Lat', $this->getLatData())->addExtraClass('googlemapfield-latfield'),
			$this->lngField = HiddenField::create($name . '[' . $fieldNames['lng'] . ']', 'Lng', $this->getLngData())->addExtraClass('googlemapfield-lngfield'),
			TextField::create('Search')
				->addExtraClass('googlemapfield-searchfield')
				->setAttribute('placeholder', 'Search for a location')
		);

		parent::__construct($name, $title);
	}

	/**
	 * @param array $properties
	 * @see https://developers.google.com/maps/documentation/javascript/reference
	 * {@inheritdoc}
	 */
	public function Field($properties = array()) {
		$key = $this->options['apiKey'] ? "&key=".$this->options['apiKey'] : "";
		Requirements::javascript(GOOGLEMAPFIELD_BASE .'/javascript/GoogleMapField.js');
		Requirements::javascript("//maps.googleapis.com/maps/api/js?callback=googlemapfieldInit".$key);
		Requirements::css(GOOGLEMAPFIELD_BASE .'/css/GoogleMapField.css');
		$jsOptions = array(
			'coords' => array($this->getLatData(), $this->getLngData()),
			'map' => array(
				'zoom' => 8,
				'mapTypeId' => 'ROADMAP',
			),
		);
		if(!$this->options['showSearchBox']){
			$this->children->removeByName("Search");
		}
		$jsOptions = array_replace_recursive($jsOptions, $this->options);
		$this->setAttribute('data-settings', Convert::array2json($jsOptions));

		return parent::Field($properties);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setValue($value) {
		$this->latField->setValue($value[$this->getLatField()]);
		$this->lngField->setValue($value[$this->getLngField()]);
		return $this;
	}

	/**
	 * Take the latitude/longitude fields and save them to the DataObject.
	 * {@inheritdoc}
	 */
	public function saveInto(DataObjectInterface $record) {
		$record->setCastedField($this->getLatField(), $this->latField->dataValue());
		$record->setCastedField($this->getLngField(), $this->lngField->dataValue());
		return $this;
	}

	/**
	 * @return FieldList The Latitude/Longitude fields
	 */
	public function getChildFields() {
		return $this->children;
	}

	/**
	 * @return string The NAME of the Latitude field
	 */
	public function getLatField() {
		$fieldNames = $this->getOption('fieldNames');
		return $fieldNames['lat'];
	}

	/**
	 * @return string The NAME of the Longitude field
	 */
	public function getLngField() {
		$fieldNames = $this->getOption('fieldNames');
		return $fieldNames['lng'];
	}

	/**
	 * @return string The VALUE of the Latitude field
	 */
	public function getLatData() {
		$fieldNames = $this->getOption('fieldNames');
		return $this->data->$fieldNames['lat'];
	}

	/**
	 * @return string The VALUE of the Longitude field
	 */
	public function getLngData() {
		$fieldNames = $this->getOption('fieldNames');
		return $this->data->$fieldNames['lng'];
	}

	/**
	 * Get the merged option that was set on __construct
	 * @param string $name The name of the option
	 * @return mixed
	 */
	public function getOption($name) {
		// Quicker execution path for "."-free names
		if (strpos($name, '.') === false) {
			if (isset($this->options[$name])) return $this->options[$name];
		} else {
			$names = explode('.', $name);

			$var = $this->options;

			foreach($names as $n) {
				if(!isset($var[$n])) {
					return null;
				}
				$var = $var[$n];
			}

			return $var;
		}
	}

	/**
	 * Set an option for this field
	 * @param string $name The name of the option to set
	 * @param mixed $val The value of said option
	 * @return $this
	 */
	public function setOption($name, $val) {
		// Quicker execution path for "."-free names
		if(strpos($name,'.') === false) {
			$this->options[$name] = $val;
		} else {
			$names = explode('.', $name);

			// We still want to do this even if we have strict path checking for legacy code
			$var = &$this->options;

			foreach($names as $n) {
				$var = &$var[$n];
			}

			$var = $val;
		}
		return $this;
	}

}
