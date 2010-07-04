{include file='header.tpl'}

<div class="message" id="register-site">
<h3>{str tag=registeryourmaharasite section=admin}</h3>

{if $register}
  {str tag=registeryourmaharasitedetail section=admin args=$WWWROOT}
  {$register|safe}
{else}
  {str tag=siteregistered section=admin args=$WWWROOT}
{/if}
</div>

{include file='footer.tpl'}
