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
<div id="siteoptions_applying" class="modal modal-docked active d-none">
  <div class="applying-box loading-inner navbar-default">
    <span class="icon-spinner icon-pulse icon icon-lg"></span>
    {str tag=applyingchanges section=admin}
  </div>
</div>
{include file="footer.tpl"}
