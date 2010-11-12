{include file="header.tpl" title="Обмен файлами"}

	{if $user}
		<form action="." method="post" class="box" enctype="multipart/form-data">
				<input type="hidden" name="m" value="upload"/>
				<label for="file">Выберете файл для загрузки</label>
				<input type="file" id="file" name="file[]">
				<input type="submit" value="Отправить">
		</form>
		
		{if $upload}
			<form action="." method="post">
				<table class="t my" id="upload">
					<caption>Мои загруженные файлы</caption>
					<thead>
						<tr>
							<th>{include file="03.index.sort.tpl" name="uploadOrderName" value=$uploadOrderName save=$uploadSaveOrder anchor="upload" title="Файл"}</th>
							<th width="150px">{include file="03.index.sort.tpl" name="uploadOrderSize" value=$uploadOrderSize save=$uploadSaveOrder anchor="upload" title="Размер"}</th>
							<th width="150px">{include file="03.index.sort.tpl" name="uploadOrderDate" value=$uploadOrderDate save=$uploadSaveOrder anchor="upload" title="Дата"}</th>
							<th width="8%">Комментарии</th>
							<th width="8%">Public</th>
							<th width="1%">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						{foreach item='row' from=$upload}
							<tr class="{cycle name='a' values=',cl1,,cl2'}">
								<td><a href="?f={$row.id_file}" target="_blank">{$row.file}</a></td>
								<td>{$row.size}</td>
								<td>{$row.udate}</td>
								<td>{if $row.comments > 0}<a href="?f={$row.id_file}" target="_blank">{$row.comments}</a>{else}-{/if}</td>
								<td>{if $row.public}да{else}-{/if}</td>
								<td align="right"><input type="checkbox" name="file[]" value="{$row.id_file}"/></td>
							</tr>
						{/foreach}
					</tbody>
					
					<tfoot>
						<tr>
							<td colspan="2">
								Всего {$upload_count|default:0} файлов{if $upload_size}, {$upload_size}{/if}
								{include file="02.index.pages.tpl" array=$upload_pages prefix='upload' prefix2='files' save="$filesSaveOrder&$uploadSaveOrder"}
							</td>
							<td colspan="4" align="right">
								<select name="m">
									<option value="">&lt; выбирете &gt;</option>
									<option value="share">Общедоступный</option>
									<option value="local">Личный</option>
									<option value="drop">Удалить</option>
								</select>
								<input type="submit" value="Выполнить">
								<img src="theme/img/ref.gif" hspace="4"/>
							</td>
						</tr>
					</tfoot>
				</table>
			</form>
		{/if}
	{else}
		{include file="01.index.auth.tpl"}
	{/if}

	{if $files}
		<table class="t" id="files">
			<caption>Доступные файлы</caption>
			<thead>
				<tr>
					<th>{include file="03.index.sort.tpl" name="filesOrderName" value=$filesOrderName save=$filesSaveOrder anchor="files" title="Файл"}</th>
					<th width="150px">{include file="03.index.sort.tpl" name="filesOrderSize" value=$filesOrderSize save=$filesSaveOrder anchor="files" title="Размер"}</th>
					<th width="150px">{include file="03.index.sort.tpl" name="filesOrderDate" value=$filesOrderDate save=$filesSaveOrder anchor="files" title="Дата"}</th>
					<th>Пользователь</th>
					<th width="8%">Комментарии</th>
				</tr>
			</thead>
			<tbody>
				{foreach item='row' from=$files}
					<tr class="{cycle name='b' values=',cl1,,cl2'}">
						<td><a href="?f={$row.id_file}" target="_blank">{$row.file}</a></td>
						<td>{$row.size}</td>
						<td>{$row.udate}</td>
						<td>{$row.user}</td>
						<td>{if $row.comments > 0}<a href="?f={$row.id_file}" target="_blank">{$row.comments}</a>{else}-{/if}</td>
					</tr>
				{/foreach}
			</tbody>
			<tfoot>
						<tr>
							<td colspan="5">
								Всего {$files_count|default:0} файлов{if $files_size}, {$files_size}{/if}
								{include file="02.index.pages.tpl" array=$files_pages prefix='files' prefix2='upload' save="$uploadSaveOrder&$filesSaveOrder"}
							</td>
						</tr>
					</tfoot>
		</table>
	{/if}

{include file="footer.tpl"}