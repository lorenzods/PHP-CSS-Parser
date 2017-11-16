<?php

class SelectorOperator {
	const OP_GENERAL_CHILD = ' ';
	const OP_IMMEDIATE_CHILD = '>';
	const OP_GENERAL_SIBLING = '~';
	const OP_IMMEDIATE_SIBLING = '+';
	protected $sOperator = null;

	public function __construct( $str ) {
		$this->sOperator = $str;
	}

	public function getOperator() {
		return $this->sOperator;
	}
}
