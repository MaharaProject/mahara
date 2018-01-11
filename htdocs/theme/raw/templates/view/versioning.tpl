{include file="header.tpl"}

    <div class="grouppageswrap view-container">
        {if $views}
            <table class="table table-striped fullwidth listing">
                <tr>
                    <th> id </th>
                    <th> title </th>
                    <th> owner </th>
                    <th> institution </th>
                </tr>
                {$viewresults|safe}
            </table>
        {else}
            <div class="no-results">
                {str tag="youhavenoviews1" section="view"}
            </div>
        {/if}
    </div>

{include file="footer.tpl"}
