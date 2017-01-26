{foreach from=$results.data item=r}
<tr class="{cycle values='r0,r1'}{if !$r.active} inactive{/if}">
    {foreach from=$cols key=f item=c}
    {strip}

        {if !$c.mergelast}
            <td{if $c.class} class="{$c.class}"{/if}>
        {/if}

            {if !$c.template}
                {$r[$f]}
            {else}
                {include file=$c.template r=$r f=$f}
            {/if}

        {if !$c.mergefirst}
            </td>
        {/if}

    {/strip}
    {/foreach}
</tr>
{/foreach}