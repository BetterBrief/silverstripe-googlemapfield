<?php

class GoogleMapField extends FormField {

	protected
		$data,
		$latField,
		$lngField,
		$options = array();

	protected static
		$defaults = array(
			'fieldNames' => array(
				'lat' => 'Lat',
				'lng' => 'Lng',
			),
		),
		$js_inserted = false;

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
		$this->children = new FieldSet(
			$this->latField = new HiddenField($name.'[Latitude]', 'Lat', $this->getLatData()),
			$this->lngField = new HiddenField($name.'[Longitude]','Lng', $this->getLngData()),
			$textField = new TextField('Search', 'Search for a location')
		);

		$this->latField->addExtraClass('googlemapfield-latfield');
		$this->lngField->addExtraClass('googlemapfield-lngfield');

		$textField->addExtraClass('googlemapfield-searchfield');

		parent::__construct($name, $title);

	}

	public function FieldHolder() {
		Requirements::css(GOOGLEMAPFIELD_BASE .'/css/GoogleMapField.css');
		Requirements::javascript(GOOGLEMAPFIELD_BASE .'/javascript/GoogleMapField.js');
		Requirements::javascript('https://google.com/maps/api/js?sensor=false&callback=googlemapfieldInit');

		return $this->renderWith('GoogleMapField', array(
			'SettingsJSON' => Convert::raw2att($this->getJSONOptions()),
		));
	}

	function setValue($value) {
		$this->latField->setValue($value[$this->getLatField()]);
		$this->lngField->setValue($value[$this->getLngField()]);
		return $this;
	}

	public function saveInto(DataObjectInterface $record) {
		$record->setCastedField($this->getLatField(), $this->latField->dataValue());
		$record->setCastedField($this->getLngField(), $this->lngField->dataValue());
	}

	public function getChildFields() {
		return $this->children;
	}

	public function getLatField() {
		$fieldNames = $this->getOption('fieldNames');
		return $fieldNames['lat'];
	}

	public function getLngField() {
		$fieldNames = $this->getOption('fieldNames');
		return $fieldNames['lng'];
	}

	public function getLatData() {
		$fieldNames = $this->getOption('fieldNames');
		return $this->data->$fieldNames['lat'];
	}

	public function getLngData() {
		$fieldNames = $this->getOption('fieldNames');
		return $this->data->$fieldNames['lng'];
	}

	public function getOption($option) {
		return $this->options[$option];
	}

	public function getJSONOptions() {
		$jsOptions = array(
			'coords' => array($this->getLatData(), $this->getLngData()),
			'map' => array(
				'zoom' => 8,
				'mapTypeId' => 'ROADMAP',
			),
		);
		return Convert::array2json(array_merge($jsOptions, $this->options));
	}

}
