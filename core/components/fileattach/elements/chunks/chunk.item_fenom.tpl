<p>{$description ? '<strong>' ~ $description ~ '</strong><br/>' : null}
    <a href="{$url}">{$name}</a> <span class="badge">{$download}</span>
    {$size ? '<br/><small>Size:' ~ $size ~ 'bytes </small>' : null}
    {$ext ? '<br/><small>Size:' ~ $ext ~ '</small>' : null}
    {$timestamp ? '<br/><small>Size:' ~ $timestamp ~ '</small>' : null}
    {$hash ? '<br/><small>Size:' ~ $hash ~ '</small>' : null}
</p>
