<?php
namespace TerAelis\ForumBundle\BBCodeService;

use Decoda\Decoda;
use Decoda\Filter\AbstractFilter;

/**
 * Provides tags for basic font styling.
 */
class IframeFilter extends AbstractFilter {
    /**
     * Supported tags.
     *
     * @type array
     */
    protected $_tags = array(
        'soundcloud' => array(
            'tag' => 'soundcloud',
            'template' => '',
            'displayType' => Decoda::TYPE_BLOCK,
            'allowedTypes' => Decoda::TYPE_NONE,
            'contentPattern' => '/^https:\/\/soundcloud[^(<|>)]+$/is',
        ),
        'youtube' => array(
            'tag' => 'youtube',
            'template' => 'video',
            'displayType' => Decoda::TYPE_BLOCK,
            'allowedTypes' => Decoda::TYPE_NONE,
            'contentPattern' => '/^[-_a-z0-9]+$/is',
        ),
        'issuu' => array(
            'tag' => 'issuu',
            'template' => '',
            'displayType' => Decoda::TYPE_BLOCK,
            'allowedTypes' => Decoda::TYPE_NONE,
            'contentPattern' => '/^(https?:)?\/\/e\.issuu.com\/embed\.html#[^(<|>)]+$/is',
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
        $parser = $this->getParser();
        $content = !empty($tag['content']) ? $tag['content'] : $content;

        // Test for an empty filter or empty tag
        if (!$setup || (!$content && $parser->getConfig('removeEmpty'))) {
            return null;
        }

        if ($content) {
            // If content doesn't match the pattern, don't wrap in a tag
            if ($setup['contentPattern']) {
                if (!preg_match($setup['contentPattern'], $content)) {
                    return sprintf('(Invalid %s)', $tag['tag']);
                }
            }
        } else {
            return sprintf('(Invalid %s)', $tag['tag']);
        }
        $allowfullscreen = false;
        switch($tag['tag']) {
            case 'youtube':
                $url="https://youtube.com/embed/".$content;
                $size = array(315,560);
                break;
            case 'soundcloud':
                $url = 'https://w.soundcloud.com/player/?url='.urlencode($content).'';
                $size = array(140,570);
                break;
            case 'issuu':
                $url = $content;
                $size = array(371,'100%');
                $allowfullscreen = true;
                break;
        }
        return '<iframe style="border: none" height="'.$size[0].'" width="'.$size[1].'" src="'.$url.'" '.($allowfullscreen ? 'allowfullscreen' : '').'></iframe>';
    }

}