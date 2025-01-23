<?php

namespace App\Customlib;

  class item
    {
        private $name;
        private $price;
        private $dollarSign;

        public function __construct($cant= '',$name = '', $price = '', $dollarSign = true)
        {
            $this -> cant = $cant;
            $this -> name = $name;
            $this -> price = $price;
            $this -> dollarSign = $dollarSign;
        }

        public function __toString()
        {
            $rightCols = 7;
            $centerCols = 29;
            $leftCols = 4;
            $left = str_pad($this -> cant, $leftCols," ") ;
            $center = str_pad($this -> name, $centerCols," ", STR_PAD_BOTH) ;
            $sign = ($this -> dollarSign ? '$ ' : '');
            $right = str_pad($sign . $this -> price, $rightCols, ' ', STR_PAD_LEFT);
            return "$left$center$right\n";
        }
    }
