<?php

namespace Sabberworm\CSS\Property;

class SelectorSegment {
	private $sTag = null;
	private $aIds = null;
	private $aClasses = array();
	private $aPseudoClasses = array();

	public function __construct( $str ) {
		// search for pseudoclasses
		$pos = strpos( $str , ':' );
		if ($pos!==false) {
			$x = explode( ':' , $str );
			array_shift( $x );
			$this->aPseudoClasses = $x;
			$str = substr( $str , 0 , $pos );
		}

		// search for classes
		$pos = strpos( $str , '.' );
		if ($pos!==false) {
			$x = explode( '.' , $str );
			array_shift( $x );
			$this->aClasses = $x;
			$str = substr( $str , 0 , $pos );
		}
		// search for ids
		$pos = strpos( $str , '#' );
		if ($pos!==false) {
			$x = explode( '#' , $str );
			array_shift( $x );
			$this->aIds = $x;
			$str = substr( $str , 0 , $pos );
		}
		// last remain: tag
		if (!empty( $str )) {
			$this->sTag = $str;
		}
	}

	public function hasTag() {
		return (!empty( $this->sTag ));
	}

	public function getTag() {
		return $this->sTag;
	}

	public function hasAnyClass() {
		return (!empty( $this->aClasses ));
	}

	public function getClasses() {
		if (empty( $this->aClasses )) {
			return array();
		}
		return $this->aClasses;
	}

	public function hasSpecificClass( $cls ) {
		if (empty( $this->aClasses )) {
			return false;
		}
		return in_array( $cls , $this->aClasses );
	}

	public function hasAnyIds() {
		return (!empty( $this->aIds ));
	}

	public function hasSpecificId( $id ) {
		if (empty( $this->aIds )) {
			return false;
		}
		return in_array( $id , $this->aIds );
	}

}
