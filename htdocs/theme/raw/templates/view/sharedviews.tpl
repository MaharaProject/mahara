{include file="header.tpl"}
{include file='modal-details.tpl'}
{$searchform|safe}
<p class="lead view-description">{str tag=sharedviewsdescription section=view}</p>

<div class="view-container">
    <table id="sharedviewlist" class="fullwidth table">
        <thead>
            <tr>
                <th>{str tag=Title}</th>
                <th class="text-center">{str tag=Comments section=artefact.comment}</th>
                <th>{str tag=lastcomment section=artefact.comment}</th>
                {if $completionvisible}
                    <th>{str tag=completionpercentage section=collection} {$completionpercentagehelp|safe}</th>
                    <th>{str tag=review section=view} {$verificationhelp|safe}</th>
                {/if}
                {if $canremoveownaccess}
                    <th>{str tag=removemyaccess section=collection}</th>
                {/if}
            </tr>
        </thead>
        <tbody>
            {include file="view/sharedviewrows.tpl"}
        </tbody>
    </table>
</div>

<div class="modal fade" id="revokemyaccess-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">
                    <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                    {str tag=revokemyaccessformtitle section=collection}
                </h1>
            </div>
            <div class="modal-body">
            <div class="description">{str tag=revokemyaccessdescription section=collection}</div>
                {$revokemyaccessform|safe}
            </div>
        </div>
    </div>
</div>

{$pagination|safe}
{include file="footer.tpl"}
