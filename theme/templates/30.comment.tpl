{include file="header.tpl" title="Ответ на сообщение"}


		<div class="ask r2">
			<span class="date">{$udate}</span>
			<span class="author" title="{$ip}">{$username} {$user}</span>
			<span class="text">{$text}</span>
		</div>
		
	<div class="stat">
			<a href="?f={$id_file}">Вернуться к информации о файле</a>
	</div>
	
	{include file="21.file.comment.tpl" id_file=$id_file author=$author}

{include file="footer.tpl"}