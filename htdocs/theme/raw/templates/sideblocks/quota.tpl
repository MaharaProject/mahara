{loadquota}
<div class="panel panel-default">
    <h3 class="panel-heading">
        {str tag="quota"}
        <span class="pull-right">
        {contextualhelp plugintype='artefact' pluginname='file' section='quota_message'}
        </span>
    </h3>
    <div class="panel-body">
        <p id="quota_message">
            {$QUOTA_MESSAGE|safe}
        </p>
        <div id="quotawrap" class="progress">
            <div id="quota_fill" class="progress-bar" role="progressbar" aria-valuenow="{if $QUOTA_PERCENTAGE }{$QUOTA_PERCENTAGE}{else}0{/if}" aria-valuemin="0" aria-valuemax="100" style="width: {$QUOTA_PERCENTAGE}%;">
                {$QUOTA_PERCENTAGE}%
            </div>
        </div>
    </div>
</div>
