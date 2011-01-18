{include file="header.tpl"}

{if $institution}                {$institutionselector|safe}{/if}

{if !$accesslists}
<p>{str tag=youhaventcreatedanyviewsyet section=view}</p>
{else}
<table class="fullwidth accesslists">
  <thead>
    <tr>
      <th>{str tag=accesslist section=view}</th>
      <th>{str tag=editaccess section=view}</th>
      <th>
        <span class="fl">{str tag=Views section=view} &amp; {str tag=collections section=collection}</span>
        <span class="fr">{str tag=secreturls section=view}</span>
      </th>
    </tr>
  </thead>
  <tbody>
{foreach from=$accesslists item=accesslist name=als}
    <tr class="{cycle values='r0,r1'}">
      <td class="al">
      {if $accesslist.access}
        <div class="videsc">{$accesslist.access}</div>
      {/if}
      {if $accesslist.accessgroups}
          <div class="accesslistitem">
        {foreach from=$accesslist.accessgroups item=accessgroup name=ags}{strip}
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
          {/if}
          {if $accessgroup.startdate}
            {if $accessgroup.stopdate}
              <span class="date"> {$accessgroup.startdate|strtotime|format_date:'strfdaymonthyearshort'}&rarr;{$accessgroup.stopdate|strtotime|format_date:'strfdaymonthyearshort'}</span>
            {else}
              <span class="date"> {str tag=after} {$accessgroup.startdate|strtotime|format_date:'strfdaymonthyearshort'}</span>
            {/if}
          {elseif $accessgroup.stopdate}
            <span class="date"> {str tag=before} {$accessgroup.stopdate|strtotime|format_date:'strfdaymonthyearshort'}</span>
          {/if}{if !$dwoo.foreach.ags.last}, {/if}
        {/strip}{/foreach}
          </div>
        {if $view.template}<div>{str tag=thisviewmaybecopied section=view}</div>{/if}
          </div>
      {/if}
      </td>
      <td class="al-edit">
        <a href="{$WWWROOT}view/access.php?id={$accesslist.viewid}" title="{str tag=editaccess section=view}"><img src="{theme_url filename='images/edit_access.gif'}" alt="{str tag=editaccess}"></a>
      </td>
      <td class="cv">
        {if $accesslist.views}
          {foreach from=$accesslist.views item=view name=v}
          <div class="cv-listitem">
            <div class="fr secreturls">
              <span class="secreturlcount">{count($view.secreturls)}</span> <span class="cv-listitem-edit"><a title="{str tag=editsecreturlaccess section=view}" href="{$WWWROOT}view/urls.php?id={$view.id}"><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a></span>
            </div>
            <div class="viewname">
              <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.name|str_shorten_text:60:true}</a>
            </div>
          </div>
          {/foreach}
        {/if}
        {if $accesslist.collections}
          {foreach from=$accesslist.collections item=collection name=c}
          <div class="cb cv-listitem">
            <div class="fr secreturls">
              <span class="secreturlcount">{count($collection.secreturls)}</span> <span class="cv-listitem-edit"><a title="{str tag=editsecreturlaccess section=view}" href="{$WWWROOT}view/urls.php?id={$collection.viewid}"><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a></span>
            </div>
            <div>
              <strong><a href="{$WWWROOT}view/view.php?id={$collection.viewid}">{$collection.name|str_shorten_text:60:true}</a></strong>
            </div>
          </div>
          {/foreach}
        {/if}
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}
{include file="footer.tpl"}
