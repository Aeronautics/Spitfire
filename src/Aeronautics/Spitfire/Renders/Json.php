<?php

namespace Aeronautics\Spitfire\Renders;

class Json extends AbstractRender
{

    public function getContent(array $data)
    {
        $flags = 0;
        if (defined('JSON_PRETTY_PRINT')) {
            $flags = JSON_PRETTY_PRINT;
        }
        return json_encode($data, $flags);
    }

    public function getMimetype()
    {
        return 'application/json';
    }


}

