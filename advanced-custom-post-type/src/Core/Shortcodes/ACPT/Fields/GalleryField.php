<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\Wordpress\WPAttachment;

class GalleryField extends AbstractField
{
    public function render()
    {
        if(!$this->isFieldVisible()){
            return null;
        }

        $return = '';
        $this->enqueueAssets();

	    $rawData = $this->fetchRawData();

	    if(!isset($rawData['value'])){
		    return null;
	    }

	    if(!is_array($rawData['value'])){
		    return null;
	    }

	    $images = $rawData['value'];
	    $sort = $this->payload->sort ?? 'asc';
        $render = $this->metaBoxFieldModel->getAdvancedOption("render") ?? "html";
	    $elements = ($this->payload->elements !== null) ? $this->payload->elements : 1;
	    $return .= $this->renderGallery($images, $elements, $sort, $render);

        return $return;
    }

    /**
     * @param $images
     * @param $elements
     * @param $sort
     * @param $render
     *
     * @return string|null
     */
    private function renderGallery($images, $elements, $sort = 'asc', $render = "html")
    {
	    if(empty($images)){
	    	return null;
	    }

	    if(!is_array($images)){
		    return null;
	    }

	    if($sort === 'desc'){
            $images = array_reverse($images);
        }

        if($sort === 'rand'){
            shuffle($images);
        }

    	if($this->payload->preview){

		    $return = '<div style="display: flex; gap: 5px;">';

		    foreach ($images as $image){
			    $wpAttachment = WPAttachment::fromUrl($image);

			    if(!$wpAttachment->isEmpty()){
				    $return .= $this->addBeforeAndAfter($wpAttachment->render([
                        'style' => 'border: 1px solid #c3c4c7; object-fit: fill;',
                        'size' => 'thumbnail',
                        'w' => 80,
                        'h' => 60,
                    ]));
			    }
		    }

		    $return .= '</div>';

			return $return;
	    }

        // Render as list of IDs
        if($render === "id"){
            $ids = [];

            foreach ($images as $imageUrl){
                $wpAttachment = WPAttachment::fromUrl($imageUrl);

                if(!$wpAttachment->isEmpty()){
                    $ids[] = $wpAttachment->getId();
                }
            }

            return implode(", ", $ids);
        }

        // Render as list of URLs
        if($render === "url"){
            $urls = [];

            foreach ($images as $imageUrl){
                $wpAttachment = WPAttachment::fromUrl($imageUrl);

                if(!$wpAttachment->isEmpty()){
                    $urls[] = $wpAttachment->getSrc();
                }
            }

            return implode(", ", $urls);
        }

        // Render as HTML
	    $style = 'margin:auto;';
	    $style .= ($this->payload->width !== null) ? 'width: '. $this->payload->width .';' : 'width: 100%;';
	    $style .= ($this->payload->height !== null) ? 'height: '. $this->payload->height .';' : 'height: 350px;';

	    $width = ($this->payload->width !== null) ? $this->payload->width : '100%';
	    $height = ($this->payload->height !== null) ? $this->payload->height : null;

	    $return = '<div class="acpt-owl-carousel owl-carousel per-row-'.(int)$elements.'" data-carousel-options=\'{"items":'.(int)$elements.'}\'>';

	    foreach ($images as $image){

		    $wpAttachment = WPAttachment::fromUrl($image);

		    if(!$wpAttachment->isEmpty()){
			    $return .= '<div class="item" style="'.$style.'">';
			    $return .= $this->addBeforeAndAfter($wpAttachment->render([
                    'w' => $width,
                    'h' => $height,
                ]));
			    $return .= '</div>';
		    }
	    }

	    $return .= '</div>';

	    return $return;
    }

    /**
     * Enqueue necessary assets
     */
    public function enqueueAssets()
    {
        // enqueue assets when the component is rendered in Gutenberg
        add_action( 'enqueue_block_assets', function (){
            if(is_admin()){
                wp_enqueue_style( 'owl.carousel.min', plugins_url( 'advanced-custom-post-type/assets/vendor/owl.carousel/assets/owl.carousel.min.css'), array(), '2.2.0', 'all');
                wp_enqueue_style( 'owl.carousel.theme', plugins_url( 'advanced-custom-post-type/assets/vendor/owl.carousel/assets/owl.theme.default.min.css'), array(), '2.2.0', 'all');
                wp_enqueue_script( 'owl.carousel', plugins_url( 'advanced-custom-post-type/assets/vendor/owl.carousel/owl.carousel.min.js'), array ( 'jquery' ), '2.2.0', true);
                wp_enqueue_script( 'custom-owl.carousel', plugins_url( 'advanced-custom-post-type/assets/static/js/owl.carousel.js'), array ( 'jquery' ), '2.2.0', true);
            }
        });

        wp_enqueue_style( 'owl.carousel.min', plugins_url( 'advanced-custom-post-type/assets/vendor/owl.carousel/assets/owl.carousel.min.css'), array(), '2.2.0', 'all');
        wp_enqueue_style( 'owl.carousel.theme', plugins_url( 'advanced-custom-post-type/assets/vendor/owl.carousel/assets/owl.theme.default.min.css'), array(), '2.2.0', 'all');
        wp_enqueue_script( 'owl.carousel', plugins_url( 'advanced-custom-post-type/assets/vendor/owl.carousel/owl.carousel.min.js'), array ( 'jquery' ), '2.2.0', true);
        wp_enqueue_script( 'custom-owl.carousel', plugins_url( 'advanced-custom-post-type/assets/static/js/owl.carousel.js'), array ( 'jquery' ), '2.2.0', true);
    }
}