<?php

return array(
    'show_at_hook' => array(
        'title' => 'Выводить автоматически',
        'description' => 'Если опция включена, ссылки на теги будут показаны автоматически на месте хука '.
            '<b>frontend_search</b>.<br>Иначе вы можете вывести ссылки вручную в шаблоне <b>search.html</b>:<br>'.
            '<br><pre><code>
{if !empty($prev_tag)}
    &lt;a href="{$prev_tag.url}"&gt;&amp;larr; {$prev_tag.name}&lt;/a&gt;
{/if}

{if !empty($next_tag)}
    &lt;a href="{$next_tag.url}"&gt;{$next_tag.name} &amp;rarr;&lt;/a&gt;
{/if}
</code></pre>',
        
        'value' => '1',
        'control_type' => waHtmlControl::CHECKBOX
    ),
    'show_empty' => array(
        'title' => 'Выводить пустые',
        'description' => 'Если опция включена, в перелинковке будут отображаться теги, которые не привязаны ни к одному товару.',
        'value' => '0',
        'control_type' => waHtmlControl::CHECKBOX
    )
);
