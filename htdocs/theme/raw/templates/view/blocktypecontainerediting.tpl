<div class="js-blockinstance blockinstance panel panel-secondary clearfix {if $configure} configure{elseif $retractable} retractable{/if}" data-id="{$id}" id="blockinstance_{$id}{if $configure}_configure{/if}">
	<h3 class="panel-heading js-heading drag-handle {if !$title}panel-heading-placeholder{/if}">
		<span class="icon icon-arrows move-indicator" role="presentation" aria-hidden="true"></span>
		<span class="blockinstance-header">
			{if $configure}{$configtitle}: {str tag=Configure section=view}{else}{$title|default:"[$strnotitle]"}{/if}

			{if $retractable && !$configure}
			<span class="icon icon-chevron-down text-watermark icon-sm" role="presentation" aria-hidden="true"></span>
			<span class="sr-only">{str tag=retractable section=view}</span>
			{/if}
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
				{if $configurable && !$configure}
				<button class="configurebutton btn btn-inverse btn-xs" name="action_configureblockinstance_id_{$id}" alt="{$strconfigtitletext}" data-id="{$id}">
					<span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span>
				</button>
				{/if}

				{if $configure}
					<button class="deletebutton btn btn-inverse btn-xs" name="action_removeblockinstance_id_{$id}" alt="{str tag=Close}" data-id="{$id}">
						<span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
					</button>
				</button>
				{else}
					<button class="deletebutton btn btn-inverse btn-xs" name="action_removeblockinstance_id_{$id}" alt="{$strremovetitletext}" data-id="{$id}">
						<span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
					</button>
				{/if}
			</span>
		</span>
	</h3>
	<div class="block blockinstance-content js-blockinstance-content">
		{$content|safe}
	</div>
</div>
