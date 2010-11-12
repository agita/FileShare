{include file="header.tpl" title="Файл $file"}

	<form action="." method="post" class="box">
		<h1><a href="?d={$id_file}">Скачать</a> | {$size}
			{if $prev || $next}
			<span>
				{if $prev}<a href="?f={$prev}">&larr;</a>{/if}
				{if $next}<a href="?f={$next}">&rarr;</a>{/if}
			</span>
			{/if}
		</h1>
	</form>
	
	{if $img}
	<center class="preview"><a href="{$upload}{$user}/{$file}" target="_self"><img src="{$upload}{$user}/{$file}"{if $big} {$img}="320"{/if}/></a></center>
	{/if}
	
	<div class="stat">
			MD5:&nbsp;&nbsp;{$md5}<br/>
			UA:&nbsp;&nbsp;&nbsp;&nbsp;{$useragent}<br/>
			IP:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$ip}
	</div>
	
	{if $comments}
		
		{function name=comm var=0}
			{foreach item='row' from=$var}
				<div class="ask r2" id="{$row.id_comment}">
					<span class="date">{$row.udate}</span>
					<span class="author" title="{$row.ip}">{$row.username} {$row.user} <a href="?q={$row.id_comment}" title="ответить"><img src="theme/img/quote.gif" alt="ответить"/></a></span>
					<span class="text">{$row.text}</span>
				</div>
				{if $row.sub}
					<div class="sub">
						<div class="sublvl lvl{$level}">
						{comm var=$row.sub level=$level+1}
						</div>
					</div>
				{/if}
			{/foreach}
		{/function}
		{comm var=$comments level=1}
	{/if}
	
	
	{include file="21.file.comment.tpl" id_file=$id_file author=$author}

{include file="footer.tpl"}