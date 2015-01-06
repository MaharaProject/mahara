{include file="header.tpl"}
            <div class="rbuttons">
                <form method="post" action="{$WWWROOT}admin/site/font/install.php">
                    <input type="submit" class="submit" value="{str tag=installfont section=skin}">
                </form>
                <form method="post" action="{$WWWROOT}admin/site/font/installgwf.php">
                    <input type="submit" class="submit" value="{str tag=installgwfont section=skin}">
                </form>
            </div>

            <p>{str tag=sitefontsdescription section=skin}</p>
{$form|safe}
{if $sitefonts}
        {if $query}
            <h2 id="searchresultsheading" class="accessible-hidden">{str tag=Results}</h2>
        {/if}
            <div id="fontlist" class="fullwidth listing">
                {foreach from=$sitefonts item=font}
                    <div class="{cycle values='r0,r1'} listrow">
                        <h3 class="title">{$font.title}</h3>
                        <ul class="actionlist">
                            <li class="notbtn">{str tag="fonttype.$font.fonttype" section="skin"}</li>
                            {if $font.fonttype == 'google'}
                            <li><a class="btn-display" href="javascript:" onclick="window.open('http://www.google.com/webfonts/specimen/{$font.urlencode}','specimen','width=700,height=800,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,copyhistory=yes,resizable=no')">{str tag="viewfontspecimen" section="skin"}<span class="accessible-hidden">{str tag=viewfontspecimenfor section=skin arg1=$font.title}</span></a></li>
                            {else}
                            <li><a class="btn-display" href="javascript:" onclick="window.open('{$WWWROOT}admin/site/font/specimen.php?font={$font.name}','specimen','width=700,height=800,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,copyhistory=yes,resizable=no')">{str tag="viewfontspecimen" section="skin"}<span class="accessible-hidden">{str tag=viewfontspecimenfor section=skin arg1=$font.title}</span></a></li>
                            {/if}
                            {if $font.fonttype == 'site'}<li><a class="btn-edit" href="{$WWWROOT}admin/site/font/edit.php?font={$font.name}">{str tag="editproperties" section="skin"}<span class="accessible-hidden">{str tag=viewfontspecimenfor section=skin arg1=$font.title}</span></a></li>{/if}
                            {if $font.fonttype == 'site'}<li><a class="btn-add" href="{$WWWROOT}admin/site/font/add.php?font={$font.name}">{str tag="addfontvariant" section="skin"}<span class="accessible-hidden">{str tag=viewfontspecimenfor section=skin arg1=$font.title}</span></a></li>{/if}
                            <li><a class="btn-del" href="{$WWWROOT}admin/site/font/delete.php?font={$font.name}">{str tag=deletefont section=skin} <span class="accessible-hidden">{$font.title}</span></a></li>
                        </ul>
                        <div style="font-family:'{$font.title|escape_css_string}';font-size:{$size}pt;line-height:{$size}pt;padding:3px 0;">
                            {if $preview == 10}{$font.title}{/if}
                            {if $preview >= 11}{str tag="sampletext$preview" section="skin"}{/if}
                        </div>
                        <div class="cb"></div>
                    </div>
                {/foreach}
            </div>
{else}
            <div class="message">{str tag="nofonts" section="skin"}</div>
{/if}
{$pagination|safe}
{include file="footer.tpl"}