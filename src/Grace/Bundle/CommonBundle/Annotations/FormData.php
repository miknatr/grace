<?php
namespace Grace\Bundle\CommonBundle\Annotations;

/** @Annotation */
final class FormData
{
    private $data = array();

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}