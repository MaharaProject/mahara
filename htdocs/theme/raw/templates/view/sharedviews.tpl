{include file="header.tpl"}
{$searchform|safe}
<p class="lead view-description">{str tag=sharedviewsdescription section=view}</p>

<div class="view-container">
    <table id="sharedviewlist" class="fullwidth table">
        <thead>
            <tr>
                <th>{str tag=Title}</th>
                <th class="text-center">{str tag=Comments section=artefact.comment}</th>
                <th>{str tag=lastcomment section=artefact.comment}</th>
            </tr>
        </thead>
        <tbody>
            {include file="view/sharedviewrows.tpl"}
        </tbody>
    </table>
</div>
{$pagination|safe}
{include file="footer.tpl"}
