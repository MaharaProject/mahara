{if $SUBPAGENAV}
                        </div><!--end subpage rel-->
{/if}
                    </div>
                </div>
{if $SIDEBARS && $SIDEBLOCKS.right}
                <div id="right-column" class="sidebar">
{include file="sidebar.tpl" blocks=$SIDEBLOCKS.right}
                </div>
{/if}
				<div class="cb"></div>
            </div>
    <div id="footer-wrap">
		<div class="cb"></div>
        <div id="powered-by"><a href="http://mahara.org/"><img src="{theme_url filename='images/powered-by-mahara.png'}" border="0" alt=""></a></div>
        <!-- This site is powered by Mahara, an Open Source
             ePortfolio system. See http://mahara.org/ for more
             details.
             NOTE: this image and link are a way that you can
             support the Mahara project. Please consider
             displaying them on your site to spread the word! -->
        <div id="footernav">
        {foreach from=$FOOTERMENU item=item name=footermenu}
          {if !$.foreach.footermenu.first}| {/if}<a href="{$WWWROOT}{$item.url}">{$item.title}</a>
        {/foreach}
        </div>
		<!-- there is a div id="performance-info" wrapping this -->{mahara_performance_info}
		<div id="version">{mahara_version}</div>
		<div class="cb"></div>
    </div>
</div>
</body>
</html>
