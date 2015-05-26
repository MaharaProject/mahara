<div class="js-blockinstance blockinstance panel panel-secondary {if $configure} configure{elseif $retractable} retractable{/if}" data-id="{$id}" id="blockinstance_{$id}{if $configure}_configure{/if}">
	<h3 class="panel-heading js-heading drag-handle {if !$title}panel-heading-placeholder{/if}">
		<span class="fa fa-arrows move-indicator"></span>
		<span class="blockinstance-header">
			{if $configure}{$configtitle}: {str tag=Configure section=view}{else}{$title|default:"[$strnotitle]"}{/if}
		</span>
		<span class="blockinstance-controls">

			<button class="keyboardmovebutton btn btn-default hidden sr-only" name="action_moveblockinstance_id_{$id}" alt="{$strmovetitletext}"  data-id="{$id}">
				{$strmovetitletext}
			</button>


			{foreach from=$movecontrols item=item}
				<button class="movebutton hidden" name="action_moveblockinstance_id_{$id}_row_{$row}_column_{$item.column}_order_{$item.order}" data-id="{$id}">
					{$item.title}
				</button>
			{/foreach}


			<span class="pull-right btn-group btn-group-top">

				{if $retractable && !$configure}
				<button alt="{str tag='retractable' section='view'}" title="{str tag='retractable' section='view'}" class="retractablebtn btn btn-inverse btn-xs" data-id="{$id}">
					<span class="fa fa-chevron-down"><span>
				</button>
				{/if}

				{if $configurable && !$configure}
				<button class="configurebutton btn btn-inverse btn-xs" name="action_configureblockinstance_id_{$id}" alt="{$strconfigtitletext}" data-id="{$id}">
					<span class="fa fa-cog"></span>
				</button>
				{/if}

				{if $configure}
					<button class="deletebutton btn btn-inverse btn-xs" name="action_removeblockinstance_id_{$id}" alt="{str tag=Close}" data-id="{$id}">
						<span class="fa fa-trash text-danger"></span>
					</button>
				</button>
				{else}
					<button class="deletebutton btn btn-inverse btn-xs" name="action_removeblockinstance_id_{$id}" alt="{$strremovetitletext}" data-id="{$id}">
						<span class="fa fa-trash text-danger"></span>
					</button>
				{/if}
			</span>
		</span>
	</h3>
	<div class="block blockinstance-content js-blockinstance-content">
		{$content|safe}
	</div>
</div>
