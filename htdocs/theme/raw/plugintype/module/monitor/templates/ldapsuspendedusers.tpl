{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td>{$item.institution}</td>
    <td class="center">{$item.ldapauthority}</td>
    <td class="center {if $item.error}errmsg{/if}">
        {$item.value}
    </td>
    <td class="center {if $item.error}errmsg{/if}">
        {$item.details}
    </td>
  </tr>
{/foreach}
