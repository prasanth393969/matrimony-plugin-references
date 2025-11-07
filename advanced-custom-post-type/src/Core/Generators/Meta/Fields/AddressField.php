<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Maps;
use ACPT\Utils\Wordpress\Translator;

class AddressField extends AbstractField
{
	/**
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function render()
	{
		$this->enqueueAssets();
		$defaultPlaceholder = Translator::translate("Type the address or point a location on the map");
		$readonly = (empty(Maps::googleMapsKey())) ? 'readonly' : '';
		$cssClass = 'regular-text acpt-admin-meta-field-input acpt-input-map';

		$coords = $this->getDefaultAttributeValues();
		$lat = $coords['lat'];
		$lng = $coords['lng'];
		$city = $coords['city'];
		$country = $coords['country'];

		$defaultLat = ($lat !== null and $lat !== '' ) ? $lat : '';
		$defaultLng = ($lng !== null and $lng !== '' ) ? $lng : '';
		$defaultCity = ($city !== null and $city !== '' ) ? $city : '';
		$defaultCountry = ($country !== null and $country !== '' ) ? $country : '';

		if($this->isChild() or $this->isNestedInABlock()){

			if($this->isLeadingField()){
				$cssClass .= ' acpt-leading-field';
			}

			$id = "address_".Strings::generateRandomId();

			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::ADDRESS_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$field .= '<input type="hidden" name="meta_fields[]" value="'. Strings::esc_attr($this->getIdName()).'[lat]">';
			$field .= '<input type="hidden" name="meta_fields[]" value="'. Strings::esc_attr($this->getIdName()).'[lng]">';
			$field .= '<input type="hidden" id="'. $id . '_lat" name="'. Strings::esc_attr($this->getIdName()).'[lat]" value="'.Strings::esc_attr($defaultLat).'">';
			$field .= '<input type="hidden" id="'. $id . '_lng" name="'. Strings::esc_attr($this->getIdName()).'[lng]" value="'.Strings::esc_attr($defaultLng).'">';
			$field .= '<input type="hidden" id="'. $id . '_city" name="'. Strings::esc_attr($this->getIdName()).'[city]" value="'.Strings::esc_attr($defaultCity).'">';
			$field .= '<input type="hidden" id="'. $id . '_country" name="'. Strings::esc_attr($this->getIdName()).'[country]" value="'.Strings::esc_attr($defaultCountry).'">';
			$field .= '<input '.$this->required().' id="'.$id.'" name="'. Strings::esc_attr($this->getIdName()).'[value]" '.$readonly.' type="text" class="'.$cssClass.'" style="margin-bottom: 10px;" value="' .Strings::esc_attr($this->getDefaultValue()) .'" placeholder="'.$defaultPlaceholder.'" '.$this->appendDataValidateAndLogicAttributes().'>';
		} else {
			$id = Strings::esc_attr($this->getIdName());

			if(empty(Maps::googleMapsKey())){
				$cssClass .= ' hidden';
			}

			$field = '<input type="hidden" name="'. $id.'_type" value="'.MetaFieldModel::ADDRESS_TYPE.'">';
			$field .= '<input type="hidden" id="'. $id.'_lat" name="'. Strings::esc_attr($this->getIdName()).'_lat" value="'.Strings::esc_attr($defaultLat).'">';
			$field .= '<input type="hidden" id="'. $id.'_lng" name="'. Strings::esc_attr($this->getIdName()).'_lng" value="'.Strings::esc_attr($defaultLng).'">';
			$field .= '<input type="hidden" id="'. $id.'_city" name="'. Strings::esc_attr($this->getIdName()).'_city" value="'.Strings::esc_attr($defaultCity).'">';
			$field .= '<input type="hidden" id="'. $id.'_country" name="'. Strings::esc_attr($this->getIdName()).'_country" value="'.Strings::esc_attr($defaultCountry).'">';
			$field .= '<input '.$this->required().' id="'.$id.'" name="'. $id .'" '.$readonly.' type="text" class="'.$cssClass.'" style="margin-bottom: 10px;" value="' .Strings::esc_attr($this->getDefaultValue()) .'" placeholder="'.$defaultPlaceholder.'" '.$this->appendDataValidateAndLogicAttributes().'>';
		}

		$field .= $this->renderMap($id);

		return $this->renderField($field);
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	private function renderMap($id)
	{
		return '
			<div class="acpt_map_preview loading" style="height: 450px;" id="'. $id.'_map"></div>
			<a class="acpt-reset-map" href="#">'.Translator::translate("Reset the map").'</a>
		';
	}

	/**
	 * @return array
	 */
	protected function getDefaultAttributeValues()
	{
		return [
			'lat' => $this->getDefaultAttributeValue('lat', null),
			'lng' => $this->getDefaultAttributeValue('lng', null),
			'city' => $this->getDefaultAttributeValue('city', null),
			'country' => $this->getDefaultAttributeValue('country', null),
		];
	}

	/**
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function enqueueAssets()
	{
		if(!empty(Maps::googleMapsKey())){

			// use Google Maps
			wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key='.Maps::googleMapsKey().'&libraries=places&callback=init', false, '3', true);
			wp_enqueue_script('google-maps');

			wp_register_script('admin_google_maps_js',  plugins_url(ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/google-maps.js' : 'advanced-custom-post-type/assets/static/js/google-maps.min.js'), ['jquery'], ACPT_PLUGIN_VERSION );
			wp_enqueue_script('admin_google_maps_js');
		} else {
			// use Leaflet
			wp_enqueue_script( 'leaflet-js', plugins_url( 'advanced-custom-post-type/assets/vendor/leaflet/leaflet.min.js'), [], '1.9.4', true);
			wp_enqueue_script( 'leaflet-geosearch-js', plugins_url( 'advanced-custom-post-type/assets/vendor/leaflet/geosearch.bundle.min.js'), [], '4.0.0', true);
			wp_enqueue_style( 'leaflet-css', plugins_url( 'advanced-custom-post-type/assets/vendor/leaflet/leaflet.min.css'), [], '1.9.4', 'all');
			wp_enqueue_style( 'leaflet-geosearch-css', plugins_url( 'advanced-custom-post-type/assets/vendor/leaflet/geosearch.min.css'), [], '1.9.4', 'all');
			wp_enqueue_script( 'custom-leaflet-js', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/leaflet.js' : 'advanced-custom-post-type/assets/static/js/leaflet.min.js'), [], '1.0.0', true);
		}
	}
}