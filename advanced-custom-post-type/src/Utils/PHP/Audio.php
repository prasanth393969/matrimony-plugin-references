<?php

namespace ACPT\Utils\PHP;

use ACPT\Utils\Wordpress\WPAttachment;

class Audio
{
    /**
     * @param WPAttachment[] $attachments
     * @param bool $customPlayer
     * @param string $style
     * @param bool $disableCover
     * @return string|null
     */
    public static function playlist($attachments = [], $customPlayer = false, $style = 'light', $disableCover = false)
    {
        if(empty($attachments)){
            return null;
        }

        if(!$customPlayer){
            $ids = [];

            foreach ($attachments as $attachment){
                $ids[] = $attachment->getId();
            }

            return do_shortcode('[playlist type="audio" style="'.$style.'" ids="'.implode(",", $ids).'"]');
        }

        self::enqueueAssets();

        $playlist = "<div class='acpt-custom-audio-playlist-wrapper ".$style."' data-theme='".$style."'>";

        // player
        $playlist .= self::single($attachments[0], true, $style, true, $disableCover);

        // playlist controls
        $playlist .= "<div class='playlist-controls'>";
        $playlist .= "<div class='shuffle-autoplay'>";
        $playlist .= "<button class='shuffle'></button>";
        $playlist .= "<button class='autoplay'></button>";
        $playlist .= "</div>";
        $playlist .= "<button class='toggle'></button>";
        $playlist .= "</div>";

        // list
        $playlist .= "<ul class='acpt-audio-playlist'>";

        foreach ($attachments as $index => $attachment){

            $src = esc_url($attachment->getSrc());
            $title = $attachment->getTitle() ?? 'Unknown title';
            $album = (isset($attachment->getMetadata()['album']) and !empty($attachment->getMetadata()['album'])) ? $attachment->getMetadata()['album'] :  'Unknown album';
            $artist = (isset($attachment->getMetadata()['artist']) and !empty($attachment->getMetadata()['artist'])) ? $attachment->getMetadata()['artist'] :  'Unknown artist';
            $thumbnailUrl = self:: getThumbnailURL($attachment);

            $playlist .= "<li id='".$attachment->getId()."' data-thumbnail='".$thumbnailUrl."' data-src='".$src."' data-title='".$title."' data-album='".$album."' data-artist='".$artist."' class='".($index === 0 ? "active" : "")."'>";
            $playlist .= "<div class='meta'>";
            $playlist .= "<span class='title'>".$title."</span>";

            if(!empty($artist)){
                $playlist .= '<span class="artist">'.$artist.'</span>';
            }

            if(!empty($album)){
                $playlist .= '<span class="album">- '.$album.'</span>';
            }

            $playlist .= "</div>";
            $playlist .= "<div class='length'>".$attachment->getMetadata()['length_formatted']."</div>";
            $playlist .= "</li>";
        }

        $playlist .= "</ul>";
        $playlist .= "</div>";

        return $playlist;
    }

    /**
     * @param WPAttachment $attachment
     * @param bool $customPlayer
     * @param string $style
     * @param bool $inPlaylist
     * @param bool $disableCover
     * @return string
     */
    public static function single(WPAttachment $attachment, $customPlayer = false, $style = 'light', $inPlaylist = false, $disableCover = false)
    {
        if(!$customPlayer){
            return do_shortcode('[audio style="'.$style.'" src="'.$attachment->getSrc().'"]');
        }

        self::enqueueAssets();

        $src = esc_url($attachment->getSrc());
        $title = $attachment->getTitle() ?? 'Unknown title';
        $album = (isset($attachment->getMetadata()['album']) and !empty($attachment->getMetadata()['album'])) ? $attachment->getMetadata()['album'] :  'Unknown album';
        $artist = (isset($attachment->getMetadata()['artist']) and !empty($attachment->getMetadata()['artist'])) ? $attachment->getMetadata()['artist'] :  'Unknown artist';

        $player = "<div id='".$attachment->getId()."' class='acpt-custom-audio-player-wrapper ".$style."' data-theme='".$style."'>";
        $player .= "<div class='meta-wrapper'>";

        if($disableCover !== true){
            $player .= self::getThumbnail($attachment);
        }

        $player .= "<div class='meta'>";
        $player .= "<h4>".$title."</h4>";

        if(!empty($artist)){
            $player .= '<span class="artist">'.$artist.'</span>';
        }

        if(!empty($album)){
            $player .= '<span class="divider">-</span>';
            $player .= '<span class="album">'.$album.'</span>';
        }

        $player .= "</div>";
        $player .= "</div>";
        $player .= "<div class='loading'>Loading...</div>";
        $player .= "<div class='wave' data-src='".$src."'></div>";
        $player .= "<div class='controls' style='display: none'>";
        $player .= "<span class='timer'></span>";
        $player .= "<div class='buttons'>";

        if($inPlaylist){
            $player .= "<button class='prev'></button>";
        }

        $player .= "<button class='rw'></button>";
        $player .= "<button class='play'></button>";
        $player .= "<button class='ff'></button>";

        if($inPlaylist){
            $player .= "<button class='next'></button>";
        }

        $player .= "</div>";
        $player .= "<div class='volumes'>";
        $player .= "<button class='mute'></button>";
        $player .= "<input type='range' value='1' min='0' max='1' step='0.01' class='volume'/>";
        $player .= "</div>";
        $player .= "</div>";
        $player .= "</div>";

        return $player;
    }

    /**
     * @param WPAttachment $attachment
     * @return string
     */
    private static function getThumbnail(WPAttachment $attachment)
    {
        if(!empty($attachment->getThumbnail())){
            return $attachment->getThumbnail()->render(['class' => 'thumbnail']);
        }

        return "<img src='".includes_url("images/media/audio.svg")."' alt='".$attachment->getAlt()."' title='".$attachment->getTitle()."' class='thumbnail'>";
    }

    /**
     * @param WPAttachment $attachment
     *
     * @return string
     */
    private static function getThumbnailURL(WPAttachment $attachment)
    {
        return (!empty($attachment->getThumbnail())) ? $attachment->getThumbnail()->getSrc() : includes_url("images/media/audio.svg");
    }

    private static function enqueueAssets()
    {
        // enqueue assets when Audio component is rendered in Gutenberg
        add_action( 'enqueue_block_assets', function (){
            if(is_admin()){
                wp_enqueue_script( 'wavesurfer-js', plugins_url( 'advanced-custom-post-type/assets/vendor/wavesurfer/wavesurfer.min.js'), [], '7.9.4', true);
                wp_enqueue_script( 'custom-acpt-audio-player-js', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/audio-player.js' : 'advanced-custom-post-type/assets/static/js/audio-player.min.js'), [], ACPT_PLUGIN_VERSION, true);
                wp_enqueue_style( 'custom-acpt-audio-player-css', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/audio-player.css' : 'advanced-custom-post-type/assets/static/css/audio-player.min.css'), [], ACPT_PLUGIN_VERSION, 'all');
            }
        });

        wp_enqueue_script( 'wavesurfer-js', plugins_url( 'advanced-custom-post-type/assets/vendor/wavesurfer/wavesurfer.min.js'), [], '7.9.4', true);
        wp_enqueue_script( 'custom-acpt-audio-player-js', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/audio-player.js' : 'advanced-custom-post-type/assets/static/js/audio-player.min.js'), [], ACPT_PLUGIN_VERSION, true);
        wp_enqueue_style( 'custom-acpt-audio-player-css', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/audio-player.css' : 'advanced-custom-post-type/assets/static/css/audio-player.min.css'), [], ACPT_PLUGIN_VERSION, 'all');
    }
}