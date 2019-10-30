{include file="header.tpl"}

<div class="center">
    <p class="alert alert-warning">{str tag=confirmconversionmessage section=view arg1=$accountprefsurl}</p>
    <form action="{$formurl}" method="post" class="pieform form-inline">
        <input type="hidden" id="viewid" name="id" value="{$viewid}">
        <input type="hidden" name="sesskey" value="{$SESSKEY}">
        <input type="hidden" name="alwaystranslate" value="true">
        <div>
            <input class="submit btn btn-primary" type="submit" value="{str tag=dontaskagain section=view}">
            <a class="btn-secondary submitcancel submit btn" href="{$WWWROOT}view/blocks.php?id={$viewid}&translate=true">
              {str tag="accept"}
            </a>
            <a class="btn-secondary submitcancel cancel btn" href="{$WWWROOT}view/view.php?id={$viewid}">
                {str tag="cancel"}
            </a>
        </div>
    </form>
</div>

{include file="footer.tpl"}
