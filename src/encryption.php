<?php
define('USER_PASS', 'klfiojret435904359043590kfdjis89esr8*#&^#^#*(#KJSHS^&#@()@KSJS^&#@*()()_#KDSJ#&89jgdfkgidfgdfg84w3889kf6');
define('ADV_PASS', 'eiwetr43905435k43504359959465032ij43289di(##$*&^&#JHDNHS^T%@*(@((@)KLRKMdasfu73878478934834dsfjfdsdsfjkfff');
define('EMP_PASS', 's14gdgkgjmij59k4g4fiojret4359043msfsfs#&^#*^#*(#XKRR^&#@()@KSJS^&#@*()()_#KDSJ#&snjdv38vnhvjksdnfjsddi44jfn');
define('ADMIN_PASS','8324583245i3b5858h89*(*$($@*$ BHVG@$*6767478843758bnb8r5438943854353845jjfuwerwerklERERUIRRUHUIRUUIRUIRU^a'); 

function get_rnd_iv($iv_len)
{
   $iv = '';
   while ($iv_len-- > 0) {
       $iv .= chr(mt_rand() & 0xff);
   }
   return $iv;
}

function md5_encrypt($plain_text, $password, $iv_len = 16)
{
   $plain_text .= "\x13";
   $n = strlen($plain_text);
   if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
   $i = 0;
   $enc_text = get_rnd_iv($iv_len);
   $iv = substr($password ^ $enc_text, 0, 512);
   while ($i < $n) {
       $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
       $enc_text .= $block;
       $iv = substr($block . $iv, 0, 512) ^ $password;
       $i += 16;
   }
   return base64_encode($enc_text);
}



// $p = "JGFYGwJnzK2y7mpcwUIVVNxfC9Cn5/9hqLxHb8TR+yg=";
// $pass  = USER_PASS;
// echo md5_encrypt($p, $pass);

function md5_decrypt($enc_text, $password, $iv_len = 16)
{
   $enc_text = base64_decode($enc_text);
   $n = strlen($enc_text);
   $i = $iv_len;
   $plain_text = '';
   $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
   while ($i < $n) {
       $block = substr($enc_text, $i, 16);
       $plain_text .= $block ^ pack('H*', md5($iv));
       $iv = substr($block . $iv, 0, 512) ^ $password;
       $i += 16;
   }
   return preg_replace('/\\x13\\x00*$/', '', $plain_text);
}

function url_enc($url){
    $url = base64_encode($url);
    $url = rawurlencode($url);
    return $url;
}

function url_dec($url){
    $url = rawurldecode($url);
    $url = base64_decode($url);
    return $url;
}
?>