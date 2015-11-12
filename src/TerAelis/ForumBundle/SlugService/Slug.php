<?php

namespace TerAelis\ForumBundle\SlugService;

class Slug
{
    public function slugify($slug)
    {
        $bad = array( 'Š','Ž','‘','ž','Ÿ','À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ',
            'Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','à','á','â','ã','ä','å','ç','è','é','ê',
            'ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ',
            'Þ','þ','Ð','ð','ß','Œ','œ','Æ','æ','µ',
            '”',"'",'“','"',"\n","\r",'_','&',':','/');

        $good = array( 'S','Z','s','z','Y','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N',
            'O','O','O','O','O','O','U','U','U','U','Y','a','a','a','a','a','a','c','e','e','e',
            'e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y',
            'TH','th','DH','dh','ss','OE','oe','AE','ae','u',
            '','-','','','','','-','','','');

        // replace strange characters with alphanumeric equivalents
        $slug = str_replace( $bad, $good, $slug );

        $slug = trim($slug);

        // remove any duplicate whitespace, and ensure all characters are alphanumeric
        $bad_reg = array('/\s+/','/[^A-Za-z0-9\-]/');
        $good_reg = array('-','');
        $slug = preg_replace($bad_reg, $good_reg, $slug);

        // and lowercase
        $slug = strtolower($slug);

        return $slug;
    }
}
?>