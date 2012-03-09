<?php

$html = null;

function downloadJoomla ($url, $path) {

 	$newfname = $path;
	$file = fopen ($url, "rb");
	
	if ($file) {
		$newf = fopen ($newfname, "wb");
    if ($newf)
		while(!feof($file)) {
			fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
    	}
    }
	if ($file) {
    	fclose($file);
    }
    if ($newf) {
    	fclose($newf);
    }
    unzipJoomla($path);
    return true;
}
 
function unzipJoomla($file, $folder = '.'){

	$zip = new ZipArchive;
	$res = $zip->open($file);
	if ($res === TRUE) {
		$zip->extractTo($mappe);
		$zip->close();
		return true;
     } else {
		die('could not unzip files to folder from file $file');
	}
}

	$mappe 			= $_GET['mappe'];
	$downloadFile	= ($_GET['downloadfile']) ? $_GET['downloadfile'] : 'http://joomlacode.org/gf/download/frsrelease/16760/72877/Joomla_2.5.2-Stable-Full_Package.zip';

if($mappe != '' && $downloadFile != ''):
	if (downloadJoomla($file,$mappe.'/joomla.zip') == true):
         $html .= '<strong>Joomla have been succesfully installed</strong><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$mappe.'">Installer Joomla</a>"';
    else:
         $html .= '<strong>Joomla could not be removed to the right position. FAILED!</strong>';
    endif;
endif;

/*      
can be used later to download the newest package                                                                                                             
$xml = simplexml_load_file('http://update.joomla.org/core/extension.xml'); 
echo $xml->update[1]->downloads->downloadurl; 
*/

?> 
<!DOCTYPE html>
<html>
	<head>
		<title>Joomla Installer</title> 
		<style type="text/css">
			body{background:rgba(242, 242, 242, 0.9);}
			input{border-radius:3px;width:400px; height:50px; font-size:40px;border:0;font-style:italic;margin:0 auto;margin-top:10px;}
			h1{font-family: Arial, Helvetica, sans-serif;font-size: 20px;font-weight: bold;text-transform: uppercase;color:#165F97;}
		</style>
		<script language="Javascript">
			function vombieReset(box, value) {
  				if (box.value == value) {
   					box.value = "";
  				 }
			}
-->
</script>
	</head>
	<body>
		<div style="margin:0 auto; width:400px; margin-top:200px;">
			<?php echo $html; ?>
			<h1>Joomla Installer (2.5.2)</h1>
			<form method="get" action="index.php">
				<input name="mappe" type="text" value="Folder (eg. and)" onclick="vombieReset(this, 'Folder')" /><br />
				<input name="downloadfile" type="text" value="Download Package" onclick="vombieReset(this,'Download Package')" />
			</form>
			<p>The "Download Package" input is optional. When leaved empty will it try to transfer Joomla 2.5.2 to your server.</p>
		</div>


	</body>
</html>
