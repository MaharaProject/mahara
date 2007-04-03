<div class="performanceinfo">
{if $perf_memory_total}
    <span class="memoryused">{str tag="memoryused" section="performance"}: {$perf_memory_total_display}</span><br />
{/if}
{if $perf_realtime}
    <span class="timeused">{str tag="timeused" section="performance"}: {$perf_realtime} {str tag="seconds" section="performance"}</span><br />
{/if}
{if $perf_includecount}
    <span class="included">{str tag="included" section="performance"}: {$perf_includecount}</span><br />
{/if}
{if $perf_dbqueries}
    <span class="dbqueries">{str tag="dbqueries" section="performance"}: {$perf_dbqueries}</span><br />
{/if}
{if $perf_ticks}
    <span class="posixtimes">{str tag="ticks" section="performance"}: {$perf_ticks} {str tag="user" section="performance"}: {$perf_user}
        {str tag="sys" section="performance"}: {$perf_sys} {str tag="cuser" section="performance"}: {$perf_cutime}
        {str tag="csys" section="performance"}: {$perf_cstime}</span><br />
{/if}
{if $perf_serverload}
    <span class="serverload">{str tag="serverload" section="performance"}: {$perf_serverload}</span><br />
{/if}
</div>

