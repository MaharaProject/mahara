{include file="header.tpl"}
<table class="fullwidth">
  <thead>
    <tr>
      <th style="width: 47%;">{str tag=Views section=view}/{str tag=collections section=collection}</th>
      <th style="width: 47%;">{str tag=accessibleby section=view}</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
{foreach from=$accesslists item=accesslist}
    <tr class="{cycle values='r0,r1'}">
      <td>
      {if $accesslist.collections}
        <div><strong>{str tag=collections section=collection}:</strong>
        {foreach from=$accesslist.collections item=collection name=c}{strip}
          <a href="">{$collection.name|str_shorten_text:30:true}</a>{if !$.foreach.c.last}, {/if}
        {/strip}{/foreach}
        </div>
      {/if}
      {if $accesslist.views}
        <div><strong>{str tag=Views section=view}:</strong>
        {foreach from=$accesslist.views item=view name=v}{strip}
          <a href="">{$view.name|str_shorten_text:30:true}</a>{if !$.foreach.v.last}, {/if}
        {/strip}{/foreach}
        </div>
      {/if}
      </td>
      <td>
      {if $accesslist.access}
        <div class="videsc">{$accesslist.access}</div>
      {/if}
      {if $accesslist.accessgroups}
        {foreach from=$accesslist.accessgroups item=accessgroup}
          <div>
          {if $accessgroup.accesstype == 'loggedin'}
            {str tag="loggedin" section="view"}
          {elseif $accessgroup.accesstype == 'public'}
            {str tag="public" section="view"}
          {elseif $accessgroup.accesstype == 'friends'}
            <a href="{$WWWROOT}user/myfriends.php" id="link-myfriends">{str tag="friends" section="view"}</a>
          {elseif $accessgroup.accesstype == 'group'}
            <a href="{$WWWROOT}group/view.php?id={$accessgroup.id}">{$accessgroup.name}</a>{if $accessgroup.role} ({$accessgroup.roledisplay}){/if}
          {elseif $accessgroup.accesstype == 'user'}
            <a href="{$WWWROOT}user/view.php?id={$accessgroup.id}">{$accessgroup.id|display_name|escape}</a>
          {elseif $accessgroup.accesstype == 'secreturl'}
            {str tag="peoplewiththesecreturl" section="view"}
          {/if}
          </div>
        {/foreach}
        {if $view.template}<div>{str tag=thisviewmaybecopied section=view}</div>{/if}
          </div>
      {else}
        <div class="videsc">{str tag="nobodycanseethisview2" section="view"}</div>
      {/if}
      </td>
      <td>
        <a href="{$WWWROOT}view/access.php?id={$accesslist.viewid}">{str tag=edit}</a>
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>

{include file="footer.tpl"}
