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
		$this->children = new FieldList(
			$this->latField = HiddenField::create($name.'[Latitude]', 'Lat', $this->getLatData())->addExtraClass('googlemapfield-latfield'),
			$this->lngField = HiddenField::create($name.'[Longitude]','Lng', $this->getLngData())->addExtraClass('googlemapfield-lngfield'),
			TextField::create('Search')
				->addExtraClass('googlemapfield-searchfield')
				->setAttribute('placeholder', 'Search for a location')
		);

		parent::__construct($name, $title);

	}

	public function Field($properties = array()) {
		Requirements::javascript(GOOGLEMAPFIELD_BASE .'/javascript/GoogleMapField.js');
		Requirements::javascript('//maps.google.com/maps/api/js?sensor=false&callback=googlemapfieldInit');
		Requirements::css(GOOGLEMAPFIELD_BASE .'/css/GoogleMapField.css');
		if(!self::$js_inserted) {
			Requirements::insertHeadTags('<script>window.googlemapfieldOptions = {};</script>');
		}
		$jsOptions = array(
			'coords' => array($this->getLatData(), $this->getLngData()),
			'map' => array(
				'zoom' => 8,
				'mapTypeId' => 'ROADMAP',
			),
		);
		$jsOptions = array_merge($this->options, $jsOptions);
		Requirements::insertHeadTags('<script>googlemapfieldOptions[\''.$this->Name.'\'] = '.json_encode($jsOptions).';</script>');
		return parent::Field($properties);
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

}
