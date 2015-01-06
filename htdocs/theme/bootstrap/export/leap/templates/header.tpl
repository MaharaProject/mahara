<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:leap2="http://terms.leapspecs.org/"
    xmlns:categories="http://wiki.leapspecs.org/2A/categories/"
    xmlns:portfolio="{$WWWROOT}export/leap/{$userid}/{$export_time}/"
    xmlns:mahara="http://wiki.mahara.org/Developer_Area/Import%2F%2FExport/LEAP_Extensions#"
>
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <id>{$WWWROOT}export/{$userid}/{$export_time}</id>
    <title>Mahara Leap2A Export for {$name}, {$export_time|format_date:"strftimedatetimeshort"}</title>
    <updated>{$export_time_rfc3339}</updated>
    <generator uri="http://mahara.org/" version="{$leap_export_version}">Mahara</generator>
{include file="export:leap:author.tpl"}
