{loadgroupquota}
<div class="panel panel-default">
    <h3 class="panel-heading">
        {str tag="groupquota"}
        <span class="pull-right">
            {contextualhelp plugintype='artefact' pluginname='file' section='groupquota_message'}
        </span>
    </h3>

    <div class="panel-body">
        <p id="quota_message">
            {$GROUPQUOTA_MESSAGE|safe}
        </p>
        <div id="quotawrap" class="progress">
            <div id="quota_fill" class="progress-bar {if $GROUPQUOTA_PERCENTAGE < 11}small-progress{/if}" role="progressbar" aria-valuenow="{if $GROUPQUOTA_PERCENTAGE }{$GROUPQUOTA_PERCENTAGE}{else}0{/if}" aria-valuemin="0" aria-valuemax="100" style="width: {$GROUPQUOTA_PERCENTAGE}%;">
                <span>{$GROUPQUOTA_PERCENTAGE}%</span>
            </div>
       </div>
    </div>
</div>
