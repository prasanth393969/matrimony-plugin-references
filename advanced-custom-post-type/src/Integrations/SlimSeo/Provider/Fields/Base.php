<?php

namespace ACPT\Integrations\SlimSeo\Provider\Fields;

class Base
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var null
     */
    protected $before;

    /**
     * @var null
     */
    protected $after;

    /**
     * Base constructor.
     *
     * @param      $value
     * @param null $before
     * @param null $after
     */
    public function __construct($value, $before = null, $after = null)
    {
        $this->value = $value;
        $this->before = $before;
        $this->after = $after;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        if(!is_string($this->value)){
            return null;
        }

        return wp_strip_all_tags( $this->before . $this->value . $this->after, true );
    }
}
