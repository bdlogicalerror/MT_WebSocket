<?php
/**
 * Created for Amidex IT.
 * User: Munna
 * Date: 8/22/2017
 * Time: 5:59 PM
 */

class handle
{
    Public function doHandShake($data, $socket) {
        $headers = array();
        $lines = preg_split("/\r\n/", $data);
        foreach($lines as $line) {
            $line = rtrim($line);
            if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
                $headers[$matches[1]] = $matches[2];
        }

        $key = base64_encode(pack('H*', sha1($headers['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n";
        $upgrade .= "Upgrade: websocket\r\n";
        $upgrade .= "Connection: Upgrade\r\n";
        $upgrade .= "WebSocket-Origin: ".$this->host."\r\n";
        $upgrade .= "WebSocket-Location: ws://".$this->host.':'.$this->port."\r\n";
        $upgrade .= "Sec-WebSocket-Accept:$key\r\n\r\n";

        socket_write($socket,$upgrade,strlen($upgrade));
        return true;
    }

    Public function mtpack($text){
        if($text!==""){
            return $this->mask(json_encode($text));
        }
    }

    Public function Unmtpack($text){
        if($text!==''){
            return json_decode($this->unmask($text));
        }
    }

    protected function unmask($text) {
        $length = ord($text[1]) & 127;
        if($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }

        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i%4];
        }
        return $text;
    }

    protected function mask($text) {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);

        if($length <= 125)
            $header = pack('CC', $b1, $length);
        elseif($length > 125 && $length < 65536)
            $header = pack('CCn', $b1, 126, $length);
        elseif($length >= 65536)
            $header = pack('CCNN', $b1, 127, $length);

        return $header.$text;
    }
    public function get_key($needle,$array){
        foreach ($array as $key=>$val){
            if($needle==$val){
                return $key;
                break;
            }
        }

    }

}