<?php 

	// 
	require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "psyDuck.php";

	// define the default storage folder for the app
	if (!defined('STORAGE_FOLDER'))
		define( 'STORAGE_FOLDER', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'storage_folder' .DIRECTORY_SEPARATOR );

	

	if (!count(debug_backtrace())):
?>

<!DOCTYPE html>
<html>
<head>
	<title> php psyDuck's example apps </title>
</head>
<body>
	<p>
		each app is contained in their own folder
	</p>
	
	<table>
		<?php foreach ( glob('*') as $folder ): ?>
							<?php if(!is_dir($folder)) continue; ?>
			<tr>
				<td> <a href="<?=$folder?>" > <?=$folder?> </a> </td>
			</tr>
		<?php endforeach; ?>
	</table>
	<p> Developed by <a href="https://github.com/EduhAzvdo/" target="_blank" >EduhAzvdo</a>	</p>
</body>
</html>
<?php 

	endif;