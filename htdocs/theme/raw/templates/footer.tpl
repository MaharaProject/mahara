{if $GROUP}
                        </div><!--end group-->
{/if}
                    </div>
                </td>
{if $SIDEBARS && $SIDEBLOCKS.right}
                <td id="right-column" class="sidebar">
{include file="sidebar.tpl" blocks=$SIDEBLOCKS.right}
                </td>
{/if}
            </tr>
        </tbody>
    </table>
    <div id="footer-wrap">
        <div id="powered-by"><a href="http://mahara.org/"><img src="{theme_url filename='images/powered-by-mahara.png'}" border="0" alt=""></a></div>
        <!-- This site is powered by Mahara, an Open Source
             ePortfolio system. Mahara is (C) 2006-2009 Catalyst IT
             Ltd. See http://mahara.org/ for more details.
             NOTE: this image and link are a way that you can
             support the Mahara project. Support us and we'll
             support you! If you remove this image and link, you
             may not receive support in the Mahara forums -->
        <div id="footernav"><a href="{$WWWROOT}terms.php">{str tag=termsandconditions}</a> | 
        <a href="{$WWWROOT}privacy.php">{str tag=privacystatement}</a> | 
        <a href="{$WWWROOT}about.php">{str tag=about}</a> | 
        <a href="{$WWWROOT}contact.php">{str tag=contactus}</a></div>
		<div id="performanceinfo">{mahara_performance_info}</div>
		<div id="version">{mahara_version}</div>
    </div>
</div>
</body>
</html>
