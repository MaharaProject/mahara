{include file="header.tpl" headertype="progresscompletion"}

<p>{$description|clean_html|safe}</p>

<div class="card">
    <div class="card-body">
        <p id="quota_message">
            {$quotamessage|safe}
        </p>
        <div id="quotawrap" class="progress">
            <div id="quota_fill" class="progress-bar {if $signoffpercentage < 11}small-progress{/if}" role="progressbar" aria-valuenow="{if $signoffpercentage }{$signoffpercentage}{else}0{/if}" aria-valuemin="0" aria-valuemax="100" style="width: {$signoffpercentage}%;">
                <span>{$signoffpercentage}%</span>
            </div>
        </div>
    </div>
</div>


<div role="dialog" id="configureblock" class="modal modal-shown modal-docked-right modal-docked closed blockinstance configure">
    <div class="modal-dialog modal-lg">
        <div data-height=".modal-body" class="modal-content">
            <div class="modal-header">
                <button name="close_configuration" class="deletebutton close">
                    <span class="times">×</span>
                    <span class="sr-only">{str tag='closeconfiguration' section='view'}</span>
                </button>
                <h4 class="modal-title blockinstance-header text-inline"></h4>
                <span aria-hidden="true" role="presentation" class="icon icon-cogs icon-2x float-right"></span>
            </div>
            <div class="modal-body blockinstance-content">
            </div>
        </div>
    </div>
</div>
{include file="footer.tpl"}
