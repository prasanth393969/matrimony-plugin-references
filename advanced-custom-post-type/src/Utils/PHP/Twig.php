<?php

namespace ACPT\Utils\PHP;

use ACPT\Core\Repository\MetaRepository;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

class Twig
{
    /**
     * Render a twig string, used in dynamic blocks.
     *
     * In case of exception, an empty string is returned
     *
     * @param string $string
     * @param array $attributes
     * @return string
     */
    public static function render($string, $attributes = [])
    {
        if(empty($string)){
            return '';
        }

        try {
            $loader = new ArrayLoader([
                'string' => $string,
            ]);

            $twig = new Environment($loader);
            self::injectWordpressFunctions($twig);
            self::injectACPTVariables($attributes);

            return $twig->render('string', $attributes);
        } catch (\Exception $exception){
            return '';
        }
    }

    /**
     * @param $string
     * @param array $attributes
     * @return string|null
     */
    public static function checkErrors($string, $attributes = [])
    {
        if(empty($string)){
            return null;
        }

        try {
            $loader = new ArrayLoader([
                'string' => $string,
            ]);

            $twig = new Environment($loader);
            self::injectWordpressFunctions($twig);
            self::injectACPTVariables($attributes);

            $twig->render('string', $attributes);

            return null;
        } catch (\Exception $exception){
            return $exception->getMessage();
        }
    }

    /**
     * Inject Wordpress functions
     *
     * @param Environment $twig
     */
    private static function injectWordpressFunctions(Environment $twig)
    {
        // Wordpress most useful functions
        $functions = [
            'get_permalink' => function($id = null){
                if($id){
                    return get_permalink($id);
                }

                return get_permalink();
            },
            'get_post_meta' => function($post_id, $key = '', $single = false){
                return get_post_meta( $post_id, $key, $single );
            },
            'get_posts' => function(array $args = []){
                return get_posts( $args );
            },
            'get_the_content' => function($more_link_text = null, $strip_teaser = false, $post = null){
                return get_the_content($more_link_text, $strip_teaser, $post);
            },
            'get_the_excerpt' => function($post = null){
                return get_the_excerpt($post);
            },
            'get_the_title' => function($id = null){
                if($id){
                    return get_the_title($id);
                }

                return get_the_title();
            },
            'have_posts' => function(){
                return have_posts();
            },
            'the_post' => function(){
                return the_post();
            },
            'do_shortcode' => function($content, $ignore_html = false){
                return do_shortcode($content, $ignore_html);
            },
        ];

        foreach ($functions as $name => $callable){
            if(is_callable($callable)){
                $function = new TwigFunction($name, $callable);
                $twig->addFunction($function);
            }
        }
    }

    /**
     * @param $attributes
     */
    private static function injectACPTVariables(&$attributes)
    {
        try {
            global $post;

            if(empty($post)){
                return;
            }

            $groups = MetaRepository::get([]);

            foreach ($groups as $group){
                foreach ($group->getBoxes() as $box){
                    foreach ($box->getFields() as $field){
                        $key = 'acpt_'.$box->getName().'_'.$field->getName();
                                $attributes[$key] = get_acpt_field([
                            'post_id' => $post->ID,
                            'box_name' => $box->getName(),
                            'field_name' => $field->getName(),
                        ]);
                    }
                }
            }

        } catch (\Exception $exception){}
    }
}