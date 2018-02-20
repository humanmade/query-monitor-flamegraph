<?php

namespace QM_Flamegraph;

/*
Copyright 2009-2015 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class QM_Collector extends \QM_Collector {

	public $id = 'flamegraph';

	public function name() {
		return __( 'Flamegraph', 'query-monitor' );
	}

	public function __construct() {
		if ( ! function_exists( 'xhprof_sample_enable' ) ) {
			return;
		}
	}

	public function process() {

		if ( ! function_exists( 'xhprof_sample_disable' ) ) {
			return;
		}

		$stack = xhprof_sample_disable();

		$this->data = $this->folded_to_hierarchical( $stack );
	}

	protected function folded_to_hierarchical( $stack ) {

		$nodes = array( (object) array(
			'name' => 'main()',
			'value' => 1,
			'children' => [],
		) );

		foreach ( $stack as $time => $call_stack ) {
			$call_stack = explode( '==>', $call_stack );


			$nodes = $this->add_children_to_nodes( $nodes, $call_stack );
		}

		return $nodes;
	}

	/**
	 * Accepts [ Node, Node ], [ main, wp-settings, sleep ]
	 */
	protected function add_children_to_nodes( $nodes, $children ) {
		$last_node = $nodes ? $nodes[ count( $nodes ) - 1 ] : null;
		$this_child = $children[0];
		$time = (int) ini_get( 'xhprof.sampling_interval' );

		if ( ! $time ) {
			$time = 100000;
		}
		if ( $last_node && $last_node->name === $this_child ) {
			$node = $last_node;
			$node->value += ( $time / 1000 );
		} else {
			$nodes[] = $node = (object) array(
				'name'	=> $this_child,
				'value' => $time / 1000,
				'children' => array(),
			);
		}
		if ( count( $children ) > 1 ) {
			$node->children = $this->add_children_to_nodes( $node->children, array_slice( $children, 1 ) );
		}

		return $nodes;

	}
}
