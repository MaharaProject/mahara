
      <td><h3 class="title"><a href="{$item.url}">{$item.name|str_shorten_text:50:true}</a></h3></td>
      <td class="accesslist">
      {if $item.access}<div class="detail">{$item.access}</div>{/if}
      {if $item.accessgroups}
        {foreach from=$item.accessgroups item=accessgroup name=ags}{strip}
          {if $accessgroup.accesstype == 'loggedin'}
            {str tag="registeredusers" section="view"}
          {elseif $accessgroup.accesstype == 'public'}
            {str tag="public" section="view"}
          {elseif $accessgroup.accesstype == 'friends'}
            <a href="{$WWWROOT}user/myfriends.php" id="link-myfriends">{str tag="friends" section="view"}</a>
          {elseif $accessgroup.accesstype == 'group'}
            <a href="{$accessgroup.groupurl}">{$accessgroup.name}</a>{if $accessgroup.role} ({$accessgroup.roledisplay}){/if}
          {elseif $accessgroup.accesstype == 'institution'}
            <a href="{$WWWROOT}account/institutions.php">{$accessgroup.id|institution_display_name}</a>
          {elseif $accessgroup.accesstype == 'user'}
            <a href="{profile_url($accessgroup.id)}">{$accessgroup.id|display_name}</a>
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
      {/if}
      {if $item.template}<div class="detail">{str tag=thisviewmaybecopied section=view}</div>{/if}
      </td>
      <td class="al-edit">
        <a id="editaccess_{$item.viewid}" href="{$WWWROOT}view/access.php?id={$item.viewid}" title="{str tag=editaccess section=view}"><img src="{theme_image_url filename='btn_access'}" alt="{str tag=editaccess}"></a>
      </td>
      <td class="secreturls">
        {$item.secreturls} <a id="secreturl_{$item.viewid}" title="{str tag=editsecreturlaccess section=view}" href="{$WWWROOT}view/urls.php?id={$item.viewid}"><img src="{theme_image_url filename='btn_secreturl'}" alt="{str tag=edit}"></a>
      </td>
