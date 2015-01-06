{foreach from=$results.data item=r}
<tr class="{cycle values='r0,r1'}">
    {foreach from=$cols key=f item=c}{strip}
    <td{if $c.class} class="{$c.class}"{/if}>
        {if !$c.template}
            {$r[$f]}
        {else}
            {include file=$c.template r=$r f=$f}
        {/if}
    </td>{/strip}
    {/foreach}
</tr>
{/foreach}