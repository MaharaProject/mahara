{include file="header.tpl"}
<div class="row">
	<div class="col-lg-9">
		<p class="lead">
		{str tag=siteoptionspagedescription section=admin}
		</p>
	</div>

	<div class="col-lg-9 as-card">
		{$siteoptionform|safe}
	</div>
</div>
{include file="footer.tpl"}
