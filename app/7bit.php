<?php

class SevenBit {
    
    var $atoms;

    public function __construct()
    {


    }


    public function encode($str) 
    {

        $this->atoms = array();

        $str_ascii = URLify::downcode($str) . " ";

        $this->atoms[] = array('type' => 'text', '7bit' => $str_ascii);

        $this->encode7BitAtoms();

        $this->decode16BitAtoms();

        dump($this->atoms);

        // Put the atoms back together.
        $output = "";
        foreach ($this->atoms as $atom) {
            $output .= $atom['16bit'];
        }

        // Limit length to 134 chars. 
        $output = mb_substr($output, 0, 134);

        return $output . " #7bit";

    }




    public function decode($str) 
    {

        $this->atoms = array();

        //$str_ascii = URLify::downcode($str);
        $str = str_replace(" #7bit", "", $str);

        $this->atoms[] = array('type' => 'text', '16bit' => $str);

        $this->encode16BitAtoms();

        $this->decode7BitAtoms();


        dump($this->atoms);

        // Put the atoms back together.
        $output = "";
        foreach ($this->atoms as $atom) {
            $output .= $atom['7bit'];
        }

        return $output;

    }




    private function encode7BitAtoms() 
    {
        foreach($this->atoms as $key => $atom) {
            $this->atoms[$key]['bitstring'] = $this->bitstring7($atom['7bit']);
        }
    }


    private function encode16BitAtoms() 
    {
        foreach($this->atoms as $key => $atom) {
            $this->atoms[$key]['bitstring'] = $this->bitstring16($atom['16bit']);
        }
    }

    private function decode7BitAtoms() 
    {
        foreach($this->atoms as $key => $atom) {
            $this->atoms[$key]['7bit'] = $this->asciistring($atom['bitstring']);
        }
    }


    private function decode16BitAtoms() 
    {
        foreach($this->atoms as $key => $atom) {
            $this->atoms[$key]['16bit'] = $this->utfstring($atom['bitstring']);
        }
    }


    private function bitstring7($str) 
    {

        $bitstring = "";

        foreach(str_split($str) as $char) {
            $bitchar = sprintf("%07d", decbin(ord($char)));
            $bitstring .= $bitchar;
        }

        return $bitstring;

    }


    private function bitstring16($str) 
    {

        mb_internal_encoding("UTF-8");

        $bitstring = "";

        for ($i = 0; $i < mb_strlen($str); $i++ ) {
            $char = mb_substr($str, $i, 1);
            $charnum = decbin($this->uniord($char));
            if ($charnum != 0) {
                $bitchar = sprintf("%014s", decbin($this->uniord($char)));
                $bitstring .= $bitchar;
            }
        }

        return $bitstring;

    }    


    private function asciistring($bitstring)
    {
        $bitchars = explode("x", chunk_split($bitstring, 7, "x"));

        $string = "";

        foreach($bitchars as $bitchar) {
            $char = chr(bindec($bitchar));
            $string .= $char;
        }

        return $string;

    }

    private function utfstring($bitstring)
    {
        $bitchars = explode("x", chunk_split($bitstring, 14, "x"));

        $string = "";

        foreach($bitchars as $bitchar) {
            if (!empty($bitchar) && (intval($bitchar)!=0)) {
                $char = $this->unichr(bindec($bitchar));
                $string .= $char;
            }
        }

        return $string;

    }


    function unichr($u) {
        return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
    }

    function uniord($c) {
        $val = mb_convert_encoding($c , 'HTML-ENTITIES', 'UTF-8');
        $val = $this->int($val);
        return $val;
    }    

    function int($s){
        return($a=preg_replace('/[^\-\d]*(\-?\d*).*/','$1',$s))?$a:'0';
    }

}