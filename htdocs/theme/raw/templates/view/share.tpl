{include file="header.tpl"}
{if !$accesslists}
<p>{str tag=youhaventcreatedanyviewsyet section=view}</p>
{else}
<table class="fullwidth accesslists">
  <thead>
    <tr>
      <th colspan=2>{str tag=accesslist section=view}</th>
      <th>
        <span class="fl">{str tag=Views section=view} &amp; {str tag=collections section=collection}</span>
        <span class="secreturl fr">{str tag=token section=view}</span>
      </th>
    </tr>
  </thead>
  <tbody>
{foreach from=$accesslists item=accesslist name=als}
    <tr class="{cycle values='r0,r1'}">
      <td class="al-edit">
        <div class="center"><strong>{$dwoo.foreach.als.iteration}</strong></div>
        <a class="btn-access" href="{$WWWROOT}view/access.php?id={$accesslist.viewid}">{str tag=edit}</a>
      </td>
      <td class="al">
      {if $accesslist.access}
        <div class="videsc">{$accesslist.access}</div>
      {/if}
      {if $accesslist.accessgroups}
        {foreach from=$accesslist.accessgroups item=accessgroup}
          <div class="accesslistitem">
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
            {str tag="token" section="view"} <a href="" title="{str tag=showfullurl section=view}" class="secreturl">{$accessgroup.token|str_shorten_text:9:true}</a>
            {/if}
          {if $accessgroup.startdate}
            {if $accessgroup.stopdate}
              {$accessgroup.startdate|strtotime|format_date:'strfdaymonthyearshort'}&rarr;{$accessgroup.stopdate|strtotime|format_date:'strfdaymonthyearshort'}
            {else}
              {str tag=after} {$accessgroup.startdate|strtotime|format_date:'strfdaymonthyearshort'}
            {/if}
          {elseif $accessgroup.stopdate}
            {str tag=before} {$accessgroup.stopdate|strtotime|format_date:'strfdaymonthyearshort'}
          {/if}
          {if $accessgroup.accesstype == 'secreturl'}
            <div class="expandurl hidden">{$WWWROOT}view/view.php?t={$accessgroup.token}</div>
          {/if}
          </div>
        {/foreach}
        {if $view.template}<div>{str tag=thisviewmaybecopied section=view}</div>{/if}
          </div>
      {/if}
      </td>
      <td class="cv">
        {if $accesslist.views}
          {foreach from=$accesslist.views item=view name=v}
          <div class="cv-listitem">
            <div class="fl cv-name">
              <a href="">{$view.name|str_shorten_text:60:true}</a>
            </div>
            <div class="fl secreturl">
            {foreach from=$view.secreturls item=url}
              <div><a href="">{$url.token|str_shorten_text:9:true}</a></div>
            {/foreach}
            </div>
          </div>
          {/foreach}
        {/if}
        {if $accesslist.collections}
          {foreach from=$accesslist.collections item=collection name=c}
          <div class="cv-listitem">
            <div class="fl cv-name">
              <a href="">{$collection.name|str_shorten_text:60:true}</a>
            </div>
            <div class="fl secreturl">
            {foreach from=$view.secreturls item=url}
              <div><a href="">{$url.token|str_shorten_text:9:true}</a></div>
            {/foreach}
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
