<?php

namespace Aeronautics\Spitfire\Renders;

class Xml extends AbstractRender
{

    public function getContent(array $data)
    {
        $rootName = key($data);
        $xmlRoot  = simplexml_load_string("<{$rootName}/>");
        $this->xmlConverter($xmlRoot, $data[$rootName]);
        $dom      = new \DOMDocument;
        $dom->formatOutput = true;
        $dom->loadXml($xmlRoot->asXML());
        return $dom->saveXML();
    }

    public function xmlConverter($xmlRoot, $data)
    {
        if (is_array($data) || is_object($data))
            foreach ($data as $k => $v) {
                if (is_numeric($k)) {
                    $child = $xmlRoot->addChild('item');
                    $this->xmlConverter($child, $v);
                } elseif (is_scalar($v) || is_null($v)) {
                    if ($k == 'foto') {
                        $v = base64_encode($v);
                    }
                    $xmlRoot->addAttribute($k, $v);
                } elseif ($k == 'links') {
                    foreach ($v as $link) {
                        $linkElem = $xmlRoot->addChild('link');
                        foreach ($link as $attribute => $attrValue)
                            $linkElem->addAttribute($attribute, $attrValue);
                    }
                } else {
                    $child = $xmlRoot->addChild($k);
                    $this->xmlConverter($child, $v);
                }
            }
        return $data;
    }

    public function getMimetype()
    {
        return 'text/xml';
    }


}

