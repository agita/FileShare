<?php

class A
{
	public $tmpl = NULL;
	public $conn = NULL;
	
	// ===============================================
	// Конструктор
	// ===============================================
	function A()
	{
		session_start();
		
		$this -> template() -> database();
		
		if ( $_REQUEST['f'] ) { $this -> file(); }
		elseif ( $_REQUEST['d'] ) { $this -> download(); }
		elseif ( $_REQUEST['q'] ) { $this -> comment(); }
		else
		{
			switch ( $_REQUEST['m'] ) 
			{
				case 'auth': 	$this -> signin(); break;
				case 'exit':	$this -> signout(); break;
				case 'upload':	$this -> upload(); break;
				case 'drop':	$this -> drop(); break;
				case 'share':	$this -> share(1); break;
				case 'local':	$this -> share(0); break;
				case 'comment':	$this -> comment(); break;
				default: 		$this -> index();
			}
		}
	}
	
	// ===============================================
	// База данных
	// ===============================================
	function database ()
	{
		global $INTO;
		
		$this -> conn = mysql_connect( $INTO['mysql']['host'], $INTO['mysql']['user'], $INTO['mysql']['pass'] );
		if ( $this -> conn )
		{
			mysql_select_db( $INTO['mysql']['base'] ); 
			if ( $INTO['mysql']['autoquery'] ) { mysql_query( $INTO['mysql']['autoquery'] ); };	
		}
		
		return $this;
	}
	
	// ===============================================
	// Шаблонизатор
	// ===============================================
	function template ()
	{
		global $INTO;
		require( DOCUMENT_ROOT.'/smarty/Smarty.class.php');
		
		$this -> tmpl = new Smarty;
		$this -> tmpl -> template_dir   = DOCUMENT_ROOT . $INTO['smarty']['templates'];
		$this -> tmpl -> compile_dir    = DOCUMENT_ROOT . $INTO['smarty']['templates_c'];
		$this -> tmpl -> compile_check	= true;
		$this -> tmpl -> assign( 'styles', $INTO['schema']['styles'] );
		$this -> tmpl -> assign( 'upload', $INTO['schema']['upload'] );
		
		return $this;
	}
	
	// ===============================================
	// Переадресация
	// ===============================================
	function redirect ( $url, $stat = 'HTTP/1.1 303 See Other' )
	{
		Header( $stat );
		Header( "Location: $url" );
		
		return $this;
	}
	
	// ===============================================
	// Размер файла в byte...TiB
	// ===============================================
	function sizeformat( $int =0 )
	{
		$type = array( " byte", " KiB", " MiB", " GiB", " TiB" );
		$inc = 0; while( $int >= 1000 ) { $int /= 1000; $inc++; }  
		return sprintf( "%0.2f %s", $int, $type[$inc] );
	}
	
	// ===============================================
	// Передача параметров с базы прямо в шаблон
	// ===============================================
	function assign( $query, $loop = NULL )
	{
		$result = mysql_query( $query );
		
		if ( !$result ) return $this;
		
		if ( $loop )
		{
			while ($row = mysql_fetch_assoc( $result ) ) { $e[] = $row; }
			$this -> tmpl -> assign( $loop, $e );
		}
		else
		{
			$row = mysql_fetch_assoc( $result );
			if ( $row ){ foreach ( array_keys( $row ) as $key ) { $this -> tmpl -> assign( $key, $row[ $key ] ); }}
		}
		
		mysql_free_result($result);
		
		return $this;
	}
	
	// ===============================================
	// Главная страница
	// ===============================================
	function index()
	{
		global $INTO;
		
		$rows = $INTO['page']['rows'];
		
		function loop ( $self, $q )
		{
			global $INTO;
			$rel = mysql_query( $q ); if ( !$rel ) return array();
			while ( $row = mysql_fetch_assoc( $rel ) )
			{
				$path = DOCUMENT_ROOT.'/'.$INTO['schema']['upload'].$row['user'].'/';	
				if ( file_exists( $path.$row['file'] ) )
				{
					$row['size'] = $self -> sizeformat( $row['size'] );
					$loop[] = $row;
				}
			}
			mysql_free_result( $rel );
			return $loop;
		}
		
		function pages ( $self, $name, $count = 0, $curr = 1, $save = 0 )
		{
			global $INTO;
			
			$link = $INTO['page']['link'];
			$rows = $INTO['page']['rows'];
			
			if ( ( $c = ceil( $count / $rows ) ) > 1 )
			{
				if ( !$curr ) $curr = 1;
				if ( $curr > $c ) $curr = $c;
				
				$step = floor($link / 2); $begin = $step; $end = $c - $step;
				if ( $curr > $begin && $begin > 0 && $curr < $end && $end <= $c  ) {$begin=$curr-$step;$end=$curr+$step;} else {
				if ( $end >= $c - $step && $curr >= floor($pages / 2) ) {$begin=$curr==1||$curr>=$step||$c<$link||$c-$link<1?1:$c-$link+($curr%2?0:1);$end=$c;}
				if ( $begin <= $step && $curr < floor($c / 2) ) {$begin=1;$end=$c<$link?$c:$link;}}
				
				for ( $i = $begin; $i <= $end; $i++) { $e[] = array( page => $i, curr => ( $curr == $i ? 1 : 0), save => $save ); }
				$self -> tmpl -> assign( $name, $e );
				return $curr > 1 ? $curr * $rows - $rows : 0 ;
			}
			return 0;
		}
		
		function order ( $self, $prefix, $prefix_ )
		{
			$sorted = array( 'OrderName' => 'file', 'OrderDate' => 'date', 'OrderSize' => 'size' );
				
			foreach ( array_keys($sorted) as $key )
			{
				$my = $prefix.$key;
				$sp = $prefix_.$key;
				
				if ( $_REQUEST[$my] )
				{ 
					$self -> tmpl -> assign( $my, strtoupper( $_REQUEST[$my] ) == 'AES' ? 'desc' : 'aes' );
					$self -> tmpl -> assign( $my.'_curr', strtoupper( $_REQUEST[$my] ) == 'AES' ? 'aes' : 'desc' );
					$sort[] = sprintf( "`%s` %s", $sorted[$key], strtoupper( $_REQUEST[$my] ) == 'AES' ? '' : 'desc' ); 
				}
				
				if ( $_REQUEST[$sp] ) 
				{
					$self -> tmpl -> assign( $prefix.'SaveOrder', "$sp=$_REQUEST[$sp]" ); 
				}
			}
			if ( $sort ) { $order = join( ",", $sort ); } 
			else { $self -> tmpl -> assign( $prefix.'OrderDate', 'aes' ); $order = '`date` desc'; }
			
			return $order ? "order by $order" : '';
		}
		
		foreach( array( 
			array( name => 'files', save => 'upload', action => "`id_user` <> '$_SESSION[id_user]' and `public`" ), 
			array( name => 'upload', save => 'files', action => "`id_user` = '$_SESSION[id_user]'" ) ) as $e ) 
		{ 
			if ( $rel = mysql_query( "select count(*) as `$e[name]_count`, sum(`size`) as `$e[name]_size` from `files` where $e[action] limit 1" ) )
			{
				if ( $row = mysql_fetch_assoc( $rel ) )
				{
					$begin = pages( $this, $e['name'].'_pages', $row[ $e['name'].'_count' ], $_REQUEST[ $e['name'] ], $_REQUEST[ $e['save'] ] );
					$row[ $e['name'].'_size' ] = $this -> sizeformat( $row[ $e['name'].'_size' ] );
					foreach ( array_keys( $row ) as $key ) {$this -> tmpl -> assign( $key, $row[ $key ] );}
					
					mysql_free_result($rel);
					
					$order = order( $this, $e['name'], $e['save'] );
					$this -> tmpl -> assign( $e['name'], loop ( $this, "select `a`.*, `b`.`user`, date_format(`date`, '%d.%m.%Y %H:%i') as `udate`, ( select count(*) from `comments` where `comments`.`id_file` = `a`.`id_file` limit 1) as `comments` from ( select * from `files` where $e[action] $order limit $begin, $rows) `a` inner join `users` `b` using(`id_user`) $order" ) );
				}
			}	
		}
		
		$this -> tmpl -> assign( 'user', $_SESSION['user'] );
		$this -> tmpl -> display( '00.index.tpl' );
		
		return $this;
	}
	
	// ===============================================
	// Вход/Регистрация пользователя
	// ===============================================
	function signin()
	{
		if ( $rel = mysql_query( "select `id_user`, `user` from `users` where `user` = '$_POST[user]' and `pwd` = md5('$_POST[pwd]') limit 1" ) )
		{
			$dat = mysql_fetch_assoc( $rel );
			
			mysql_free_result($rel);
			
			if ( $dat['id_user'] )
			{
				// Авторизация пройдена успешно
				// Сохраняем данные в сессии и делаем редирект на главную страницу
				
				foreach( array('user', 'id_user') as $key ) { $_SESSION[$key] = $dat[$key]; }
				
				return $this -> redirect( './' );
			}
			else
			{
				$rel = mysql_query( "select `id_user` from `users` where `user` = '$_POST[user]' limit 1" );
				$dat = mysql_fetch_assoc( $rel );
				
				mysql_free_result($rel);
				
				if ( $dat['id_user'] || !preg_match('/\@/', $_POST['user'] ) )
				{
					// Авторизация не пройдена, неверный пароль
					// Выводим повторно форму аторизации с соответствующем сообщением
					
					$this -> tmpl -> assign( 'user', $_POST['user'] );
					$this -> tmpl -> display( '10.deny.tpl' );
				}
				else
				{
					// Регистрация пользователя
					// Выводим повторно форму аторизации с соответствующем сообщением
					
					mysql_query( "insert into `users`(`user`, `pwd`) values ( '$_POST[user]', md5('$_POST[pwd]') )" );
					$_SESSION['id_user'] = mysql_insert_id();
					$_SESSION['user'] = $_POST['user'];
					return $this -> redirect( './' );
				}
			}
		}
		
		return $this;
	}
	
	// ===============================================
	// Выход пользователя
	// ===============================================
	function signout()
	{
		session_destroy();
		return $this -> redirect( './' );
	}
	
	// ===============================================
	// Загрузка файла
	// ===============================================
	function upload()
	{
		global $INTO;
		
		$path = DOCUMENT_ROOT.'/'.$INTO['schema']['upload'].$_SESSION['user'].'/';
		if ( !file_exists( $path ) ) { mkdir( $path, 0777, true ); chmod($path, 0777); }
		
		foreach( array_keys( $_FILES['file']['name'] ) as $key )
		{
			// Резервируем для загрузки нескольких файлов
			// Получаем имя файла без его пути, что свойственно для IE6
			
			$file = basename( $_FILES['file']['name'][$key] );
			
			if ( file_exists( $path.$file ) )
			{
				// Получаем md5 сумму существующего файла
				$old = md5_file( $path.$file );
			}
			
			if ( move_uploaded_file( $_FILES['file']['tmp_name'][$key], $path.$file ) )
			{
				// MD5 сумма загруженного файла
				$md5 = md5_file( $path.$file );
				
				// Размер, IP, UseAgent пользователя
				$q[] = sprintf( "`size` = '%d'", filesize( $path.$file ) );
				$q[] = sprintf( "`ip` = '%s'", getenv('REMOTE_ADDR') );
				$q[] = sprintf( "`useragent` = '%s'", getenv('HTTP_USER_AGENT') );
				$q[] = sprintf( "`id_user` = '%d'", $_SESSION['id_user'] );
				
				// Файл, и дата
				$q[] = "`md5` = '$md5'";
				$q[] = "`file` = '$file'";
				$q[] = "`date` = now()";
				
				// Генерируем общею часть запроса
				$query = "`files` set ". join( ",", $q );
				
				// Создаем запись о новом и обновляем о существующем
				if ( ! $old ) { mysql_query( "insert into $query" ); }
				else { mysql_query( "update $query where `md5` = '$old' and `id_user` = '$_SESSION[id_user]' limit 1" ); }
			}
		}
		return $this -> redirect( './' );
	}
	
	
	// ===============================================
	// Удаление файлов
	// ===============================================
	function drop()
	{
		global $INTO;
		
		$path = DOCUMENT_ROOT.'/'.$INTO['schema']['upload'].$_SESSION['user'].'/';
		
		if ( count( $_POST['file'] ) > 0 && file_exists($path) )
		{
			$rel = mysql_query( 'select `file`, `id_file` from `files` where `id_file` in ('. join(',', $_POST['file'] ) . ") and `id_user` = '$_SESSION[id_user]'" ); 
			
			if ( $rel )
			{
				while ( $row = mysql_fetch_assoc( $rel ) )
				{
					unlink( $path.$row['file'] );
					if ( !file_exists( $path.$row['file'] ) ) { $drop[] = $row['id_file']; }
				}
				mysql_free_result( $rel );
				
				mysql_query( 'delete from `files` where `id_file` in ('. join(',', $drop ) . ") and `id_user` = '$_SESSION[id_user]'" ); 
			}
		}
		return $this -> redirect( './' );
	}
	
	// ===============================================
	// Общедоступный/Личный
	// ===============================================
	function share( $value = 1 )
	{
		if ( count( $_POST['file'] ) > 0 )
		{
			mysql_query( "update `files` set `public` = '$value' where `id_file` in (". join(',', $_POST['file'] ) . ") and `id_user` = '$_SESSION[id_user]'" ); 
		}
		return $this -> redirect( './' );
	}
	
	// ===============================================
	// Информация о файле
	// ===============================================
	function file()
	{
		global $INTO;
		$id = round( $_REQUEST['f'] );
		
		$result = mysql_query( "select `a`.*, `b`.`user`, date_format(`date`, '%d.%m.%Y %H:%i') as `udate` from ( select * from `files` where `id_file` = '$id') `a` inner join `users` `b` using(`id_user`) limit 1" );
		if ( $result ) 
		{
			$row = mysql_fetch_assoc( $result );
			$row['size'] = $this -> sizeformat( $row['size'] );
			$path = DOCUMENT_ROOT.'/'.$INTO['schema']['upload'].$row['user'].'/';
			
			if ( preg_match('/\.(jpe?g|png|bmp|gif)$/', $row['file']) )
			{
				list( $w, $h, $t, $a ) = getimagesize( $path.$row['file'] );
				$row['img'] = $w > $h ? 'width' : 'height';
				$row['big'] = $w > 800 || $h > 320;
			}
			
			if ( $row ){ foreach ( array_keys( $row ) as $key ) { $this -> tmpl -> assign( $key, $row[ $key ] ); }}
		}
		mysql_free_result($result);
		
		$result = mysql_query( "select * from (select *, date_format(`date`, '%d.%m.%Y %H:%i') as `udate` from `comments` where `id_file` = '$id' ) `a` left outer join `users` `b` using(`id_user`) order by `date`" );
		while ( $row = mysql_fetch_assoc( $result ) ) { $comments[ $row['id_parent'] ][] = $row; }
		mysql_free_result($result);
		
		function tree( $data, $key )
		{
			if ( $data[ $key ] ) { foreach ( $data[ $key ] as $row ) { $row['sub'] = tree( $data, $row['id_comment'] ); $e[] = $row; } }
			return $e;
		}
		
		$this -> assign( "select (select `id_file` from `files` where `id_file` < '$id' and ( `public` or `id_user` = '$_SESSION[id_user]' ) order by `id_file` desc limit 1) as `prev`, ( select `id_file` from `files` where `id_file` > '$id' and ( `public` or `id_user` = '$_SESSION[id_user]' ) order by `id_file` limit 1) as `next`" );
		$this -> tmpl -> assign( 'comments', tree( $comments, 0 ) );
		$this -> tmpl -> assign( 'author', $_SESSION['user'] );
		$this -> tmpl -> assign( 'upload', $INTO['schema']['upload'] );
		$this -> tmpl -> display( '20.file.tpl' );
	}
	
	// ===============================================
	// Информация о файле
	// ===============================================
	function download()
	{
		global $INTO;
		
		$id = round( $_REQUEST['d'] );
		$rels = mysql_query( "select `a`.*, `b`.`user` from ( select * from `files` where `id_file` = '$id') `a` inner join `users` `b` using(`id_user`) limit 1" );
		$data = mysql_fetch_assoc( $rels );
		$path = DOCUMENT_ROOT.'/'.$INTO['schema']['upload'].$data['user'].'/';
		
		header("Expires: 0");
		header("Cache-Control: private");
		header("Content-Disposition: attachment; filename=".preg_replace('/[\s\t\n\r]+/','_',$data['file'] ) );
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");  
		
		echo fread(fopen($path.$data['file'], "rb"), filesize($path.$data['file']));  
		exit;
	}
	
	// ===============================================
	// Коментирование файла
	// ===============================================
	function comment()
	{
		if ( $_REQUEST['q'] )
		{
			$id = round( $_REQUEST['q'] );
			$this -> assign( "select *, date_format(`date`, '%d.%m.%Y %H:%i') as `udate` from ( select * from `comments` where `id_comment`='$id' ) a left outer join `users` `b` using(`id_user`)" );
			$this -> tmpl -> assign( 'author', $_SESSION['user'] );
			$this -> tmpl -> assign( 'id_parent', $id );
			$this -> tmpl -> display( '30.comment.tpl' );
		}
		else
		{
			$q[] = sprintf( "`ip` = '%s'", getenv('REMOTE_ADDR') );
			$q[] = sprintf( "`useragent` = '%s'", getenv('HTTP_USER_AGENT') );
			$q[] = sprintf( "`username` = '%s'", $_REQUEST['username'] );
			$q[] = sprintf( "`id_user` = '%d'", $_SESSION['id_user'] );
			$q[] = sprintf( "`id_file` = '%d'", $_REQUEST['id_file'] );
			$q[] = sprintf( "`id_parent` = '%d'", $_REQUEST['id_parent'] );
			$q[] = sprintf( "`text` = '%s'", preg_replace('/[\n\r]+/','<br/>',preg_replace('/<[^>]+>/',' ', $_REQUEST['text'] ) ) );
			$q[] = "`date` = now()";
			
			$res = mysql_query( "insert into `comments` set ". join( ",", $q ) );
			$anc = mysql_insert_id();
			
			return $this -> redirect( "?f=$_REQUEST[id_file]#$anc" );
		}
	}
}
?>