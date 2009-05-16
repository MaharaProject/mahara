{include file="export:leap:entry.tpl" skipfooter=true}
{if $start_date}        <leap:date leap:point="start" leap:label="{$start_label|escape}">{$start_date|escape}</leap:date>
{/if}
{if $end_date}        <leap:date leap:point="end" leap:label="{$end_label|escape}">{$end_date|escape}</leap:date>
{/if}
{include file="export:leap:entryfooter.tpl"}
