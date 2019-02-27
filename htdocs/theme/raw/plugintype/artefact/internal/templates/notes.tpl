{include file="header.tpl"}
<p class="lead">{str tag=notesdescription1 section=artefact.internal}</p>
<div class="table-responsive">
    <table id="notes" class="table">
        <thead>
            <tr>
                <th>{str tag=noteTitle section=artefact.internal}</th>
                <th>{str tag=blockTitle section=artefact.internal}</th>
                <th>{str tag=containedin section=artefact.internal}</th>
                <th>
                    <span class="icon icon-lg icon-paperclip" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">
                        {str tag=Attachments section=artefact.resume}
                    </span>
                </th>
                <th>
                    <span class="accessible-hidden sr-only">
                        {str tag=edit}
                    </span>
                </th>
            </tr>
        </thead>
        <tbody>
            {$datarows|safe}
        </tbody>
    </table>
</div>
{$pagination|safe}
{if $pagination_js}
    <script>
    {$pagination_js|safe}
    </script>
{/if}
{include file="footer.tpl"}
