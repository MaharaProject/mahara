{include file='header.tpl'}

<div id="register-site-full">
<h1> {if isset($PAGEICON)}
    <span class="{$PAGEICON}"></span>
    {/if}
    {str tag=registeryourmaharasite section=admin}
</h1>
<div class="panel panel-default col-md-9">
	<div class="panel-body ptxl">
		{if $register}
			{str tag=registeryourmaharasitedetail section=admin args=$WWWROOT}
			<button class="btn btn-default" type="button" data-toggle="collapse" data-target="#register_whatsent_container" aria-expanded="false" aria-controls="register_whatsent_container">
				<span class="icon icon-chevron-circle-down mrs"></span>
				{str tag=datathatwillbesent section=admin}
			</button>

			{$register|safe}
			
		{else}
			{str tag=siteregistered section=admin args=$WWWROOT}
		{/if}
	</div>
</div>
</div>

{include file='footer.tpl'}
