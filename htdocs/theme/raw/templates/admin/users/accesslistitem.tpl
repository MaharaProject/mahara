{if $item.access}<div>{$item.access}</div>{/if}
{foreach from=$item.accessgroups item=accessgroup name=ags}{strip}
  {if $accessgroup.accesstype == 'loggedin'}
    {str tag="registeredusers" section="view"}
  {elseif $accessgroup.accesstype == 'public'}
    {str tag="public" section="view"}
  {elseif $accessgroup.accesstype == 'friends'}
    {str tag="friends" section="view"}
  {elseif $accessgroup.accesstype == 'group'}
    <a href="{$WWWROOT}group/view.php?id={$accessgroup.id}">{$accessgroup.name}</a>{if $accessgroup.role} ({$accessgroup.roledisplay}){/if}
  {elseif $accessgroup.accesstype == 'institution'}
    <a href="{$WWWROOT}institution/index.php?institution={$accessgroup.id}">{$accessgroup.id|institution_display_name}</a>
  {elseif $accessgroup.accesstype == 'user'}
    <a href="{$WWWROOT}user/view.php?id={$accessgroup.id}">{$accessgroup.id|display_name:null:true:true}</a>
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
{if $item.template}<div>{str tag=thisviewmaybecopied section=view}</div>{/if}
{if $item.secreturls}<div>{str tag=secreturls section=view} ({$item.secreturls})</div>{/if}
