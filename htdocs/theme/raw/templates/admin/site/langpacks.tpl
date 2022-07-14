{include file="header.tpl"}

<div class="row">
    <div class="col-lg-9">
        <p class="lead">
            {str tag=languagepackdescription section=langpacks}
        </p>
    </div>
</div>
<div id="results" class="section card view-container">
    <h2 class="card-header" id="resultsheading">{str tag="Results"}</h2>
    {if $results}
        {$syncformopen|safe}
        <div class="table-responsive">
            <table id="searchresults" class="table table-striped fullwidth listing">
                <thead>
                    <tr>
                        {foreach from=$columns key=f item=c}
                            <th class="{if $c.class} {$c.class}{/if}">
                                {$c.name}
                                {if $c.accessible}
                                    <span class="accessible-hidden visually-hidden">{$c.accessible}</span>
                                {/if}
                                {if $c.headhtml}<div class="headhtml allnone-toggles">{$c.headhtml|safe}</div>{/if}
                            </th>
                        {/foreach}
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$results item=r}
                    <tr class="{cycle values='r0,r1'}">
                        {foreach from=$columns key=f item=c}
                        {strip}
                            <td{if $c.class} class="{$c.class}"{/if}>
                            {if !$c.template}
                                {$r[$f]}
                            {elseif $c.template && $r.active && $f == 'select'}
                                <span class="icon icon-check-circle lang-current" title="{str tag=langpackuptodate section=langpacks arg1=$r.name}"></span>
                            {else}
                                {include file=$c.template r=$r f=$f}
                            {/if}
                            </td>
                        {/strip}
                        {/foreach}
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
        <div class="card-body">
        {$syncform|safe}
        </div>
        </form>

    {else}
        <p class="no-results">{str tag="nolanguagepacksfound" section="langpacks"}</p>
    {/if}
</div>
<div>
    {$addform|safe}
</div>

{include file="footer.tpl"}
