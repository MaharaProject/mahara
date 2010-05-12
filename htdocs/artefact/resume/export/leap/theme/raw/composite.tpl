{auto_escape off}
{include file="export:leap:entry.tpl" skipfooter=true}
{if $start}        <leap:date leap:point="start" leap:label="{$start|escape}"></leap:date>
{/if}
{if $end}        <leap:date leap:point="end" leap:label="{$end|escape}"></leap:date>
{/if}
{include file="export:leap:entryfooter.tpl"}
{/auto_escape}
