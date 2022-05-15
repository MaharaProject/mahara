{if $r.submittedstatus > 1}
 {if $r.needrequeue}
     <span class="errmsg">{str tag=submittedpendingreleasefailed section=view arg1='$WWWROOT/admin/users/exportqueue.php'}</span>
 {else}
     {str tag=submittedpendingrelease section=view}
 {/if}
{else}
  <label class="accessible-hidden sr-only" for="selectcontentrelease_{$r.releaseid}">{str tag=selectuser section=admin arg1="$r.firstname $r.lastname"}</label>
  <input name="selectcontentrelease" class="selectcontentrelease" type="checkbox" id="selectcontentrelease_{$r.releaseid}" value="{$r.releaseid}" data-releasetype="{$r.releasetype}">
{/if}
