<?php

namespace ACPT\Integrations\Elementor;

use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\Elementor\Controls\CssControl;
use ACPT\Integrations\Elementor\Controls\DateFormatControl;
use ACPT\Integrations\Elementor\Controls\ElementsControl;
use ACPT\Integrations\Elementor\Controls\HeightControl;
use ACPT\Integrations\Elementor\Controls\RenderControl;
use ACPT\Integrations\Elementor\Controls\ShortcodeControl;
use ACPT\Integrations\Elementor\Controls\TargetControl;
use ACPT\Integrations\Elementor\Controls\WidthControl;
use ACPT\Integrations\Elementor\Controls\WrapperControl;
use ACPT\Integrations\Elementor\Widgets\WidgetGenerator;
use Elementor\Widgets_Manager;

class ACPT_Elementor extends AbstractIntegration
{
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "elementor";
    }

    /**
     * @inheritDoc
     */
    protected function isActive()
    {
	    $isActive = is_plugin_active( 'elementor/elementor.php' );

	    if(!$isActive){
		    return false;
	    }

        return ACPT_ENABLE_META and $isActive;
    }

    /**
     * @inheritDoc
     */
    protected function runIntegration()
    {
        if($this->checkIfElementorVersionIsCompatible()){
            add_action( 'elementor/frontend/before_enqueue_styles', [$this, 'enqueueScript'] );
            add_action( 'elementor/controls/register', [$this, 'registerElementorControls'] );
            add_action( 'elementor/elements/categories_registered', [$this, 'addElementorWidgetCategory'] );
            add_action( 'elementor/widgets/register', [$this, 'registerElementorWidgets'] );
        }
    }

    /**
     * Enqueue scripts
     */
    public function enqueueScript()
    {
        // for Gallery field
        wp_register_style( 'gallery-css', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/gallery.css' : 'advanced-custom-post-type/assets/static/css/gallery.min.css'), [], ACPT_PLUGIN_VERSION );
        wp_enqueue_style( 'gallery-css' );

        // for Audio fields
        wp_enqueue_script( 'wavesurfer-js', plugins_url( 'advanced-custom-post-type/assets/vendor/wavesurfer/wavesurfer.min.js'), [], '7.9.4', true);
        wp_enqueue_script( 'custom-acpt-audio-player-js', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/audio-player.js' : 'advanced-custom-post-type/assets/static/js/audio-player.min.js'), [], ACPT_PLUGIN_VERSION, true);
        wp_enqueue_style( 'custom-acpt-audio-player-css', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/audio-player.css' : 'advanced-custom-post-type/assets/static/css/audio-player.min.css'), [], ACPT_PLUGIN_VERSION, 'all');
    }

    /**
     * Register Elementor controls
     *
     * @param $controls_manager
     */
    public function registerElementorControls( $controls_manager )
    {
	    $controls_manager->register( new ShortcodeControl() );
    }

    /**
     * Add ACPT category to Elementor
     *
     * @param $elements_manager
     */
    public function addElementorWidgetCategory( $elements_manager )
    {
        $elements_manager->add_category(
            'acpt',
            [
                'title' => esc_html__( 'ACPT', ACPT_PLUGIN_NAME ),
                'icon' => 'fa fa-plug',
            ]
        );
    }

    /**
     * https://github.com/wpacademy/wpac-material-cards-elementor
     *
     * @param Widgets_Manager $widgetsManager
     * @throws \Exception
     * @since 1.0.3
     */
    public function registerElementorWidgets(Widgets_Manager $widgetsManager)
    {
    	try {
            $fieldGroups = MetaRepository::get([
                'clonedFields' => true
            ]);

            foreach ($fieldGroups as $fieldGroup){
                if(count($fieldGroup->getBelongs()) > 0){
                    foreach ($fieldGroup->getBelongs() as $belong){
                        $this->registerFields($fieldGroup, $widgetsManager, $belong);
                    }
                }
            }
	    } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }
    }

    /**
     * @param MetaGroupModel $metaGroup
     * @param Widgets_Manager $widgetsManager
     * @param BelongModel $belong
     * @throws \Exception
     */
    private function registerFields(MetaGroupModel $metaGroup, Widgets_Manager $widgetsManager, BelongModel $belong)
    {
        foreach ($metaGroup->getBoxes() as $metaBox){
            foreach ($metaBox->getFields() as $boxFieldModel){

                $notAllowedTypes = [
                    MetaFieldModel::CLONE_TYPE
                ];

                if(!in_array($boxFieldModel->getType(), $notAllowedTypes)){
                    $boxFieldModel->setBelongsAndFindLabels($belong);
                    $widgetsManager->register( new WidgetGenerator([], [
                        'boxFieldModel' => $boxFieldModel,
                        'find' => $boxFieldModel->getFindLabel(),
                    ]));
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function checkIfElementorVersionIsCompatible()
    {
        $elementorPlugin = __DIR__.'/../../../../elementor/elementor.php';

        if( !file_exists($elementorPlugin) ){
            return false;
        }

        $pluginData = get_plugin_data( $elementorPlugin );

        return version_compare( $pluginData['Version'], self::MINIMUM_ELEMENTOR_VERSION, '>=' );
    }
}
