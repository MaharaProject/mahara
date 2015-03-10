{include file="header.tpl"}
<p class="lead">{str tag=sharedviewsdescription section=view}</p>
<div class="pbl ptl">{$searchform|safe}</div>
<div class="panel panel-default mtl">
    <div class="panel-body">
        <div class="table-responsive">
            <table id="sharedviewlist" class="fullwidth table">
                <thead>
                    <tr>
                        <th>{str tag=name}</th>
                        <th class="center">{str tag=Comments section=artefact.comment}</th>
                        <th>{str tag=lastcomment section=artefact.comment}</th>
                    </tr>
                </thead>
                <tbody>
                    {include file="view/sharedviewrows.tpl"}
                </tbody>
            </table>
        </div>
    </div>
</div>
{$pagination|safe}
{include file="footer.tpl"}
