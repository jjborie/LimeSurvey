<?php

namespace LimeSurvey\Api\Command\Request;

class Request
{
    private $data = array();

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData($key = null, $defaultValue = null)
    {
        return $key && isset($this->data[$key])
            ? $this->data[$key]
            : $defaultValue;
    }
}
