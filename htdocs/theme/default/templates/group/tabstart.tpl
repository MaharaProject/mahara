{* Tabs and beginning of page container for group info pages *}
                <div id="grouppage-tabs">
                <ul>
                {foreach from=$grouptabs key=tab item=tabinfo}
                    <li{if $current == $tab} class="current"{/if}><a href="{$WWWROOT}{$tabinfo.url}">{$tabinfo.title}</a></li>
                {/foreach}
                </ul>
                </div>
{*
{if $groupviews && $member}
  <ul id="groupviewoptions">
    <li{if !$shared} class="current"{/if}>
      <a href="groupviews.php?group={$groupid}">{str tag="viewsownedbygroup" section="view"}</a>
    </li>
    <li{if $shared} class="current"{/if}>
      <a href="groupviews.php?group={$groupid}&shared=1">{str tag="viewssharedtogroup" section="view"}</a>
    </li>
  </ul>
{/if}
*}
                <div id="grouppage-container">
