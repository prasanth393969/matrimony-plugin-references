<?php

namespace ACPT\Utils\PHP;

use ACPT\Core\Helper\Strings;
use ACPT\Utils\Wordpress\WPAttachment;

class ImageSlider
{
    /**
     * @param WPAttachment[] $attachments
     * @param $defaultPercent
     * @param $width
     * @param $height
     *
     * @return string|null
     */
    public static function render(array $attachments, $defaultPercent = 50, $width = null, $height = null)
    {
        if(count($attachments) !== 2){
            return null;
        }

        $id = Strings::generateRandomId();

        self::enqueueAssets();
        
        try {
            $slider = "<div class='acpt-image-slider' id='".$id."_slider' style='--position: ".$defaultPercent."%'>";
            $slider .= '<div class="image-container">';
            $slider .= '<img
                class="image-before slider-image"
                src="'.$attachments[0]->getSrc().'"
                alt="'.$attachments[0]->getAlt().'"
                width="'.$width.'"
                height="'.$height.'"
            />';
            $slider .= '<img
                class="image-after slider-image"
                src="'.$attachments[1]->getSrc().'"
                alt="'.$attachments[1]->getAlt().'"
                width="'.$width.'"
                height="'.$height.'"
              />';
            $slider .= "</div>";
            $slider .= '<input type="range" min="0" max="100" value="'.$defaultPercent.'" class="slider">';
            $slider .= '<div class="slider-line" aria-hidden="true"></div>';
            $slider .= '<div class="slider-button" aria-hidden="true">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="30"
                    height="30"
                    fill="currentColor"
                    viewBox="0 0 256 256"
                  >
                    <rect width="256" height="256" fill="none"></rect>
                    <line
                      x1="128"
                      y1="40"
                      x2="128"
                      y2="216"
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="16"
                    ></line>
                    <line
                      x1="96"
                      y1="128"
                      x2="16"
                      y2="128"
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="16"
                    ></line>
                    <polyline
                      points="48 160 16 128 48 96"
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="16"
                    ></polyline>
                    <line
                      x1="160"
                      y1="128"
                      x2="240"
                      y2="128"
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="16"
                    ></line>
                    <polyline
                      points="208 96 240 128 208 160"
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="16"
                    ></polyline>
                  </svg>
                </div>';
            $slider .= "</div>";

            return $slider;
        } catch (\Exception $exception){
            return null;
        }
    }

    private static function enqueueAssets()
    {
        // enqueue assets when Audio component is rendered in Gutenberg
        add_action( 'enqueue_block_assets', function (){
            if(is_admin()){
                wp_enqueue_script( 'custom-acpt-image-slider-js', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/image-slider.js' : 'advanced-custom-post-type/assets/static/js/image-slider.min.js'), ['jquery'], ACPT_PLUGIN_VERSION, true);
                wp_enqueue_style( 'custom-acpt-image-slider-css', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/image-slider.css' : 'advanced-custom-post-type/assets/static/css/image-slider.min.css'), [], ACPT_PLUGIN_VERSION, 'all');
            }
        });
        
        wp_enqueue_script( 'custom-acpt-image-slider-js', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/image-slider.js' : 'advanced-custom-post-type/assets/static/js/image-slider.min.js'), ['jquery'], ACPT_PLUGIN_VERSION, true);
        wp_enqueue_style( 'custom-acpt-image-slider-css', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/image-slider.css' : 'advanced-custom-post-type/assets/static/css/image-slider.min.css'), [], ACPT_PLUGIN_VERSION, 'all');
    }
}