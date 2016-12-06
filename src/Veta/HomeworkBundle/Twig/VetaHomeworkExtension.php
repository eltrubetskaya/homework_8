<?php
/**
 * Created by PhpStorm.
 * User: elena
 * Date: 05.12.16
 * Time: 16:54
 */

namespace Veta\HomeworkBundle\Twig;

use Twig_Extension;
use Twig_ExtensionInterface;

class VetaHomeworkExtension extends Twig_Extension implements Twig_ExtensionInterface
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('text', [$this, 'textFilter']),
        ];
    }

    /**
     * @return mixed
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('totime', [$this, 'toTime']),
        ];
    }

    /**
     * Replace symbol in Text
     *
     * @param $text
     * @return string
     */
    public function textFilter($text)
    {
        $vowels = ["<", ">", "|", "%", "^"];
        $out_text = str_replace($vowels, "", $text);

        return $out_text;
    }

    /**
     * @param string $string
     * @return int
     */
    public function toTime($string)
    {
        return strtotime($string);
    }
}
