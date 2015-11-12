<?php
namespace TerAelis\ForumBundle\BBCodeService;

use Decoda\Decoda;
use Decoda\Filter\AbstractFilter;

/**
 * Provides tags for basic font styling.
 */
class AncreFilter extends AbstractFilter {
    /**
     * Supported tags.
     *
     * @type array
     */
    protected $_tags = array(
        'toancre' => array(
            'tag' => 'toancre',
            'template' => '',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE,
            'attributes' => array(
                'default' => true
            ),
            'mapAttributes' => array(
                'default' => 'id'
            )
        ),
        'ancre' => array(
            'tag' => 'ancre',
            'template' => '',
            'displayType' => Decoda::TYPE_BLOCK,
            'allowedTypes' => Decoda::TYPE_NONE,
            'contentPattern' => '/^\w+$/is',
        ),
    );

    /**
     * Custom build the HTML for videos.
     *
     * @param array $tag
     * @param string $content
     * @return string
     */
    public function parse(array $tag, $content) {
        $setup = $this->getTag($tag['tag']);

        switch($tag['tag']) {
            case 'ancre':
                $id = $content;
                break;
            case 'toancre':
                $id = isset($tag['attributes']['id']) ? $tag['attributes']['id'] : '';
                $content = !empty($tag['content']) ? $tag['content'] : $content;
                break;
        }

        // Test for an empty filter or empty tag
        if (!$setup || empty($id)) {
            return null;
        }

        switch($tag['tag']) {
            case 'ancre':
                return '<span id="'.$id.'"></span>';
                break;
            case 'toancre':
                return '<a href="#'.$id.'">'.$content.'</a>';
                break;
        }
        return null;
    }

}