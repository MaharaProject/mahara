<div class="mtxl mbxl">
	<div class="pull-right">
		{contextualhelp plugintype='core' pluginname='view' section='viewmenu'}
	</div>
	{if $enablecomments || $LOGGEDIN}
	<ul class="nav nav-tabs ">
		{if $enablecomments}
			<li class="active">
				<a id="add_feedback_link" class="feedback" href="#comment-form" aria-controls="comment-form" role="tab" data-toggle="tab">
					<span class="fa fa-lg fa-comments prm"></span>
					{str tag=Comment section=artefact.comment}
				</a>
			</li>
		{/if}
		{if $LOGGEDIN}
			<li>
				<a id="objection_link" class="objection " href="#report-form" role="tab" aria-controls="report-form" data-toggle="tab">
						<span class="fa fa-lg fa-flag prs"></span>
						{str tag=reportobjectionablematerial}
				</a>
			</li>
		{/if}
	</ul>
	{/if}
</div>