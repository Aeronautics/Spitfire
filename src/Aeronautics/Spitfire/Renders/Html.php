<?php

namespace Aeronautics\Spitfire\Renders;

class Html extends AbstractRender
{

    private $labels;

    public function getLabels()
    {
        if (null === $this->labels) {
            $this->labels = parse_ini_file(__DIR__ . '/HtmlLabels.ini');
        }
        return $this->labels;
    }

    public function getLabel($label, $default = null)
    {
        $labels = $this->getLabels();
        if (isset($labels[$label])) {
            $default = $labels[$label];
        }
        return $default;
    }

    public function getContent(array $data)
    {
        $rootName = key($data);

        $template = file_get_contents(__DIR__ . '/HtmlTemplate.html');
        $template = str_replace('{rootName}', $rootName, $template);
        $xmlRoot  = simplexml_load_string($template);
        $this->htmlConverter($xmlRoot->body->div->ul, $data[$rootName]);
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
                    $child->addAttribute('class', 'well');
                    $child = $child->addChild('dl');
                    $this->htmlConverter($child, $v);
                } elseif (is_scalar($v) || is_null($v)) {
                    if ('ul' === $xmlRoot->getName()) {
                        $xmlRoot = $xmlRoot->addChild('li');
                        $xmlRoot->addAttribute('class', 'well');
                        $xmlRoot = $xmlRoot->addChild('dl');
                    }
                    $dt = $xmlRoot->addChild('dt', $this->getLabel($k, $k));
                    $dt->addAttribute('class', $k . '-label');
                    if ($k == 'foto' && !empty($v)) {
                        $dd     = $xmlRoot->addChild('dd');
                        $img    = $dd->addChild('img');
                        $img->addAttribute('src', 'data:image/png;base64,' . $v);
                    } else {
                        $dd     = $xmlRoot->addChild('dd', $v);
                    }
                    $dd->addAttribute('class', $k . '-value');
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

