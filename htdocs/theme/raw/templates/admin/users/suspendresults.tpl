{foreach from=$data item=item}
<tr class="{cycle values='r0,r1'}">
    <td>{$item->name}</td>
    <td>{if $item->institutions}
        {foreach from=$item->institutions item=institution}
            <div>{$institution}</div>
        {/foreach}
        {/if}
    </td>
    <td>{if $item->institutions}
        {foreach from=$item->institutionids item=institutionid}
            <div>{$institutionid}</div>
        {/foreach}
        {else}
            <div>{$item->studentid}</div>
        {/if}
    </td>
    <td>{$item->cusrname}</td>
    <td>{$item->reason}</td>
    <td>{$item->expiry}</td>
    <td><input type="checkbox" name="usr_{$item->id}"></td>
</tr>
{/foreach}