<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Utils\Wordpress\WPAttachment;

class FileField extends AbstractField
{
    /**
     * @inheritDoc
     */
    protected function options(): array
    {
        return [
            'render' => [
                'type'    => 'select',
                'label'   => __( 'Render as', ACPT_PLUGIN_NAME ),
                'default' => 'url',
                'options' => $this->mediaRenderOptions(static::class),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function render($rawValue, $options = [])
    {
        if(!is_array($rawValue)){
            return null;
        }

        if(empty($rawValue['value'])){
            return null;
        }

        $value = $rawValue['value'];
        $before = $rawValue['before'];
        $after = $rawValue['after'];

        if(!is_numeric($value)){
            return null;
        }

        $attachment = WPAttachment::fromId($value);

        if($attachment->isEmpty()){
            return null;
        }

        $render = $options['render'] ?? "src";

        switch ($render){
            case "id":
                return $attachment->getId();

            case 'title':
                return $attachment->getTitle();

            case 'alt':
                return $attachment->getAlt();

            case 'caption':
                return $attachment->getCaption();

            case 'description':
                return $attachment->getDescription();

            case 'artist':
                return (isset($attachment->getMetadata()['artist']) and !empty($attachment->getMetadata()['artist'])) ? $attachment->getMetadata()['artist'] :  null;

            case 'album':
                return (isset($attachment->getMetadata()['album']) and !empty($attachment->getMetadata()['album'])) ? $attachment->getMetadata()['album'] :  null;

            default:
                return $attachment->getSrc();
        }
    }
}