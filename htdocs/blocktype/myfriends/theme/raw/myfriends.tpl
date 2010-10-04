<div class="friends">
{if $friends}
    <table id="userfriendstable" class="center fullwidth">
      <tbody>
      {$friends.tablerows|safe}
      </tbody>
    </table>
	<div id="myfriends_page_container" class="hidden">{$friends.pagination|safe}</div>
<script>
addLoadEvent(function() {literal}{{/literal}
    {$friends.pagination_js|safe}
    removeElementClass('myfriends_page_container', 'hidden');
{literal}}{/literal});
</script>
{else}
    {if $lookingatownpage}
        <div class="message">{str tag="trysearchingforfriends" section=group args=$searchingforfriends}</div>
    {else}
        {if $relationship == 'none' && $friendscontrol == 'auto'}
            <div class="message">{$newfriendform|safe}</div>
        {elseif $relationship == 'none' && $friendscontrol == 'auth'}
            <div class="message"><a href="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view">{str tag='requestfriendship' section='group'}</a></div>
        {elseif $relationship == 'requestedfriendship'}
            <div class="message">{str tag=friendshiprequested section=group}</div>
        {/if}
        {* Case not covered here: friendscontrol disallows new users. The block will appear empty. *}
    {/if}
{/if}
</div>
