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
    // Only DTMF digits are allowed
    return preg_replace('/[^\+\*#0-9]/', '', $str);
}

function gen_phone_entry($type, $unsanitized_phone_str, $preferred = false)
{
    // $preferred means the phone number has been defined as default for this contact, but AFAIK there's
    // no way to represent this in the GXP1625 XML.
    echo '<Phone type="'.$type.'">'."\n";
    echo '<phonenumber>'.escape_for_xml(sanitize_phone_number($unsanitized_phone_str)).'</phonenumber>'."\n";
    echo '<accountindex>1</accountindex>'."\n";
    echo '</Phone>'."\n";
}

header('Content-Type: application/xml');

// Newlines MUST be included, otherwise the entire file may be ignored by the GXP1625. This doesn't always
// happen (I suspect it may be related to size of the file and/or size of specific tags).

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<AddressBook>'."\n";

$contact_id = 1;

foreach(scandir($vcard_dir) as $file)
    {
        if (! preg_match('/.*\.vcf$/', $file, $matches))
            continue;

        foreach(VCardParser::parseFromFile($vcard_dir.'/'.$file) as $vcard)
            {
                echo '<Contact>'."\n";
                echo '<id>'.$contact_id++.'</id>'."\n";
                echo '<FirstName>'.escape_for_xml($vcard->firstname).'</FirstName>'."\n";
                if (strlen($vcard->lastname ?? '') > 0)
                    echo '<LastName>'.escape_for_xml($vcard->lastname).'</LastName>'."\n";

                foreach($vcard->phone as $key => $value)
                    {
                        // TYPE field may be stored in upper-case
                        $lkey = strtolower($key);

                        $pref = preg_match('/,pref$/', $lkey);

                        switch(preg_replace('/,pref$/', '', $lkey))
                            {
                            case 'work':
                                gen_phone_entry('Work', $value[0], $pref);
                                break;
                            case 'home':
                                gen_phone_entry('Home', $value[0], $pref);
                                break;
                            case 'cell':
                            case 'x-mobile':    // en
                            case 'x-mòbil':     // ca
                            case 'x-móvil':     // es
                                gen_phone_entry('Cell', $value[0], $pref);
                                break;

                            case 'default':     // Android lists this as "Other"
                            default:
                                // Not ideal, but still better than silently skipping it.
                                gen_phone_entry('Cell', $value[0], $pref);
                            }
                    }
                
                echo '<Frequent>0</Frequent>'."\n";
                echo '<Primary>0</Primary>'."\n";
                echo '</Contact>'."\n";
            }
    }

echo '</AddressBook>'."\n";
?>
