    <div class="blockinstance cb bt-{$blocktype}{if $retractable} retractable{/if}" id="blockinstance_{$id}">
        {if $title}<div class="blockinstance-header{if $retractable && $retractedonload} retracted{/if}">
            {if $retractable}
                <span class="arrow retractor"></span>
            {/if}
            <h2 class="title"><!-- (Adding some newlines within HTML comments to improve formatting without adding rendered whitespace)
              -->{if $retractable}<span class="retractor">{/if}<!--
              -->{$title}<!--
              -->{if $retractable}</span>{/if}<!--
              -->{if $viewartefacturl} <a href="{$viewartefacturl}" title="{str tag=clickformoreinformation section=view}"><!--
                --><img alt="{str tag=detailslinkalt section=view}" src="{theme_url filename='images/detail_small.png'}" /><!--
              --></a>{/if}<!--
              -->{if $feedlink} <a href="{$feedlink}"><!--
                --><img class="feedicon" src="{theme_url filename='images/feed.png'}"><!--
              --></a>{/if}<!--
            --></h2>
            <span class="cb"></span>
        </div>{/if}
        <div id="blockinstance-content-{$id}" class="blockinstance-content{if $retractable && $retractedonload} js-hidden{/if}">
            {if $content}
                {$content|safe}
            {else}
                <img src="{theme_url filename="images/loading.gif"}" />
                <script type="text/javascript">
                    jQuery("div#blockinstance-content-{$id}").load("{$WWWROOT}blocktype/blocktype.ajax.php?blockid={$id}");
                </script>
            {/if}
        </div>
    </div>
    {if $retractable}
        <script>
            {include file="view/retractablejs.tpl" id=$id}
        </script>
    {/if}
