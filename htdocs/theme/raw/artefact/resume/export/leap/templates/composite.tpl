{include file="export:leap:entry.tpl" skipfooter=true}
{if $myrole}         <leap2:myrole>{$myrole}</leap2:myrole>
{/if}
{if $start}        <leap2:date leap2:point="start" leap2:label="{$start}"></leap2:date>
{/if}
{if $end}        <leap2:date leap2:point="end" leap2:label="{$end}"></leap2:date>
{/if}
{include file="export:leap:entryfooter.tpl"}
