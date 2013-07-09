{include file="header.tpl"}

{if !$allownew}
  <div class="message info">{str tag=publicaccessnotallowed section=view}</div>
{/if}

{if $editurls}
<table class="secreturls fullwidth">
  <tbody>
  {foreach from=$editurls item=item name=urls}
    <tr class="{cycle values='r0,r1' advance=false}">
      <td><strong>{$item.url}</strong></td>
      <td class="right buttons btns2">
        <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline" title="{str tag=edit}" href="">
          <img src="{theme_url filename="images/btn_edit.png"}">
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
