<?php

namespace ACPT\Core\CQRS\Query;

use ACPT\Utils\PHP\Date;
use Parsedown;

class FetchChangelogQuery implements QueryInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $changelogFile = plugin_dir_path( __FILE__ ) . "../../../../changelog.md";

        if(!file_exists($changelogFile)){
            return [];
        }

        $versions = [];
        $md = file_get_contents($changelogFile);
        $Parsedown = new Parsedown();
        $html = $Parsedown->text($md);

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $h2 = $dom->getElementsByTagName("h2");

        /** @var \DOMElement $title */
        foreach ($h2 as $title){

            $version = explode(" - ", $title->textContent);
            $date = $version[1];
            $idVersion = "id-".$version[0];

            $versions[] = [
                'label' => $version[0],
                'value' => $idVersion,
            ];

            $title->setAttribute('class', "changelog-section-header");
            $title->setAttribute('id', $idVersion);

            $spanVersion = new \DOMElement( "span" );
            $spanVersion->setAttribute('class', 'version');
            $spanVersion->textContent = $version[0];

            $spanDate = new \DOMElement( "span" );
            $spanDate->setAttribute('class', 'date');

            $dateFormat =  get_option('date_format') ?? "Y-m-d";
            $spanDate->textContent = Date::format($dateFormat, $version[1]);

            $title->textContent = "";
            $title->appendChild($spanVersion);
            $title->appendChild($spanDate);
        }

        $h3 = $dom->getElementsByTagName("h3");

        /** @var \DOMElement $title */
        foreach ($h3 as $title){

            switch ($title->textContent){

                default:
                case "Added":
                    $className = "added";
                    break;

                case "Changed":
                    $className = "changed";
                    break;

                case "Removed":
                    $className = "removed";
                    break;

                case "Fixed":
                    $className = "fixed";
                    break;
            }

            $title->setAttribute("class", $className);
            $title->nextSibling->nextSibling->setAttribute("class", $className);
        }

        return [
            'versions' => $versions,
            'changelog' => $dom->saveHTML(),
        ];
    }
}