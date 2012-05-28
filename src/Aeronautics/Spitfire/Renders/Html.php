<?php

namespace Aeronautics\Spitfire\Renders;

class Html extends AbstractRender
{

    public function getContent(array $data)
    {
        $rootName = key($data);

        $template = file_get_contents(__DIR__ . '/HtmlTemplate.html');
        $template = str_replace('{rootName}', $rootName, $template);
        $xmlRoot  = simplexml_load_string($template);
        $this->htmlConverter($xmlRoot->body->article->ul, $data[$rootName]);
        $dom      = new \DOMDocument;
        $dom->loadXml($xmlRoot->asXML());
        $dom->formatOutput = true;
        return '<!DOCTYPE html>' . $dom->saveHtml();
    }

    public function htmlConverter($xmlRoot, $data)
    {
        if (is_array($data) || is_object($data))
            foreach ($data as $k => $v) {
                if (is_numeric($k)) {
                    $child = $xmlRoot->addChild('li');
                    $child = $child->addChild('dl');
                    $this->htmlConverter($child, $v);
                } elseif (is_scalar($v) || is_null($v)) {
                    if ('ul' === $xmlRoot->getName()) {
                        $xmlRoot = $xmlRoot->addChild('li');
                        $xmlRoot = $xmlRoot->addChild('dl');
                    }
                    $xmlRoot->addChild('dt', $k);
                    if ($k == 'foto' && !empty($v)) {
                        $dd     = $xmlRoot->addChild('dd');
                        $img    = $dd->addChild('img');
                        $base64 = base64_encode($v);
                        $img->addAttribute('src', 'data:image/png;base64,' . $base64);
                    } else {
                        $xmlRoot->addChild('dd', $v);
                    }
                } elseif ($k == 'links') {
                    $xmlRoot->addChild('dt', 'Links');
                    $nav = $xmlRoot->addChild('dd');
                    $nav = $nav->addChild('ul');
                    foreach ($v as $link) {
                        $linkElem = $nav->addChild('li');
                        $linkElem = $linkElem->addChild('a', $link['title'] ? : $link['href']);
                        foreach ($link as $attribute => $attrValue)
                            $linkElem->addAttribute($attribute, $attrValue);
                    }
                } else {
                    $child = $xmlRoot->addChild('li');
                    $this->htmlConverter($child, $v);
                }
            }
        return $data;
    }

    public function getMimetype()
    {
        return 'text/html';
    }


}

