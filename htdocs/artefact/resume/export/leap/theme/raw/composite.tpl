{include file="export:leap:entry.tpl" skipfooter=true}
{if $start || $end}        <mahara:resumedate start="{$start|escape}" end="{$end|escape}"/>
{/if}
{include file="export:leap:entryfooter.tpl"}
