{loadquota}
    <div class="sidebar-header panel-heading"><h3>{str tag="quota"} 
        {contextualhelp plugintype='artefact' pluginname='file' section='quota_message'}</h3></div>
    <div class="sidebar-content panel-body">
    <p id="quota_message">
        {$QUOTA_MESSAGE|safe}
    </p>
    <div id="quotawrap" class="progress">
{if $QUOTA_PERCENTAGE < 100}
    <div id="quota_fill" class="progress-bar" role="progress-bar" aria-valuenow="{$QUOTA_PERCENTAGE}" aria-valuemin="0" arai-valuemax="0" style="width: {$QUOTA_PERCENTAGE*2}%;">{$QUOTA_PERCENTAGE}%</div>
<!--     <p id="quota_bar">
        <span id="quota_percentage">{$QUOTA_PERCENTAGE}%</span>
    </p> -->
{else}
    <div id="quota_fill" style="display: none; width: 200px;">&nbsp;</div>
    <p id="quota_bar_100">
        <span id="quota_percentage">{$QUOTA_PERCENTAGE}%</span>
    </p>
{/if}
	</div>
</div>
