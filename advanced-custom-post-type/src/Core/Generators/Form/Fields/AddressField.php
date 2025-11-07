<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Address;
use ACPT\Utils\PHP\Maps;

class AddressField extends AbstractField
{
	/**
	 * @return string
	 * @throws \Exception
	 */
	public function render()
	{
        $isMulti = $this->isMulti();

        if($isMulti){
            return $this->renderMultiAddress();
        }

        return $this->renderSingleAddress();
	}

    /**
     * @return bool
     */
    private function isMulti()
    {
        return $this->fieldModel->getMetaField() !== null ? $this->fieldModel->getMetaField()->getType() === MetaFieldModel::ADDRESS_MULTI_TYPE : false;
    }

    /**
     * @return string
     */
    private function renderSingleAddress()
    {
        $css = "";

        if(empty(Maps::googleMapsKey())){
            $css = "display: none;";
        }

        $field = "
			<input
			    ".$this->disabled()."
				id='".Strings::esc_attr($this->getIdName())."'
				name='".Strings::esc_attr($this->getIdName())."'
				placeholder='".$this->placeholder()."'
				value='".$this->defaultValue()."'
				type='text'
				style='".$css."'
				class='input-map ".$this->cssClass()."'
				".$this->required()."
				".$this->appendDataValidateAndConditionalRenderingAttributes()."
			/>
		";

        $field .= '<input type="hidden" id="'. Strings::esc_attr($this->getIdName()).'_lat" name="'. Strings::esc_attr($this->getIdName("lat")).'" value="'.$this->getLat().'">';
        $field .= '<input type="hidden" id="'. Strings::esc_attr($this->getIdName()).'_lng" name="'. Strings::esc_attr($this->getIdName("lng")).'" value="'.$this->getLng().'">';
        $field .= '<input type="hidden" id="'. Strings::esc_attr($this->getIdName()).'_city" name="'. Strings::esc_attr($this->getIdName("city")).'" value="'.$this->getCity().'">';
        $field .= '<input type="hidden" id="'. Strings::esc_attr($this->getIdName()).'_country" name="'. Strings::esc_attr($this->getIdName("country")).'" value="'.$this->getCountry().'">';
        $field .= '<div class="acpt_map_preview" id="'. Strings::esc_attr($this->getIdName()).'_map"></div>';

        return $field;
    }

    private function renderMultiAddress()
    {
        $id = Strings::generateRandomId();

        $field = "
			<input
			    ".$this->disabled()."
				id='".Strings::esc_attr($id)."'
				name='".Strings::esc_attr($this->getIdName())."'
				value='".$this->defaultValue()."'
				type='hidden'
				".$this->required()."
				".$this->appendDataValidateAndConditionalRenderingAttributes()."
			/>
		";

        $field .= '<input type="hidden" id="'. Strings::esc_attr($id).'_lat" name="'. Strings::esc_attr($this->getIdName("lat")).'" value="'.$this->getLat().'">';
        $field .= '<input type="hidden" id="'. Strings::esc_attr($id).'_lng" name="'. Strings::esc_attr($this->getIdName("lng")).'" value="'.$this->getLng().'">';
        $field .= '<input type="hidden" id="'. Strings::esc_attr($id).'_city" name="'. Strings::esc_attr($this->getIdName("city")).'" value="'.$this->getCity().'">';
        $field .= '<input type="hidden" id="'. Strings::esc_attr($id).'_country" name="'. Strings::esc_attr($this->getIdName("country")).'" value="'.$this->getCountry().'">';

        // only for Google Maps
        if(!empty(Maps::googleMapsKey())){
            $field .= '<input class="acpt-form-control acpt-input-map" id="'.$id.'_google_placeholder" />';
        }

        $field .= '<div class="acpt_map_multi_wrapper">';
        $field .= '<div id="'.$id.'_selections" class="acpt_map_multi_selections">';

        if(!empty($this->defaultValue())){
            $rawData = Address::fetchMulti($this->defaultValue());
            $latRawData = Address::fetchMulti($this->getLat());
            $lngRawData = Address::fetchMulti($this->getLng());

            if(!empty($rawData)){
                foreach ($rawData as $index => $datum){

                    if(empty(Maps::googleMapsKey())){
                        $active = $index === count($rawData)-1;
                    } else {
                        $active = $index === 0;
                    }

                    $field .= '<div class="selection '.($active ? "active" : "").'" data-index="'.$index.'" data-lat="'.$latRawData[$index].'" data-lng="'.$lngRawData[$index].'">';
                    $field .= '<span class="acpt_map_multi_selection">';
                    $field .= $datum;
                    $field .= '</span>';
                    $field .= '<a class="acpt_map_delete_multi_selection button button-danger">-</a>';
                    $field .= '</div>';
                }
            }
        }

        $field .= '</div>';
        $field .= '<div class="acpt_map_multi_preview loading" style="height: 450px;" id="'. $id.'_map"></div>';
        $field .= '</div>';

        return $field;
    }

    /**
     * @return string
     */
    private function getLat()
    {
        return $this->defaultExtraValue('lat') ?? "";
    }

    /**
     * @return string
     */
    private function getLng()
    {
        return $this->defaultExtraValue('lng') ?? "";
    }

    /**
     * @return string
     */
    private function getCity()
    {
        return $this->defaultExtraValue('city') ?? "";
    }

    /**
     * @return string
     */
    private function getCountry()
    {
        return $this->defaultExtraValue('country') ?? "";
    }

	/**
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function enqueueFieldAssets()
	{
		if(!empty(Maps::googleMapsKey())){
			wp_register_script('admin_google_maps_js',  plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/google-maps.js' : 'advanced-custom-post-type/assets/static/js/google-maps.min.js'), ['jquery'], ACPT_PLUGIN_VERSION );
			wp_enqueue_script('admin_google_maps_js');

			wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key='.Maps::googleMapsKey().'&libraries=places&callback=init', false, '3', true);
			wp_enqueue_script('google-maps');
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
