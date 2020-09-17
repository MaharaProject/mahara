{include file="header.tpl"}

<div class="btn-top-right btn-group btn-group-top">
    <form method="post" action="{$WWWROOT}admin/site/font/install.php" class="btn-group">
        <input class="submit btn btn-secondary" type="submit" value="{str tag=installfont section=skin}">
    </form>
    <form method="post" action="{$WWWROOT}admin/site/font/installgwf.php" class="btn-group">
        <input class="submit btn btn-secondary" type="submit" value="{str tag=installgwfont section=skin}">
    </form>
</div>
{$form|safe}
<p class="lead">{str tag=sitefontsdescription section=skin}</p>
{if $sitefonts}
    {if $query}
        <h2 id="searchresultsheading" class="accessible-hidden sr-only">{str tag=Results}</h2>
    {/if}
    <div id="fontlist" class="card fullwidth listing">
        {$sitefontshtml|safe}
    </div>
{else}
<p class="no-results lead">
    {str tag="nofonts" section="skin"}
</p>
{/if}


{$pagination|safe}
{if $pagination_js}
    <script>
    {$pagination_js|safe}
    $(document).on('pageupdated', function(e, data) {
        wire_specimens();
    });
    wire_specimens();
    </script>
{/if}

{include file="pagemodal.tpl"}
{include file="footer.tpl"}
