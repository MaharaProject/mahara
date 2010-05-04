{auto_escape off}
{include file="header.tpl"}
<p>{str tag="institutionmemberspagedescription" section="admin"}</p>
<p>{$instructions}</p>
{$usertypeselector}
<div class="userlistform">
{$institutionusersform}
</div>
{include file="footer.tpl"}
{/auto_escape}
