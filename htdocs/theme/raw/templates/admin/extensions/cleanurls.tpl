{include file='header.tpl'}

<div class="row">
	<div class="col-md-9">
		<div class="panel panel-default">
			<div class="panel-body">
				{str tag=cleanurlsdescription section=admin}
			</div>
		</div>

		{if $cleanurls}
		<div class="panel panel-default">
			<h3 class="panel-heading">{str tag=cleanurlsettings section=admin}</h3>
			<table class="table">
				{foreach from=$cleanurlconfig key=key item=item}
				<tr><td>$cfg->{$key}:</td><td>{$item}</td></tr>
				{/foreach}
			</table>
			<div class="panel-body">
				{$regenerateform|safe}
			</div>
		</div>
		{/if}
	</div>
</div>
{include file='footer.tpl'}

