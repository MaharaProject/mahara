<div id="home-info-container">
    {if $USER->is_logged_in()}
        <div id="hideinfo" class="nojs-hidden-block">
            <a href="#" title="{str tag=Hide2}">
                <img src="{theme_image_url filename='btn_close'}" alt="{str tag=Close}" />
            </a>
        </div>
    {/if}
    <table class="home-info-boxes{if $USER->is_logged_in()} loggedin{/if} fullwidth">
    <tr>
        <td>{if $USER->is_logged_in()}<a href="{$url.views}" title="{str tag=create}">{/if}<div id="home-info-create" class="home-info-box">
            <h2 class="title">{str tag=create}</h2>
            <p>{str tag=createsubtitle}</p>
            <div class="mask"><div class="detail">{str tag=createdetail}</div></div>
            </div>{if $USER->is_logged_in()}</a>{/if}
        </td>
        <td>{if $USER->is_logged_in()}<a href="{$url.share}">{/if}<div id="home-info-share" class="home-info-box">
            <h2 class="title">{str tag=share}</h2>
            <p>{str tag=sharesubtitle}</p>
            <div class="mask"><div class="detail">{str tag=sharedetail}</div></div>
            </div>{if $USER->is_logged_in()}</a>{/if}
        </td>
        <td>{if $USER->is_logged_in()}<a href="{$url.groups}">{/if}<div id="home-info-engage" class="home-info-box">
            <h2 class="title">{str tag=engage}</h2>
            <p>{str tag=engagesubtitle}</p>
            <div class="mask"><div class="detail">{str tag=engagedetail}</div></div>
            </div>{if $USER->is_logged_in()}</a>{/if}
        </td>
    </tr>
    </table>
</div>
