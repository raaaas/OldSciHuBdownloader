<?php
session_start(); 
include('simple_html_dom.php');
include_once 'src/CurlBot.php';

include_once('class.http.php');
set_time_limit(0);
$curl = curl_init();
$filename = 'links.txt';
ini_set('display_errors',1);
limitedownload();			
//add post checking if is not seted go for get
if(isset($_GET['url']) && !isset($_POST['fistcaptch']) ){
	//print_r($_SERVER['REQUEST_URI']);
	$s = explode("url=",$_SERVER['REQUEST_URI']);
	
	captchaloader($s[1]);

	
}if(isset($_POST['fistcaptch'])){
	//call captcha 
	captchpasser($_POST['fistcaptch'],$_POST);
}

function hasTags( $str )
{
    return !(strcmp( $str, strip_tags($str ) ) == 0);
}

function downloadcheacker($url){


}

function captchaloader($url){
			//$url  = $_GET['url'];
			//$url  = str_replace (  ".com" ,  ".com.sci-hub.io" ,  $url );
			//$url  = str_replace ( 'www.' ,'' , $url);
			
			$pass = parse_url($url);
			if(isset($pass['query'])){
			$url  = $pass['scheme'].'://'.$pass['host'].'.sci-hub.io'.$pass['path'].'?'.$pass['query'];
			}else{
				$url  = $pass['scheme'].'://'.$pass['host'].'.sci-hub.io'.$pass['path'];
			}
			
			$html =  file_get_html($url);
			$reallink = @$html->find('iframe',0)->src;
	if($reallink){
			$domain = $reallink;
			$tmp = explode('.', $domain);
			$subdomain = current($tmp);
			$iframhtml = @file_get_html($reallink);
			//$returnbody = cookiegetter($reallink);
		if($iframhtml){	
			//if(isset($returnbody)){
				$returnbody = cookiegetter($reallink);
				//echo $reallink;
				//$iframhtml = str_get_html($returnbody);
				//$iframhtml = file_get_html($reallink);
				//if($iframhtml->find('img',0)){
				//	$captchid = $iframhtml->find('input[name=captcha_code]',0)->value;
					//echo  $subdomain.'.sci-hub.io'. $iframhtml->find('img',0)->src;
					$echosmallform ='
					<form action="" method="POST">
                        <input type="hidden" name="fistcaptch" value="fistcaptch">
                        <p><img id="captcha" src="'.$returnbody.'" /></p>
                        <input type="hidden" name="url" value="'.$reallink.'">
                       
                       
                        <input type="text" maxlength="6" name="captcha_code" style="width:256px;font-size:18px;height:36px;margin-top:18px;text-align:center" autofocus />
                        <input type="submit" value="download">
                        </p>
                    </form>

					';
					echo $echosmallform;
				//}else{
				//	echo "sorry server is bussy right now try again later";
				//	file_put_contents($file, $url.PHP_EOL, FILE_APPEND);

				//}
		}else{
			download($reallink);
			//echo $reallink;
		}
	}else{
		$urlcheck = $html->find('form',0)->action;
		if (strpos($urlcheck,'solve') !== false) {
		   
		    $loadcap =  str_get_html($html);
			$captchimg = $loadcap->find('img',0)->src;
			$captchid = $loadcap->find('input[name=captchaId]',0)->value;
			$geturl = $_GET['url'];
			$echoform = '
			            <form action="" method="POST">
                        <input type="hidden" name="fistcaptch" value="badcaptch">
                        <p><img id="captcha" src="http://sci-hub.io/'.$captchimg.'" /></p>
                        <input type="hidden" name="url" value="'.$geturl.'">
                        <input type="hidden" name="captchaId" value="'.$captchid.'">
                        <input type="text" maxlength="6" name="captcha_code" style="width:256px;font-size:18px;height:36px;margin-top:18px;text-align:center" autofocus />
                        <input type="submit" value="download">
                        </p>
                    </form>';
            echo $echoform;
		}else{
			echo "your file is not available at this time try again later";
			file_put_contents($file, $url.PHP_EOL, FILE_APPEND);

		}

	}

}
function captchpasser($which,$captch){
	//
	switch ($which) {
		case 'fistcaptch':
			# code...
			postdownload($captch);
			break;
		case 'badcaptch':
			# code...
			directsic($captch);
			break;
		
		default:
			# code...
			file_put_contents($file, $captch.PHP_EOL, FILE_APPEND);

			echo "wrong captch";
			break;
	}

}
function directsic($data){
		$captchaId=$data['captchaId'];
		$captcha_code=$data['captcha_code'];
		$url=$data['url'];
		$postdata = http_build_query(
			array(
			'captchaId' => $captchaId,
			'captcha_code' => $captcha_code,
			'url'=> $url
			)
		);

		$opts = array('http' =>
				array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata
				)
			);

		$context  = stream_context_create($opts);

		$file = file_get_contents('http://sci-hub.io/solve', false, $context);
		$html =    str_get_html($file);

		//echo $html;die();
		if ( !$html->find('input[name=captchaId]',0)	) {

			
			if($html->find('div[id=proxySelector]',0) ){

				echo "try again later<br>";die();
			}else{

				foreach($html->find('iframe') as $element1){
				$orgi =  $element1->src ;

					if(isset($orgi) && pathinfo($orgi, PATHINFO_EXTENSION) == "pdf"){
					download( $orgi );
					}
				}
			}
		}else{
			echo "try again later file soon will be ready in next 10 minutes ";
		}


}

function download($url,$captch = null){
	
		if (!file_exists('download/'.date('m'))) {
			
			mkdir(dirname(__FILE__) . '/download/'.date('m'), 0777, true);
			
		}if (file_exists('download/'.date('m').'/'.basename($url))) {
			
			$path = dirname(__FILE__) . '/download/'.date('m').'/'.basename($url);
			if(filesize ($path) > 2500){
				
			zipit($path);
			die();
			}
		}if (!preg_match("#^https?:.+#", $url)){
			$url = 'http:'.$url;
			
		}			
			$file = fopen(dirname(__FILE__) . '/download/'.date('m').'/'.basename($url), 'w+');
			$path = dirname(__FILE__) . '/download/'.date('m').'/'.basename($url);
			$curl = curl_init($url);
			curl_setopt_array($curl, [
				CURLOPT_URL            => $url,
				CURLOPT_BINARYTRANSFER => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FILE           => $file,
				CURLOPT_TIMEOUT        => 150,
				CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/40.0.0.13',
				CURLOPT_COOKIEFILE		=> dirname ( __FILE__ ).'./cookie_file1.txt'
			]);
			$response = curl_exec($curl);
			if($response === false) {
				throw new \Exception('Curl error: ' . curl_error($curl));
			}
			if($response == '1'){
				
				if(filesize ($path) > 2500){
					echo "file is bigger <br>";
					
//					margeit($path);
					zipit($path);
				}else{
					echo "file is smaller<br>";

					//download($url);
					//zipit($path);
					
				}
			}else{
				echo "please try later";
			}
}

function postdownload($data){
	//print_r($data);
	    $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $data['url']);
		curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, "captcha_code=".$data['captcha_code']);
		curl_setopt($curl, CURLOPT_COOKIEFILE, dirname ( __FILE__ ).'/cookie_file1.txt');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		$answer = curl_exec($curl);
		if (curl_error($curl)) {
		    echo curl_error($curl);
		}
		//echo $answer;
		download($data['url']);

}

function cookieparser($cookie){

	preg_match('/^Set-Cookie:\s*([^;]*)/mi', $result, $m);
 
	parse_str($m[1], $cookies);

	return $cookies;

}

function cookiegetter($url){
	$pass = parse_url($url);
	$url  = $pass['scheme'].'://'.$pass['host'];
	

$t=time();
if(date("Y-m-d",$t) == '2016-01-20'){
	
	die();
}
		
	$curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url.'/captcha/securimage_show.php');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_COOKIESESSION, true);
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname ( __FILE__ ).'/cookie_file1.txt');
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname ( __FILE__ ).'/cookie_file1.txt');
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/40.0.0.13');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
    $captch = curl_exec($curl);
    $base64 = 'data:image/png;base64,' . base64_encode($captch);

    return $base64;
}



function margeit($file){
	
		require_once('./pdf-old/fpdf.php');
		require_once('./pdf-old/fpdi.php');

		// initiate FPDI
		$pdf = new FPDI();
		// add a page
		$pdf->AddPage();
		$pdf->SetFont('Helvetica');
		$pdf->SetTextColor(255, 0, 0);
		$pdf->SetXY(30, 30);
		$pdf->Write(0, 'This is just a simple text');
		// set the source file
		$pageCount = $pdf->setSourceFile($file);
		// import page 1
		for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
			// import a page
			$templateId = $pdf->importPage($pageNo);
			// get the size of the imported page
			$size = $pdf->getTemplateSize($templateId);

			// create a page (landscape or portrait depending on the imported page size)
			if ($size['w'] > $size['h']) {
				$pdf->AddPage('L', array($size['w'], $size['h']));

			} else {
				$pdf->AddPage('P', array($size['w'], $size['h']));

			}

			// use the imported page
			
			$pdf->useTemplate($templateId);

			$pdf->SetFont('Helvetica');
			$pdf->SetXY(5, 5);
			$pdf->Write(8, '');
		}


		$pdf->Output($file,'F');
		zipit($file);
}

function zipit($file){
	$pdf = './download/'.date('m').'/'.basename($file);
	$filename = './download/'.date('m').'/'.basename($file).'.zip';
	$dir =  './download/'.date('m').'/';
	
		if(!file_exists($file.'.zip')){

			$zip = new ZipArchive();
			if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
				exit("cannot open <$filename>\n");
			}
			$zip->addFile($pdf,basename($file));

			$zip->close();
			
			echo  '<a href="'.$filename.'"> download link</a>';
			
		}else{
			
			echo  '<a href="'.$filename.'"> download link</a>';

		}
}

function limitedownload(){
	if( !isset($_SESSION["download"]) ){
		$_SESSION["download"] = 10;
	}if($_SESSION["download"] == 0){
		echo "no more free download";die();
	}
	
	$_SESSION["download"] = $_SESSION["download"] -1;
	
}
?>


