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
			'showSearchBox' => true,
			'apikey' => null,
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
			$this->latField = HiddenField::create($name . '[' . $fieldNames['lat'] . ']', 'Lat', $this->getLatData())->addExtraClass('googlemapfield-latfield'),
			$this->lngField = HiddenField::create($name . '[' . $fieldNames['lng'] . ']', 'Lng', $this->getLngData())->addExtraClass('googlemapfield-lngfield'),
			TextField::create('Search')
				->addExtraClass('googlemapfield-searchfield')
				->setAttribute('placeholder', 'Search for a location')
		);

		parent::__construct($name, $title);
	}

	public function Field($properties = array()) {
		$key = $this->options['apikey'] ? "&key=".$this->options['apikey'] : "";
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

	function setValue($value) {
		$this->latField->setValue($value[$this->getLatField()]);
		$this->lngField->setValue($value[$this->getLngField()]);
		return $this;
	}

	public function saveInto(DataObjectInterface $record) {
		$record->setCastedField($this->getLatField(), $this->latField->dataValue());
		$record->setCastedField($this->getLngField(), $this->lngField->dataValue());
		return $this;
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
