{include file="header.tpl"}
<table id="searchresults" class="table table-striped fullwidth listing">
    <thead>
        <tr>
            <th>{str tag=firstname}</th>
            <th>{str tag=lastname}</th>
            <th>{str tag=portfolioname section="module.lti"}</th>
            <th>{str tag=timesubmitted section="module.lti"}</th>
            <th>{str tag=grade section="module.lti"}</th>
            <th>{str tag=timegraded section="module.lti"}</th>
            <th>{str tag=gradedby section="module.lti"}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$submissions item=s}
        <tr>
            <td>{$s->user->firstname}</td>
            <td>{$s->user->lastname}</td>
            {if is_null($s->grade)}
                <td><a href="{$WWWROOT}./module/lti/graderedirect.php?collectionid={$s->collectionid}&viewid={$s->viewid}">{$s->name}</a></td>
            {else}
                <td>{$s->name}</td>
            {/if}
            <td>{$s->timesubmitted|strtotime|format_date}</td>
            <td>{$s->grade|default:"-"}</td>
            <td>{if $s->timegraded}{$s->timegraded|strtotime|format_date}{else}-{/if}</td>
            <td>{$s->grader->firstname|default:"-"} {$s->grader->lastname}</td>
            <td>
                {if is_null($s->grade)}
                    <a href="{$WWWROOT}./module/lti/graderedirect.php?collectionid={$s->collectionid}&viewid={$s->viewid}">{str tag=grade section="module.lti"}</a>
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{include file="footer.tpl"}