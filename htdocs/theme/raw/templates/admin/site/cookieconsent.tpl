{include file="header.tpl"}
    <div class="row">
        <div class="col-md-8">
              <div class="panel panel-default">
                 <h3 class="panel-heading">
                    {str tag=directive2009136 section=cookieconsent}
                 </h3>

                <div class="panel-body">
                    <p class="lead">{$introtext1|safe}</p>
                    <p>{$introtext2|safe}</p>
                    <blockquote class="small"><p>{$introtext3|safe}</p></blockquote>
                    <p>{$introtext4|safe}</p>
                    <p>{$introtext5|safe}</p>
                </div>
            </div>
            {$form|safe}
        </div>
        <div class="col-md-4">
              <div class="panel panel-default last">
                <h3 class="panel-heading">
                    <span class="icon-language icon left" role="presentation" aria-hidden="true"></span>
                    {str tag=readfulltext1 section=cookieconsent}
                </h3>
                <div class="panel-body" id="cookietext">

                    <p>{str tag=directive2009136 section=cookieconsent}</p>
                    <ul class="list-inline unstyled">
                        {foreach from=$languages item=lang name=languages}
                        <li class="list-tile">
                            <a href="http://eur-lex.europa.eu/LexUriServ/LexUriServ.do?uri=OJ:L:2009:337:0011:0036:{$lang}:PDF" title="{str tag=readdirective$lang section=cookieconsent}" class="link-thumb thumbnail">{$lang} <span class="metadata">.pdf</span></a>
                        </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>
    </div>


{include file="footer.tpl"}
