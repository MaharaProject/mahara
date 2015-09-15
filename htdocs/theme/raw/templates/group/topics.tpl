{include file="header.tpl"}
<p class="lead">{str tag=activetopicsdescription section=interaction.forum}</p>
<div class="table-responsive">
    <table id="topiclist" class="table fullwidth table-padded">
        <thead>
            <tr>
                <th>{str tag=Topic section=interaction.forum}</th>
                <th>{str tag=lastpost section=interaction.forum}</th>
                <th class="text-center">{str tag=Posts section=interaction.forum}</th>
            </tr>
        </thead>
        <tbody>
            {include file="group/topicrows.tpl"}
        </tbody>
    </table>
</div>
<div id="topics_pagination">
    {$pagination|safe}
</div>
{include file="footer.tpl"}
