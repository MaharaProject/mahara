          {if $SUBPAGENAV}
            </div><!--end subpage rel-->
          {/if}
      </div><!-- end main-column -->

      </div><!-- mainmiddle -->

      </div>

      {if $SIDEBARS && $SIDEBLOCKS.right}
          <div class="col-md-3 sidebar">
            {include file="sidebar.tpl" blocks=$SIDEBLOCKS.right}
          </div>
      {/if}

      {if $SIDEBARS && $SIDEBLOCKS.left}
          <div class="col-md-3 col-md-pull-9 sidebar">
              {include file="sidebar.tpl" blocks=$SIDEBLOCKS.left}
          </div>
      {/if}

     
      </div><!-- row -->

   </div><!-- container -->

    <footer class="{if $editing == true}editcontent{/if} footer container">
      <div class="row">
        <div class="col-md-12">
          <div id="powered-by" class="pull-left prl">
              <a href="http://mahara.org/">
                  <img src="{theme_url filename='images/powered_by_mahara.png'}" alt="Powered by Mahara">
              </a>
          </div>
          <!-- This site is powered by Mahara, an Open Source
               ePortfolio system. See http://mahara.org/ for more
               details.
               NOTE: this image and link are a way that you can
               support the Mahara project. Please consider
               displaying them on your site to spread the word! -->
          <ul class="nav nav-pills footer-nav">
          {foreach from=$FOOTERMENU item=item name=footermenu}
            <li class="">
              <a href="{$item.url}">{$item.title}</a>
            </li>
          {/foreach}
          </ul>
          <!-- there is a div id="performance-info" wrapping this -->
          {mahara_performance_info}
          <div id="version">{mahara_version}</div>
        </div>
        </div>
    </footer><!-- footer-wrap -->


    {if $ADDITIONALHTMLFOOTER}{$ADDITIONALHTMLFOOTER|safe}{/if}

</body>
</html>
