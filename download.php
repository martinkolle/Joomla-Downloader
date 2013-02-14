<?php
if(isset($_GET['url']) && isset($_GET['start'])){
    joomla::start();
} elseif(isset($_GET['leave'])){
    joomla::leave();
}

class joomla {

	//Messages and errors is pushed to this array
    public static $msg = array();
    
    /*
	* Start function
	* Checking if tmp dir exists else create
	* Get the variables for Joomla package etc.
    */

    public function start(){
        set_time_limit (24 * 60 * 60);

        $url        = $_GET['url'];
        $tmp        = dirname(__FILE__).'/weird-tmp/';
        $file       = $tmp . basename($url);
        $moveTo     = dirname(__FILE__).'/';

        if(!file_exists($tmp)){
            if(!mkdir($tmp, 0777)){
                array_push(self::$msg,array("msg" => "Could not create '".$tmp."'","type"=>"error"));
            } else {
                array_push(self::$msg,array("msg" => "Creating tmp folder ".$tmp,"type"=>"msg"));
            }
        } else {
            array_push(self::$msg, array("msg" => "Tmp folder exists ".$tmp,"type"=>"msg"));
        }

        self::download($url,$file);
        self::unzip($file, $tmp);
        self::move($tmp, $moveTo);
        self::clean($tmp);
        die(self::error());
    }

    /**
	* Version function
	* Find joomla versions depending on the official xml files
	* We are getting the packages from github, because Joomla do not include the url for the full package download
	* @return rows for table
    */

    public function version(){

        $versions       = array('http://update.joomla.org/core/sts/extension_sts.xml', 'http://update.joomla.org/core/extension.xml');
        $joomlaVersion  = array();
        $html           = null;
            foreach ($versions as $key => $version):
                if(self::connection($version)) :
                    $joomlaVersions = simplexml_load_file($version);
                    foreach ($joomlaVersions as $joomla):
                        if(!in_array((string) $joomla->version, $joomlaVersion, true)):
                            array_push($joomlaVersion, (string) $joomla->version);
                            $url = "https://github.com/joomla/joomla-cms/archive/".$joomla->version.".zip";
                            $class = ($joomla->section == 'STS') ? "label label-warning" : "label label-success";
                            $html .= '<tr>';
                                $html .= '<td>'.$joomla->version.'</td>';
                                $html .= '<td><span class="label label-info">'.$joomla->tags->tag.'</span></td>';
                                $html .= '<td class="'.$joomla->section.'"><span class="'.$class.'">'.$joomla->section.'</span></td>';
                                $html .= '<td><p alt="'.$url.'" class="btn download">Download</p></td>';
                            $html .= '</tr>';
                        endif;
                    endforeach;
                else :
                    array_push(self::$msg, array("msg" => "Could not connect to ".$version,"type"=>"error"));
                endif;
            endforeach;
        return $html;
    }

    /** 
	* Downloading Joomla and place it in the tmp folder
    */
    public function download($url, $name){
        
        $header = get_headers($url, 1);

        if($header[1] == "HTTP/1.1 404 Not Found"){
            array_push(self::$msg, array("msg" => "File not found".$url,"type"=>"error"));
            array_push(self::$msg, array("msg" => "Download aborted!","type"=>"error"));

            die(self::error());
        }
        if(!@copy($url,$name)){
        $error = error_get_last();
            die(array_push(self::$msg, array("msg" => "Download failed: ".$error['message'],"type"=>"error")));
        } else {
            array_push(self::$msg, array("msg" => "Download to ".$name,"type"=>"msg"));
        }

        return true;  
    }

    /**
	* Unzip Joomla in the tmp folder
    */

    public function unzip($file, $to){

        $zip = new ZipArchive;
        if ($zip->open($file) === true) {
            $zip->extractTo($to);
            $zip->close();
            array_push(self::$msg, array("msg" => "Unzipped to ".$to,"type"=>"msg"));
        } else {
            die(array_push(self::$msg, array("msg" => "Could not unzip to ".$to,"type"=>"error")));
        }
    }

    /**
	* Delete tmp folder
	*/
    public function clean($tmp){
        if(function_exists("system")):
            system('/bin/rm -rf ' . escapeshellarg($tmp));
            array_push(self::$msg, array("msg" => "Removed tmp folder ".$tmp,"type"=>"msg"));   
        else :
            array_push(self::$msg, array("msg" => "Please remove tmp folder ".$tmp,"type"=>"error"));
        endif;
    }

    /**
    * Removes this file for security reason
    */
    public function leave(){
        if(unlink(__FILE__)):
            die("1");
        else:
            die("0");
        endif;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <title>Download Joomla</title>
    <link href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {padding-top: 40px;padding-bottom: 40px;background-color: #f5f5f5;}
      table{width:100%;}
      td.section.STS{background:#f89406;}
      td.section{background: #468847;}
      th{color:#08c;}
      tr{height:20px;}
      .container {max-width: 700px;padding: 19px 29px 29px;margin: 0 auto 20px;background-color: #fff;border: 1px solid #e5e5e5;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);-moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);box-shadow: 0 1px 2px rgba(0,0,0,.05);}
    </style>
  </head>
  <body>
    <div class="container">
    <div style="width:100%;clear:both;height:160px;"><img src="http://kmweb.dk/joomla.png" style="float:left;"/> <h1 style="padding-top:50px;">Download Joomla!</h1></div>
    <form class="form-signin">
        <table class="table table-striped">
            <tr class="no-slide">
                <th>Version</th><th>Tag</th><th>Section</th><th style="width:20%"></th>
            </tr>
            <?php echo joomla::version(); ?>
        </table>
    </form>
        <div class="progress" style="display:none">
            <div id="howlong" class="bar" style="width: 1%;"></div>
        </div>
        <div id="msg"><?php echo joomla::error(); ?></div>
    </div>
    <script src="http://twitter.github.com/bootstrap/assets/js/jquery.js"></script>
    <script type="text/javascript">
    $(document).ready(function(){
        $("p.download").click(function(){
            var url = $(this).attr("alt");
            $(".progress").slideDown(600);
            $("#msg").hide();
            $(this).html("Downloading...").addClass("disabled");
            $("p.download").addClass("disabled").removeClass("download");
            $(this).closest("tr").addClass("no-slide");
            $("tr").not('.no-slide').fadeOut(700);
            setTimeout(function(){
                $.ajax({
                  type: 'GET',url: 'download.php', data: {url: url, start: '1'},
                  beforeSend:function(){
                    $('#msg').html('<div class="loading">Loading... Please wait about one minut.</div>');
                    $('#msg').append('<p><span class="btn btn-mini btn-success" href="#"><i class="icon-download-alt"></i></span> Downloading from '+url+'</p>');
                    $('#msg').slideDown(600);
                    $('#howlong').css("width", "20%");
                    $("div.progress").addClass("progress-striped active");
                  },
                  success:function(data){
                    $("#msg").css("display", "none").append(data).slideDown("slow");
                    $("#msg").append('<a onclick="redirect();" id="redirect" class="btn btn-large btn-primary redirect">Start installation...</a>');
                    $('#howlong').css("width", "100%");
                    $("div.progress").removeClass("progress-striped active");
                  },
                  error:function(){
                    $("form").slideUp();
                    $('#msg').append('<p><span class="btn btn-mini btn-danger" href="#"><i class="icon-remove"></i></span> Something went wrong - that is the only thing I can say.</p>');
                    $('div.progress').slideUp();
                  }
                });
            }, 1000);
        });
    });

function redirect(){
    $("#redirect").html("Deleting...");
    $("#redirect").addClass("disabled");
    setTimeout(function(){
        $.ajax({
            type: 'GET',url: 'download.php', data: {leave: "true"},
            success:function(data){
               if(data == 1){
                $(this).html("Redirecting...");
                    setTimeout(function() {window.location = "index.php";}, 1000);
                } else{
                    alert("Could not delete download.php. Please delete download.php before using Joomla! If not, it is possible to completely remove your Joomla installation! ("+data+").");
                }
            },
            error:function(){
                alert("Could not delete ("+data+"). Please delete download.php before using Joomla! If not, it is possible to completely remove your Joomla installation!");
            }
        });
    }, 1000);
}
    </script>
  </body>
</html>