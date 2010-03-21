  {foreach from=$data item=item}
    <tr class="{cycle name=rows values='r0,r1'}">
      <td>
      {include file="comment.tpl" comment=$item}
      </td>
    </tr>
  {/foreach}
