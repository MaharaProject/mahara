                        </div>
                    </div>
                </main>
            </div>
        </div>
        {if !$htmllite}
        <footer class="footer">
            <p>Export generated for {$user|full_name|escape} on {$export_time|format_date}, from their portfolio at <a href="{$WWWROOT}">{$sitename}</a></p>
        </footer>
        {/if}
    </body>
</html>
