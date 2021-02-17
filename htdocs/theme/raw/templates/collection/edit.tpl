{include file="header.tpl"}
{if $ADMIN || $INSTITUTIONALADMIN}
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">
                {$form|safe}
            </div>
        </div>
    </div>
</div>
{else}
{$form|safe}
{/if}
{if $institutionname}
    {if $updatingautocopytemplatewarning}
<div  class="modal fade" id="set-confirm-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p>
                    {$updatingautocopytemplatewarning}
                </p>
                <div class="btn-group default submitcancel form-group">
                    <button id="set-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                    <button id="set-cancel-button" type="button" class="submitcancel cancel">{str tag="cancel"}</button>
                </div>
            </div>
        </div>
    </div>
</div>
    {/if}
<div class="modal fade" id="unset-confirm-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p>
                    {$onlyactivetemplatewarning}
                </p>
                <div class="btn-group default submitcancel form-group">
                    <button id="unset-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                    <button id="unset-cancel-button" type="button" class="submitcancel cancel">{str tag="cancel"}</button>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}

{include file="footer.tpl"}
