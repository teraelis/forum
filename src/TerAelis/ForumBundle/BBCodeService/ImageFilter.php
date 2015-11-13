<?php
/**
 * @copyright   2006-2014, Miles Johnson - http://milesj.me
 * @license     https://github.com/milesj/decoda/blob/master/license.md
 * @link        http://milesj.me/code/php/decoda
 */

namespace TerAelis\ForumBundle\BBCodeService;

use Decoda\Filter\ImageFilter as DecodaImageFilter;

/**
 * Provides tags for images.
 */
class ImageFilter extends DecodaImageFilter {
    /**
     * {@inheritdoc}
     */
    public function parse(array $tag, $content) {
        $tag['attributes']['class'] = 'js-content-image';
        return parent::parse($tag, $content);
    }
}