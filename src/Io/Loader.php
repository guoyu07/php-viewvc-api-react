<?php

namespace Clue\React\ViewVcApi\Io;

use SimpleXMLElement;

class Loader
{
    private $entities;

    public function __construct(array $entities = null)
    {
        if ($entities === null) {
            // get all HTML entities (minus those for XML parsing)
            $entities = get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES, 'UTF-8');
            unset($entities['<'], $entities['>'], $entities['&']);
        }

        $this->entities = $entities;
    }

    public function loadXmlFile($path)
    {
        return $this->loadXmlString(file_get_contents($path));
    }

    public function loadXmlString($html)
    {
        // log output often uses garbled ISO-8859-1 and UTF-8 encodings
        $html = \ForceUTF8\Encoding::toUTF8($html);

        // fix invalid markup of outdated ViewVC versions
        // - help link in footer not terminated
        // - selected branch/tag in CVS "sticky tag" dropdown has not attribute value
        // - self closing elements with no trailing slash
        // - remove navheader because of its often incomplete form tags
        $html = str_replace('Help</strong></td>', 'Help</a></strong></td>', $html);
        $html = str_replace('selected>', 'selected="selected">', $html);
        $html = preg_replace('#<((?:input|br|hr|img)[^\/\>]*)>#', '<$1 />', $html);
        $html = preg_replace('#\<div class\=\"vc_navheader\"\>.*?\<\/div\>#s','', $html);

        // replace named HTML entities with their UTF-8 value
        $html = str_replace(array_values($this->entities), array_keys($this->entities), $html);

        // clean up namespace declaration
        $html = str_replace('xmlns="', 'ns="', $html);

        return new SimpleXMLElement($html);
    }
}
