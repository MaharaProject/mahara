{if $SIDEBLOCKS.left && $SIDEBLOCKS.right}
{if $THEME->columnwidthunits == 'pixels'}                </div>
{/if}
            </div>
            <div class="col2">
{include file="sidebar.tpl" blocks=$SIDEBLOCKS.left}
            </div>
            <div class="col3">
{include file="sidebar.tpl" blocks=$SIDEBLOCKS.right}
            </div>
        </div>
    </div>
</div>
{elseif $SIDEBLOCKS.left}
{if $THEME->columnwidthunits == 'pixels'}            </div>
{/if}
        </div>
        <div class="col2">
{include file="sidebar.tpl" blocks=$SIDEBLOCKS.left}
        </div>
    </div>
</div>
{elseif $SIDEBLOCKS.right}
{if $THEME->columnwidthunits == 'pixels'}            </div>
{/if}
        </div>
        <div class="col2">
{include file="sidebar.tpl" blocks=$SIDEBLOCKS.right}
        </div>
    </div>
</div>
{else}
    TODO: 1 column layout
{/if}
    </div>
    <div id="footerwrap">
        <div id="poweredby"><a href="http://mahara.org/"><img src="{theme_url filename='images/powered-by-mahara.png'}" border="0" alt=""></a></div>
        <!-- This site is powered by Mahara, an Open Source
             ePortfolio system. Mahara is (C) 2006-2009 Catalyst IT
             Ltd. See http://mahara.org/ for more details.
             NOTE: this image and link are a way that you can
             support the Mahara project. Support us and we'll
             support you! If you remove this image and link, you
             may not receive support in the Mahara forums -->
        <p><a href="{$WWWROOT}terms.php">{str tag=termsandconditions}</a> | 
        <a href="{$WWWROOT}privacy.php">{str tag=privacystatement}</a> | 
        <a href="{$WWWROOT}about.php">{str tag=about}</a> | 
        <a href="{$WWWROOT}contact.php">{str tag=contactus}</a></p>
{mahara_version}
{mahara_performance_info}
    </div>
</div>
</body>
</html>
