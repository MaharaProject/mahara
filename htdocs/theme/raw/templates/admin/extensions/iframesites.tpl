{include file="header.tpl"}
<p class="lead">{str tag=allowediframesitesdescription section=admin}</p>
<p class="lead">{str tag=allowediframesitesdescriptiondetail section=admin}</p>

<div class="card">
  {if $editurls}
  <table class="iframesources fullwidth table">
    <thead>
      <tr>
        <th>{str tag=displayname}</th>
        <th>{str tag=Site}</th>
        <th><span class="accessible-hidden sr-only">{str tag=edit}</span></th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$editurls item=item name=urls}
      <tr class="{cycle values='r0,r1' advance=false}">
        <th>{if $item.icon.faicon}
        <span class="icon icon-lg icon-brand icon-{$item.icon.faicon}" style="{$item.icon.style}" alt="{$item.name}" title="{$item.name}"></span>
        {else}
        <img src="{$item.icon.icon}" alt="{$item.name}" title="{$item.name}">
        {/if}
        {$item.name}</th>
        <td>{$item.url}</td>
        <td class="buttonscell">
          <div class="btn-group">

            <a id="edit-{$item.id}" class="url-open-editform btn btn-secondary btn-sm float-left closed" title="{str tag=edit}" href="">
              <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
              <span class="sr-only">{str(tag=editspecific arg1=$item.name)|escape:html|safe}</span>
            </a>
            {$item.deleteform|safe}

          </div>
        </td>
      </tr>
      <tr class="editrow {cycle} url-editform js-hidden active" id="edit-{$item.id}-form">
        <td colspan=3 class="form-condensed">{$item.editform|safe}</td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  {/if}


  <div class="card-body">
    {$newform|safe}
  </div>
</div>
{include file="footer.tpl"}
