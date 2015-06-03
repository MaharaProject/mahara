<label class="accessible-hidden sr-only" for="selectusersexport_{$r.eid}">{str tag=selectuserexport section=admin arg1="$r.contentname"}</label>
<input name="selectusersexport" class="selectusersexport" type="checkbox" id="selectusersexport_{$r.eid}" value="{$r.eid}"{if $r.statustype eq 'pending'} disabled{/if}>
