{auto_escape off}
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:leap="http://wiki.cetis.ac.uk/2009-03/LEAP2A_predicates#"
    xmlns:leaptype="http://wiki.cetis.ac.uk/2009-03/LEAP2A_types#"
    xmlns:categories="http://wiki.cetis.ac.uk/2009-03/LEAP2A_categories/"
    xmlns:portfolio="{$WWWROOT}export/leap/{$userid}/{$export_time}/"
    xmlns:mahara="http://wiki.mahara.org/Developer_Area/Import%2F%2FExport/LEAP_Extensions#"
>
    <id>{$WWWROOT}export/{$userid}/{$export_time}</id>
    <title>Mahara Leap2A Export for {$name}, {$export_time|format_date:"strftimedatetimeshort"}</title>
    <updated>{$export_time_rfc3339}</updated>
    <generator uri="http://mahara.org/" version="{$leap_export_version|escape}">Mahara</generator>
{include file="export:leap:author.tpl"}

{/auto_escape}
