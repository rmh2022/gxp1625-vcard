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

function gen_phone_entry($type, $unsanitized_phone_str, $preferred = false)
{
    // $preferred means the phone number has been defined as default for this contact, but AFAIK there's
    // no way to represent this in the GXP1625 XML.
    echo '<Phone type="'.$type.'">';
    echo '<phonenumber>'.escape_for_xml(sanitize_phone_number($unsanitized_phone_str)).'</phonenumber>';
    echo '<accountindex>1</accountindex>';
    echo '</Phone>';
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
                    gen_phone_entry('Work', $vcard->phone['work'][0], false);
                if (count($vcard->phone['work,pref']) > 0)
                    gen_phone_entry('Work', $vcard->phone['work,pref'][0], true);

                if (count($vcard->phone['home']) > 0)
                    gen_phone_entry('Home', $vcard->phone['home'][0], false);
                if (count($vcard->phone['home,pref']) > 0)
                    gen_phone_entry('Home', $vcard->phone['home,pref'][0], true);

                if (count($vcard->phone['cell']) > 0)
                    gen_phone_entry('Cell', $vcard->phone['cell'][0], false);
                if (count($vcard->phone['cell,pref']) > 0)
                    gen_phone_entry('Cell', $vcard->phone['cell,pref'][0], true);
                
                echo '<Frequent>0</Frequent>';
                echo '<Primary>0</Primary>';
                echo '</Contact>';
            }
    }

echo '</AddressBook>';
?>
