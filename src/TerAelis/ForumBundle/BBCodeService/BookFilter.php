<?php
namespace TerAelis\ForumBundle\BBCodeService;

use Decoda\Decoda;
use Decoda\Filter\AbstractFilter;

/**
 * Provides tags for basic font styling.
 */
class BookFilter extends AbstractFilter {
    /**
     * Supported tags.
     *
     * @type array
     */
    protected $_tags = array(
        'book' => array(
            'template' => 'book',
            'displayType' => Decoda::TYPE_BLOCK,
            'allowedTypes' => Decoda::TYPE_BOTH,
            'attributes' => array(
                'default' => '/^[\w,]+$/i',
            ),
            'mapAttributes' => array(
                'default' => 'styles'
            ),
        )
    );
}