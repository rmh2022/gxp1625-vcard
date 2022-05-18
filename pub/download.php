<?php

require_once('../lib/VCardParser.php');
require_once('../etc/config.php');

use JeroenDesloovere\VCard\VCardParser;

function escape_for_xml($str)
{
    return htmlspecialchars($str, ENT_XML1, 'UTF-8');
}

function sanitize_phone_number($str)
{
    return preg_replace('/[^\+0-9]/', '', $str);
}

header('Content-Type: application/xml');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<AddressBook>';

$contact_id = 1;

foreach(scandir($vcard_dir) as $file)
    {
        if (! preg_match('/.*\.vcf$/', $file, $matches))
            continue;

        foreach(VCardParser::parseFromFile($vcard_dir.'/'.$file) as $vcard)
            {
                echo '<Contact>';
                echo '<id>'.$contact_id++.'</id>';
                echo '<FirstName>'.escape_for_xml($vcard->firstname).'</FirstName>';
                if (strlen($vcard->lastname ?? '') > 0)
                    echo '<LastName>'.escape_for_xml($vcard->lastname).'</LastName>';

                // A single entry is allowed for each phone type, so we pick the first one.

                if (count($vcard->phone['work']) > 0)
                    {
                        echo '<Phone type="Work">';
                        echo '<phonenumber>'.escape_for_xml(sanitize_phone_number($vcard->phone['work'][0])).'</phonenumber>';
                        echo '<accountindex>1</accountindex>';
                        echo '</Phone>';
                    }

                if (count($vcard->phone['home']) > 0)
                    {
                        echo '<Phone type="Home">';
                        echo '<phonenumber>'.escape_for_xml(sanitize_phone_number($vcard->phone['home'][0])).'</phonenumber>';
                        echo '<accountindex>1</accountindex>';
                        echo '</Phone>';
                    }

                if (count($vcard->phone['cell']) > 0)
                    {
                        echo '<Phone type="Cell">';
                        echo '<phonenumber>'.escape_for_xml(sanitize_phone_number($vcard->phone['cell'][0])).'</phonenumber>';
                        echo '<accountindex>1</accountindex>';
                        echo '</Phone>';
                    }
                
                echo '<Frequent>0</Frequent>';
                echo '<Primary>0</Primary>';
                echo '</Contact>';
            }
    }

echo '</AddressBook>';
?>
