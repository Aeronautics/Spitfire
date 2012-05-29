<?php

namespace Aeronautics\Spitfire\Parsers\CMSP;

use \UnexpectedValueException as Value;
use \DateTime;
use \StdClass;
use \SplFixedArray;

class Presenca
{
    /**
     * URL to fetch XML.
     * Example: http://www2.camara.sp.gov.br/SIP/BaixarXML.aspx?arquivo=Presencas_2012_04_11_[0].xml
     */
    const URL = "http://www2.camara.sp.gov.br/SIP/BaixarXML.aspx?arquivo=%s";

    public static function getContent(DateTime $d)
    {
        $date     = $d->format('Y_m_d');
        $filename = 'Presencas_'.urlencode($date.'[0]').'.xml';
        $content  = file_get_content(sprintf(self::URL, urlencode($filename)));
        if (!$content)
            throw new Value('Nenhum valor retornado para XML: '.urldecode($filename));

        return $content;
    }

    protected function getSimpleXml($content=null)
    {
        $content = $content ?: $this->getContent(new DateTime);
        if (empty($content))
            throw new Value('Conteúdo inválido (em branco)!');

        return simplexml_load_string($content);
    }

    /**
     * @param  string        $content=null Valid XML
     * @return SplFixedArray
     */
    public function parsePresencesToArray($content=null)
    {
        $xml     = $this->getSimpleXml($content);
        $array   = new SplFixedArray(count($xml->Presencas->Vereador));
        $index   = 0;
        foreach ($xml->Presencas->Vereador as $vereador) {
            $object                = new StdClass;
            $object->nome          = (string) $vereador['NomeParlamentar'];
            $object->idParlamentar = (string) $vereador['IDParlamentar'];
            $object->partido       = (string) $vereador['Partido'];
            $object->presente      = (string) $vereador['Presente'];
            $object->idEvento      = (string) $vereador['IDEvento'];
            $object->acaoEvento    = (string) $vereador['AcaoEvento'];
            $object->horaEvento    = DateTime::createFromFormat('d/m/Y H:i:s', (string) $vereador['HoraEvento']);
            $array[$index++]       = $object;
        }

        return $array;
    }

    public function parseSumaryToObject($content=null)
    {
        $xml    = $this->getSimpleXml($content);
        $object = new StdClass;

    }
}
