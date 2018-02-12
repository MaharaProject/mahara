{if $ignoreagreevalue}
    <h2>{$privacytitle}</h2>
    {$privacy->content|safe}
{else}
    <div class="panel panel-default">
        <div class="last form-group collapsible-group">
            <fieldset class="pieform-fieldset last collapsible">
                <legend>
                    <h4>
                        <a href="#dropdown{$privacy->id}" data-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                            {$privacytitle}
                            <span class="icon icon-chevron-down collapse-indicator right pull-right"></span>
                        </a>
                    </h4>
                </legend>
                <div class="fieldset-body collapse" id="dropdown{$privacy->id}">
                    <span class="text-midtone pull-right">{str tag='lastupdated' section='admin'} {$privacytime} </span>
                    {$privacy->content|safe}
{/if}
