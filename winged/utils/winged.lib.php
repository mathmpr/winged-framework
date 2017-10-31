<?php 
class wl{
	public static function convertslash($str){
        return trim(str_replace("\\", "/", $str));
    }

	public static function dotslash($str, $give = false){
        $str = trim($str);
        if($str != ""){
            if($give){
                if($str[strlen($str) - 1] != "/"){
                    $str .= "/";
                }
                if($str[0] != "." && $str[1] != "/"){
                    $str = "./" . $str;
                }
                return trim($str); 
            }else{
                if($str[strlen($str) - 1] == "/"){
                    $str[strlen($str) - 1] = "";
                }
                return trim(str_replace("./", "", $str));
            }
        }
        return false;
    }

    public static function slashexplode($str){
        if(strlen($str) >= 2){
            if($str[0] == "." && $str[1] == "/"){
                $str[0] = "";
                $str[1] = "";
            }
        }
        if($str[0] == "/"){
            $str[0] = "";
        }
        if($str[strlen($str) - 1] == "/"){
            $str[strlen($str) - 1] = "";
        }
        if(trim($str) != ""){
            return explode("/", trim($str));
        }
        return array();
    }

    public static function resetarray($arr){
    	$new = array();    	
    	foreach ($arr as $key => $value) {
    		array_push($new, $value);
    	}    	
    	return $new;
    }

}