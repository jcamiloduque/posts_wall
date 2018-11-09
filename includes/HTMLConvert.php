<?php

if(class_exists('URLHelper') != true)include_once "URLHelper.php";
class HTMLConvert {
	
	private $string = "";
	private $result = array();
	private $script = "";
	private $module='';
	private $standard=array();
	
	public function __construct(array $options = null){
        if (is_array($options)){
            $this->setOptions($options);
        }
        $this->script=BASE_URL."/".($this->module!=""?$this->module."/":"");
    }
    
	public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
            	//Llamar el método
                $this->$method($value);
            }
        }
        //Se devuelve el objeto
        return $this;
    }
    
    public function __set($name, $value){
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)){
            throw new Exception('Propiedad invalida');
        }
        $this->$method($value);
    }
    
	public function __get($name){
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)){
            throw new Exception('Propiedad invalida');
        }
        return $this->$method();
    }
    
    public function setString($text){
    	$this->string=$text;
    }
	
	public function setStandard($text){
    	$this->standard=$text;
    }
    
    public function getString(){
    	return $this->string;
    }

	public function getModule(){
    	return $this->module;
    }
    
    public function setModule($value){
    	$this->module=$value;
    	
    }
    
    public function getArray(){
    	$doc = new DOMDocument();
    	@$doc->loadHTML('<?xml encoding="UTF-8">' .str_replace("<![CDATA[","",$this->string));
		// dirty fix
		foreach ($doc->childNodes as $item)if ($item->nodeType == XML_PI_NODE)$doc->removeChild($item); 
		// remove hack
		$doc->encoding = 'UTF-8'; // insert proper
        $this->result=array();
        $this->result['childNodes']=array();
        $this->result['attributes']=array();
        $this->result['tagName']="html";
        $this->result['text']="";
        if($doc->getElementsByTagName("head")->item(0)!=null)array_push($this->result['childNodes'],$this->execute($doc->getElementsByTagName("head")->item(0)));
        if($doc->getElementsByTagName("body")->item(0)!=null)array_push($this->result['childNodes'],$this->execute($doc->getElementsByTagName("body")->item(0)));
    	return $this->result;
    }
    
    private function execute($value){
    	$tmp=array();
    	$tmp['tagName']=@$value->nodeName;
    	$text='';
    	if($tmp['tagName']=='#text' || $tmp['tagName']=='#comment' || $tmp['tagName']=='#cdata-section'){
            $text=$value->textContent;
            if(trim($text)=="")return null;
        }
    	$tmp['text']=$text;
    	$tmp3=array();
    	$attributes=@$value->attributes;
    	if(isset($attributes))if($attributes->length){
		    foreach ($attributes as $attrName => $attrNode) {
		    	$atr=array();
		    	$atr['name']=$attrName;
		    	$atr['value']=$attrNode->value;
		        array_push($tmp3,$atr);
		    }
    	}
	    $tmp['attributes']=$tmp3;
    	$tmp2=array();
    	$nodes=@$value->childNodes;
    	if(isset($nodes))if($nodes->length){
		    foreach($nodes as $i) {
				$ts=$this->execute($i);
		     	if(isset($ts))array_push($tmp2,$ts);
		    }
    	}
	    $tmp['childNodes']=$tmp2;
    	return $tmp;
    }
	
	public function addStandard($value){
		if(!is_array($value))return -1;
		return array_push($this->standard,$value);
	}
	
	public function standardValue($value){
		if(!isset($value[0]))return NULL;
		foreach ($this->standard as $standard) {
			if(ucfirst($standard["value"])==ucfirst($value[0])){
				$tmp = array("module"=>$standard["module"],"controller"=>$standard["controller"],"action"=>$standard["action"]);
				$tmp2=array();
				if (count($value)>1){
					$i=1;
					if(isset($standard["values"]))
					if(is_array($standard["values"]))
					foreach ($standard["values"] as $val) {
						if(isset($value[$i])){
							$tmp2[$val]=$value[$i];
							$i++;
						}else break;
					}
				}
				$tmp["values"]=$tmp2;
				return $tmp;
			}
		}
		return NULL;
	}

    /**
     * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
     * array containing the HTTP server response header fields and content.
     */
    private static function get_web_page( $url )
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_USERAGENT      => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)", // who am i
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_VERBOSE        => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_MAXREDIRS      => 10,
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'method'=>"GET",
            'header'=>"Accept: text/html,application/xhtml+xml,application/xml\r\n" .
            "Accept-Charset: ISO-8859-1,utf-8\r\n" .
            "Accept-Encoding: gzip,deflate,sdch\r\n" .
            "Accept-Language: en-US,en;q=0.8\r\n",
            'user_agent'=>"User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.66 Safari/535.11\r\n"
        ));
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }

    public static function getPageMetadata($url){
        $result = self::get_web_page( $url );
        if ( $result['errno'] != 0 )return array();
        if ( $result['http_code'] != 200 )return array();

        $data = $result['content'];

        if($data == false)return array();
        $meta = array();
        $data = preg_replace('#<script(.*?)>(.*?)</script>#is', "", $data);
        $data = preg_replace('#<noscript(.*?)>(.*?)</noscript>#is', "", $data);
        preg_match_all("/[^>\"]*meta[^>]*description[^>]*[\/]?[^>]*>/si",$data, $description,PREG_PATTERN_ORDER);

        for($i=0;$i<count($description);$i++){
            $meta["description"] = preg_replace("/.*content[^=]*=\s*(\s*(?:\"(?:[^\"\\\\]+|\\\\.)*\"|'(?:[^'\\\\]+|\\\\.)*')(?:\s*,\s*(?:\"(?:[^\"\\\\]+|\\\\.)*\"|'(?:[^'\\\\]+|\\\\.)*'))*\s*).*/ius", "$1", $description[$i]);
        }
        $tmp = '';
        if(is_array($meta["description"])){
            foreach($meta["description"] as $desc){
                $_tl = strlen($desc);
                if($_tl>2) {
                    $desc = substr($desc, 1, -1);
                    if (is_string($desc) && $desc != '' && $_tl > strlen($tmp)) {
                        $tmp = $desc;
                    }
                }
            }
        }
        $meta["description"]=$tmp;
        preg_match_all("/[^>\"]*meta[^>]*image[^>]*[\/]?[^>]*>/si",$data, $image,PREG_PATTERN_ORDER);
        for($i=0;$i<count($image);$i++){
            $meta["image"] = preg_replace("/.*content.*=.*[\"\'](.*)[\"\'].*/is", "$1", $image[$i]);
        }
        if(!is_array($meta["image"]))$meta["image"] = array();
        preg_match_all("/<[^>\"]*img[^>]*src[^>]*[\/]?[^>]*>/si",$data, $image,PREG_PATTERN_ORDER);
        if(isset($image[0]))$_images = preg_replace("/.*src[\s]*=[^\"\']*[\"\']([^\"\']*)[\"\'].*/is", "$1", $image[0]);

        foreach($_images as $dt){
            $ur = URLHelper::url_to_absolute($url,$dt);
            if($ur!=false){
                array_push($meta["image"],$ur);
            }
        }
        if(count($meta["image"])==0)unset($meta["image"]);
        else $meta["image"] = array_values(array_unique($meta["image"]));

        preg_match_all("/<[^>\"]*meta[^>]*title[^>]*[\/]?[^>]*>/si",$data, $title,PREG_PATTERN_ORDER);

        for($i=0;$i<count($title);$i++){
            $meta["title"] = preg_replace("/.*content[^=]*=\s*(\s*(?:\"(?:[^\"\\\\]+|\\\\.)*\"|'(?:[^'\\\\]+|\\\\.)*')(?:\s*,\s*(?:\"(?:[^\"\\\\]+|\\\\.)*\"|'(?:[^'\\\\]+|\\\\.)*'))*\s*).*/ius", "$1", $title[$i]);
        }
        $tmp = '';
        if(is_array($meta["title"])){
            foreach($meta["title"] as $desc){
                $_tl = strlen($desc);
                if($_tl>2) {
                    $desc = substr($desc, 1, -1);
                    if (is_string($desc) && $desc != '' && $_tl > strlen($tmp)) {
                        $tmp = $desc;
                    }
                }
            }
        }
        $meta["title"]=$tmp;
        if(preg_match_all("/<[^>\"]*meta[^>]*(name|property)[^>\"]*=[^>\"]*\"og[^>\"]*\"[\/]?[^>]*>/si",$data, $og,PREG_PATTERN_ORDER)){
            $meta["og"]=array();
            for($i=0;$i<count($og[0]);$i++){
                $meta["og"][preg_replace("/.*(name|property)[^>\"]*=[^>\"]*\"(og[^>\"]*)\".*/is", "$2", $og[0][$i])] = preg_replace("/.*content.*=.*[\"\'](.*)[\"\'].*/is", "$1", $og[0][$i]);
            }
        }
        if(preg_match_all("/<[^>\"]*meta[^>]*name[^>\"]*=[^>\"]*\"item[^>\"]*\"[\/]?[^>]*>/si",$data, $og,PREG_PATTERN_ORDER)){
            $meta["item"]=array();
            for($i=0;$i<count($og[0]);$i++){
                $meta["item"][preg_replace("/.*name[^>\"]*=[^>\"]*\"(item[^>\"]*)\".*/is", "$1", $og[0][$i])] = preg_replace("/.*content.*=.*[\"\'](.*)[\"\'].*/is", "$1", $og[0][$i]);
            }
        }
        if($meta["title"]==""){
            preg_match_all("/<[^>\"]*title[^>]*>[^<]*<[^>]*\/[^>]*title[^>]*>/si",$data, $title,PREG_PATTERN_ORDER);
            for($i=0;$i<count($title);$i++){
                $meta["title"] = preg_replace("/<[^>]*title[^>]*>([^<>]*)<[^>]*\/[^>]*title[^>]*>/si", "$1", $title[$i]);
            }
            if(is_array($meta["title"])){
                if(isset($meta["title"][0])){
                    $meta["title"]=$meta["title"][0];
                }
                else $meta["title"]="";
            }
        }
        if($meta["description"]==""){
            $cont = false;
            preg_match_all("/<[^>\"]*div[^>]*itemprop[^>\"]*=[^>\"]*\"description\"[^>]*>(.*?)<[^>]*\/div[^>]*>/si",$data, $title,PREG_PATTERN_ORDER);
            if(isset($title[0])){
                if($title[0]!=""&&$title[0]!=null){
                    $data = $title[0];
                    if(is_array($data)){
                        if(isset($data[0]))$data=$data[0];
                        else $data="";
                    }
                    $cont = true;
                }
            }
            if($cont){
                if($meta["description"]==""){
                    preg_match_all("/<[^>]*blockquote[^>]*>.*<[^>]*\/[^>]*blockquote[^>]*>/si",$data, $title,PREG_PATTERN_ORDER);
                    if(isset($title[0]))$meta["description"] = preg_replace('/<[^>]*>/', '',$title[0]);
                    if(is_array($meta["description"])){
                        if(isset($meta["description"][0])){
                            $meta["description"]=$meta["description"][0];
                        }
                        else $meta["description"]="";
                    }
                }
                if($meta["description"]==""){
                    preg_match_all("/<[^>\"]*strong[^>]*>.*<[^>]*\/[^>]*strong[^>]*>/si",$data, $title,PREG_PATTERN_ORDER);
                    if(isset($title[0]))$meta["description"] = preg_replace('/<[^>]*>/', '',$title[0]);
                    if(is_array($meta["description"])){
                        if(isset($meta["description"][0])){
                            $meta["description"]=$meta["description"][0];
                        }
                        else $meta["description"]="";
                    }

                }
            }

            if($cont&&$meta["description"]==""){
                $meta["description"] = preg_replace('/<[^>]*>/', '',$data);
                if(is_array($meta["description"])){
                    if(isset($meta["description"][0])){
                        $meta["description"]=$meta["description"][0];
                    }
                    else $meta["description"]="";
                }
            }
        }
        if($meta["description"]==""){
            preg_match_all("/<p>(?<=^|>)[^><]{50,}?(?=<|$)<\/p>/si",$data, $title,PREG_PATTERN_ORDER);
            if(isset($title[0]))$meta["description"] = preg_replace('/<[^>]*>/', '',$title[0]);
            if(is_array($meta["description"])){
                if(isset($meta["description"][0])){
                    $meta["description"]=$meta["description"][0];
                }
                else $meta["description"]="";
            }
        }
        $meta["description"] = html_entity_decode (trim($meta["description"]));
        $meta["description"] = StringOptions::limit_text_by_letters(preg_replace('/\s+/',' ',$meta["description"]),300,true);
        $meta["title"] = html_entity_decode (StringOptions::limit_text_by_words($meta["title"],20,false));
        $meta["host"] = parse_url($url)["host"];
        if(isset($meta["og"])&&is_array($meta["og"])&&isset($meta["og"]["og:video:height"])&&str_replace("www.","",strtolower($meta["host"]))=="reverbnation.com")$meta["og"]["og:video:minHeight"] = $meta["og"]["og:video:height"];
        $meta["url"] = $url;
        return $meta;
    }
}

?>