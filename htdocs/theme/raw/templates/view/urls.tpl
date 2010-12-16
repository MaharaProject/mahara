{include file="header.tpl"}

{if $editurls}
<table class="secreturls">
  <tbody>
  {foreach from=$editurls item=item name=urls}
    <tr class="{cycle values='r0,r1' advance=false}">
      <td><strong>{$item.url}</strong></td>
      <td class="buttons">
        <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline" title="{str tag=edit}" href="">
          <img src="{theme_url filename="images/edit.gif"}">
        </a>
        {$item.deleteform|safe}
      </td>
    </tr>
    <tr class="editrow {cycle} url-editform js-hidden" id="edit-{$item.id}-form">
      <td colspan=2>{$item.editform|safe}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}

{$newform|safe}

{include file="footer.tpl"}
