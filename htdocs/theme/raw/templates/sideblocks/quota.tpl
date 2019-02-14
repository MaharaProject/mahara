{loadquota}
<div class="card">
    <h3 class="card-header">
        {str tag="quota"}
        <span class="float-right">
        {contextualhelp plugintype='artefact' pluginname='file' section='quota_message'}
        </span>
    </h3>
    <div class="card-body">
        <p id="quota_message">
            {$QUOTA_MESSAGE|safe}
        </p>
        <div id="quotawrap" class="progress">
            <div id="quota_fill" class="progress-bar {if $QUOTA_PERCENTAGE < 11}small-progress{/if}" role="progressbar" aria-valuenow="{if $QUOTA_PERCENTAGE }{$QUOTA_PERCENTAGE}{else}0{/if}" aria-valuemin="0" aria-valuemax="100" style="width: {$QUOTA_PERCENTAGE}%;">
                <span>{$QUOTA_PERCENTAGE}%</span>
            </div>
        </div>
    </div>
</div>
