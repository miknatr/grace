<?php

namespace Grace\Bundle\CommonBundle\Generator\ZendCodeGenerator;

/**
 * Переопределяем поведение, для успешного чтения аннотаций
 * нужно чтобы строки в phpdoc-блоках не урезались автоматически
 * даже если строка слишком длиная
 */
class PhpDoc extends \Zend_CodeGenerator_Php_Docblock
{
    /**
     * _docCommentize()
     *
     * @param string $content
     * @return string
     */
    protected function _docCommentize($content)
    {
        $indent = $this->getIndentation();
        $output = $indent . '/**' . self::LINE_FEED;
        //Комментируем эту строку, остальные строки оставлены из стандартного поведения
        //$content = wordwrap($content, 80, self::LINE_FEED);
        $lines = explode(self::LINE_FEED, $content);
        foreach ($lines as $line) {
            $output .= $indent . ' *';
            if ($line) {
                $output .= " $line";
            }
            $output .= self::LINE_FEED;
        }
        $output .= $indent . ' */' . self::LINE_FEED;
        return $output;
    }
}