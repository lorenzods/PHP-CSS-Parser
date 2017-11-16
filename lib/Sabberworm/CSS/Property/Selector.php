<?php

namespace Sabberworm\CSS\Property;

/**
 * Class representing a single CSS selector. Selectors have to be split by the comma prior to being passed into this class.
 */
class Selector {

	//Regexes for specificity calculations
	const NON_ID_ATTRIBUTES_AND_PSEUDO_CLASSES_RX = '/
	(\.[\w]+)                   # classes
	|
	\[(\w+)                     # attributes
	|
	(\:(                        # pseudo classes
		link|visited|active
		|hover|focus
		|lang
		|target
		|enabled|disabled|checked|indeterminate
		|root
		|nth-child|nth-last-child|nth-of-type|nth-last-of-type
		|first-child|last-child|first-of-type|last-of-type
		|only-child|only-of-type
		|empty|contains
	))
	/ix';

	const ELEMENTS_AND_PSEUDO_ELEMENTS_RX = '/
	((^|[\s\+\>\~]+)[\w]+   # elements
	|
	\:{1,2}(                # pseudo-elements
		after|before|first-letter|first-line|selection
	))
	/ix';

	private $sSelector;
	private $iSpecificity;
	private $aSegments = null;

	public function __construct($sSelector, $bCalculateSpecificity = false) {
		$this->setSelector($sSelector);
		if ($bCalculateSpecificity) {
			$this->getSpecificity();
		}
	}

	public function getSelector() {
		return $this->sSelector;
	}

	public function setSelector($sSelector) {
		$this->sSelector = trim($sSelector);
		$this->iSpecificity = null;
	}

	public function __toString() {
		return $this->getSelector();
	}


	public function getSegmentedPathFromString( $str ) {
		$delim = ' >+~';
		$retval = array();
		$s = preg_replace('!\s+!', ' ', $str );
		$s = str_replace( ' >','>',$s );
		$s = str_replace( '> ','>',$s );
		$s = str_replace( ' +','+',$s );
		$s = str_replace( '+ ','+',$s );
		$s = str_replace( ' ~','~',$s );
		$s = str_replace( '~ ','~',$s );
		$a = preg_split( '/([\s>\+\~])/' , $s , -1 , PREG_SPLIT_DELIM_CAPTURE );
		$even = true;
		foreach ($a as $segment) {
			if ($even) {
				$retval[] = new SelectorSegment( $segment );
			} else {
				$retval[] = new SelectorOperator( $segment );
			}
			$even=!$even;
		}
		return $retval;
	}


	public function getSegments() {
		if ($this->aSegments === null) {
			$this->aSegments = $this->getSegmentedPathFromString( $this->sSelector );
		}
		return $this->aSegments;
	}

	public function getSpecificity() {
		if ($this->iSpecificity === null) {
			$a = 0;
			/// @todo should exclude \# as well as "#"
			$aMatches = null;
			$b = substr_count($this->sSelector, '#');
			$c = preg_match_all(self::NON_ID_ATTRIBUTES_AND_PSEUDO_CLASSES_RX, $this->sSelector, $aMatches);
			$d = preg_match_all(self::ELEMENTS_AND_PSEUDO_ELEMENTS_RX, $this->sSelector, $aMatches);
			$this->iSpecificity = ($a * 1000) + ($b * 100) + ($c * 10) + $d;
		}
		return $this->iSpecificity;
	}

	public function isSegmentsMatching( $ts ) {
		$os = $this->getSegments();
		// minimal, almost dumb design (only tags and classes supported)
		foreach ($ts as $target_segment) {
			if (!($target_segment instanceof SelectorSegment)) {
				continue;
			}
			if ($target_segment->hasTag()) {
				if (!$target_segment->hasAnyClass()) { // css class: h2
					foreach ($os as $s) {
						if (!($s instanceof SelectorSegment)) continue;
						if ($target_segment->getTag() != $s->getTag()) {
							continue;
						}
						return true;
					}
				} else {	// css class: h2.modal-panel
					foreach ($os as $s) {
						if (!($s instanceof SelectorSegment)) continue;
						if ($target_segment->getTag() != $s->getTag()) {
							continue;
						}
						foreach ($s->getClasses() as $cls) {
							if ($os->hasSpecificClass( $cls )) {
								return true;
							}
						}
					}
				}
			} else {
				if ($target_segment->hasAnyClass()) {	// css class: .modal-panel
					foreach ($os as $s) {
						if (!($s instanceof SelectorSegment)) continue;
						foreach ($target_segment->getClasses() as $cls) {
							if ($s->hasSpecificClass( $cls )) {
								return true;
							}
						}
					}

				}
			}
		}
		return false;
	}

	public function matchesWithCSSPath( $css_path ) {
		// imbecil checking
		$search_segments = $this->getSegmentedPathFromString( $css_path );
		return $this->isSegmentsMatching( $search_segments );
	}

}
