{include file="header.tpl"}
<div class="card card-body">
    <div class="rbuttons">
        <form method="post" action="{$WWWROOT}admin/site/font/install.php" class="btn-group">
            <input class="submit btn btn-primary" type="submit" value="{str tag=installfont section=skin}">
        </form>
        <form method="post" action="{$WWWROOT}admin/site/font/installgwf.php" class="btn-group">
            <input class="submit btn btn-primary" type="submit" value="{str tag=installgwfont section=skin}">
        </form>
    </div>
    <p>{str tag=sitefontsdescription section=skin}</p>
    {$form|safe}
    {if $sitefonts}
        {if $query}
            <h2 id="searchresultsheading" class="accessible-hidden sr-only">{str tag=Results}</h2>
        {/if}
        <div id="fontlist" class="card fullwidth listing">
            {$sitefontshtml|safe}
        </div>
    {else}
    <p class="no-results">
        {str tag="nofonts" section="skin"}
    </p>
    {/if}
</div>

{$pagination|safe}
{include file="footer.tpl"}
