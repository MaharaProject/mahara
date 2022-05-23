{if $r.submittedstatus > 1}
 {str tag=submittedpendingrelease section=view}
{else}
  <label class="accessible-hidden visually-hidden" for="selectcontentrelease_{$r.releaseid}">{str tag=selectuser section=admin arg1="$r.firstname $r.lastname"}</label>
  <input name="selectcontentrelease" class="selectcontentrelease" type="checkbox" id="selectcontentrelease_{$r.releaseid}" value="{$r.releaseid}">
{/if}
