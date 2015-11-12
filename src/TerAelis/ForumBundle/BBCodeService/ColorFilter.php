<?php
namespace TerAelis\ForumBundle\BBCodeService;

use Decoda\Decoda;
use Decoda\Filter\AbstractFilter;

/**
 * Provides tags for basic font styling.
 */
class ColorFilter extends AbstractFilter {
    /**
     * Supported tags.
     *
     * @type array
     */
    protected $_tags = array(
        'color' => array(
            'tag' => 'color',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE,
            'attributes' => array(
                'default' => true
            ),
            'mapAttributes' => array(
                'default' => 'color'
            )
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
        $color = isset($tag['attributes']['color']) ? $tag['attributes']['color'] : $content;
        $setup = $this->getTag($tag['tag']);
        $parser = $this->getParser();
        $content = !empty($tag['content']) ? $tag['content'] : $content;

        // Test for an empty filter or empty tag
        if (!$setup || (!$content && $parser->getConfig('removeEmpty'))) {
            return null;
        }

        return '<span style="color: '.$color.'">'.$content.'</span>';
    }

}