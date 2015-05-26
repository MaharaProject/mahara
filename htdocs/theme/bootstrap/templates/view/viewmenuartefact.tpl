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

	<div class="text-right btn-top-right btn-group btn-group-top pull-right">
		{if $LOGGEDIN}
			<a id="toggle_watchlist_link" class="watchlist btn btn-sm btn-default" href="">

				{if $viewbeingwatched}
						<span class="fa fa-eye-slash prs"></span>
				{else}
						<span class="fa fa-eye prs"></span>
				{/if}

				{if $artefact}
					{if $viewbeingwatched}
						{str tag=removefromwatchlistartefact section=view arg1=$view->get('title')}
					{else}
						{str tag=addtowatchlistartefact section=view arg1=$view->get('title')}
					{/if}
				{else}
					{if $viewbeingwatched}
						{str tag=removefromwatchlist section=view}
					{else}
						{str tag=addtowatchlist section=view}
					{/if}
				{/if}
			</a>
		{/if}

		<a id="print_link" class="print btn btn-sm btn-default" href="" onclick="window.print(); return false;">
			<span class="fa fa-lg fa-print prs"></span> 
			{str tag=print section=view}
		</a>
	</div>
</div>