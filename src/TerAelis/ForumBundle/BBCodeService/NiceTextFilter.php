<?php
namespace TerAelis\ForumBundle\BBCodeService;

use Decoda\Decoda;
use Decoda\Filter\AbstractFilter;

/**
 * Provides tags for text and font styling.
 */
class NiceTextFilter extends AbstractFilter {

    /**
     * Supported tags.
     *
     * @type array
     */
    protected $_tags = array(
        'indent' => array(
            'tag' => 'indent',
            'htmlTag' => 'div',
            'displayType' => Decoda::TYPE_BLOCK,
            'allowedTypes' => Decoda::TYPE_BOTH,
            'htmlAttributes' => array(
                'style' => 'text-indent: 2em;'
            ),
        ),
        'margin' => array(
            'tag' => 'margin',
            'htmlTag' => 'div',
            'displayType' => Decoda::TYPE_BLOCK,
            'allowedTypes' => Decoda::TYPE_BOTH,
            'attributes' => array(
                'default' => array('/^[0-9]{2}$/', 'margin: {default}px'),
            ),
            'mapAttributes' => array(
                'default' => 'style'
            )
        ),
        'maj' => array(
            'tag' => 'maj',
            'htmlTag' => 'span',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE,
            'htmlAttributes' => array(
                'style' => 'font-variant:small-caps;'
            ),
        ),
    );
}