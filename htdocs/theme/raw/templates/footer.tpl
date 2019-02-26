                    </div><!-- end main-column -->

                </div><!-- mainmiddle -->

            </main>

            {if $SIDEBARS && $SIDEBLOCKS.right}
            <div class="col-lg-3 sidebar">
                    {include file="sidebar.tpl" blocks=$SIDEBLOCKS.right}
            </div>
            {/if}

            {if $SIDEBARS && $SIDEBLOCKS.left}
            <div class="col-lg-3 order-md-1 sidebar">
                            {include file="sidebar.tpl" blocks=$SIDEBLOCKS.left}
            </div>
            {/if}

        </div><!-- row -->

    </div><!-- container -->

<footer class="{if $editing == true}editcontent{/if} footer">
    <div class="footer-inner container">
        <div id="powered-by" class="float-left mahara-logo">
            <a href="https://mahara.org/">
                <img src="{theme_image_url filename='powered_by_mahara'}?v={$CACHEVERSION}" alt="Powered by Mahara" class="mahara-footer-logo">
            </a>
        </div>
        <!-- This site is powered by Mahara, an Open Source
        ePortfolio system. See https://mahara.org/ for more
        details.
        NOTE: this image and link are a way that you can
        support the Mahara project. Please consider
        displaying them on your site to spread the word! -->
        <ul class="nav nav-pills footer-nav float-left">
        {foreach from=$FOOTERMENU item=item name=footermenu}
            <li>
                {if $item.fullurl}
                {$item.fullurl|safe}
                {else}
                <a href="{$item.url}" class="nav-link">{$item.title}</a>
                {/if}
            </li>
        {/foreach}
        </ul>
        <div class="metadata float-right mahara-version" id="version">
            {mahara_version}
        </div>

        <div class="metadata text-center fullwidth site-performace">
            <!-- there is a div id="performance-info" wrapping this -->
            {mahara_performance_info}
        </div>
    </div>
</footer><!-- footer-wrap -->
{if $ADDITIONALHTMLFOOTER}{$ADDITIONALHTMLFOOTER|safe}{/if}
</body>
</html>
