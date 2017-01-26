{include file="header.tpl"}

{if $file}
    {if $zipinfo}
    <h3>{$file->get('title')}</h3>
    <div class="row">
        <div class="col-md-6">
            <p class="detail">
                <strong>{str tag=Files section=artefact.file}:</strong> 
                {$zipinfo->files}
                <br>
                <strong>{str tag=Folders section=artefact.file}:</strong>
                {$zipinfo->folders}
                <br>
                <strong>{str tag=spacerequired section=artefact.file}:</strong>
                {$zipinfo->displaysize}
            </p>
            {if $quotaerror}
                {$quotaerror|safe}
            {else}
            <div class="alert alert-info">
                {$message}
            </div>
            {/if}
            {$form|safe}
        </div>
        <div class="col-md-6">
            <div class="extract-files">
                <h4>{str tag=Contents section=artefact.file}:</h4>
                <ul class="list-group list-group-unbordered text-small">
                {foreach from=$zipinfo->names item=name}
                    <li class="list-group-item">{$name}</li>
                {/foreach}
                </ul>
            </div>
        </div>
    </div>
    {/if}
{/if}

{include file="footer.tpl"}
