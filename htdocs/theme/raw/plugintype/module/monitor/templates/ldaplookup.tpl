{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td>{$item.institution}</td>
    <td class="center">{$item.ldapauthority}</td>
    <td class="center {if !$item.ldapstatus}errmsg{/if}">
        {$item.ldapstatusdesc}
    </td>
    <td class="center">{$item.ldapstatusmessage}</td>
  </tr>
{/foreach}
