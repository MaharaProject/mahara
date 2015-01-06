          {if $SUBPAGENAV}
            </div><!--end subpage rel-->
          {/if}
        </div><!-- end main-column-container -->
      </div><!-- end main-column -->

      </div><!-- mainmiddle -->

      {if $SIDEBARS && $SIDEBLOCKS.right}
          <div class="sidebar col-md-3">
              {include file="sidebar.tpl" blocks=$SIDEBLOCKS.right}
          </div>
      {/if}

      {if $SIDEBARS && $SIDEBLOCKS.left}
          <div class="sidebar col-md-3 col-md-pull-9">
              {include file="sidebar.tpl" blocks=$SIDEBLOCKS.left}
          </div>
      {/if}

    

    <footer id="footer" class="{if $editing == true}editcontent{/if} footer row">
      <div class="col-md-12">
        <div id="powered-by">
            <a href="http://mahara.org/">
                <img src="{theme_url filename='images/powered_by_mahara.png'}" border="0" alt="Powered by Mahara">
            </a>
        </div>
        <!-- This site is powered by Mahara, an Open Source
             ePortfolio system. See http://mahara.org/ for more
             details.
             NOTE: this image and link are a way that you can
             support the Mahara project. Please consider
             displaying them on your site to spread the word! -->
        <div class="nav" id="footer-nav">
        {foreach from=$FOOTERMENU item=item name=footermenu}
          {if !$.foreach.footermenu.first}
              <span class="bar-before">{/if}
                <a href="{$item.url}">{$item.title}</a>
                {if !$.foreach.footermenu.first}
            </span>
            {/if}
        {/foreach}
        </div> 
        <!-- there is a div id="performance-info" wrapping this -->{mahara_performance_info}
        <div id="version">{mahara_version}</div>
      </div>
    </footer><!-- footer-wrap -->


    {if $ADDITIONALHTMLFOOTER}{$ADDITIONALHTMLFOOTER|safe}{/if}
</body>
</html>
