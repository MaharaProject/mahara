{include file='header.tpl'}

<div id="register-site-full">
<h1>{str tag=registeryourmaharasite section=admin}</h1>

{if get_config('new_registration_policy')}
  {str tag=newsiteregistrationpolicy section=admin}
{/if}
{if $register}
  {str tag=registeryourmaharasitedetail section=admin args=$WWWROOT}
  {$register|safe}
{else}
  {str tag=siteregistered section=admin args=$WWWROOT}
{/if}
</div>

{include file='footer.tpl'}
