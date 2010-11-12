{if $array}
	, страница: &nbsp;&nbsp;
	{foreach item='row' from=$array}
		{if $row.curr}<b>{$row.page}</b>{else}<a href="?{$prefix}={$row.page}{if $row.save}&{$prefix2}={$row.save}{/if}{if $save}&{$save}{/if}#{$prefix}">{$row.page}</a>{/if}&nbsp;
	{/foreach}
{/if}