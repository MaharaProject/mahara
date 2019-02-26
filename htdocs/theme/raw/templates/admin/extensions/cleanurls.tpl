{include file='header.tpl'}

<div class="row">
	<div class="col-lg-9">
		<div class="card card">
			<div class="card-body">
				{str tag=cleanurlsdescription section=admin}
			</div>
		</div>

		{if $cleanurls}
		<div class="card card">
			<h3 class="card-heading">{str tag=cleanurlsettings section=admin}</h3>
			<table class="table">
				{foreach from=$cleanurlconfig key=key item=item}
				<tr><td>$cfg->{$key}:</td><td>{$item}</td></tr>
				{/foreach}
			</table>
			<div class="card-body">
				{$regenerateform|safe}
			</div>
		</div>
		{/if}
	</div>
</div>
{include file='footer.tpl'}
