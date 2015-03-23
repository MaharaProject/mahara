{loadquota}
<div class="panel panel-default">
    <h3 class="panel-heading">
        {str tag="quota"} 
        <span class="pull-right">
        {contextualhelp plugintype='artefact' pluginname='file' section='quota_message'}
        </span>
    </h3>
    <div class="sidebar-content panel-body">
        <p id="quota_message">
            {$QUOTA_MESSAGE|safe}
        </p>
        <div id="quotawrap" class="progress">
            {if $QUOTA_PERCENTAGE < 100}
                <div id="quota_fill" class="progress-bar" role="progress-bar" aria-valuenow="{$QUOTA_PERCENTAGE}" aria-valuemin="0" arai-valuemax="0" style="width: {$QUOTA_PERCENTAGE*2}%;">{$QUOTA_PERCENTAGE}%</div>
            {else}
                <div id="quota_fill" style="display: none; width: 200px;">&nbsp;</div>
                <p id="quota_bar_100">
                    <span id="quota_percentage">{$QUOTA_PERCENTAGE}%</span>
                </p>
            {/if}
        </div>
    </div>
</div>