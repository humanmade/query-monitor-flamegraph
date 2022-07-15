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
	// In milliseconds
	const EXCIMER_INTERVAL = 1;

	public $max_depth = null;
	public $id = 'flamegraph';
	public $profiler;

	public function name() {
		return __( 'Flamegraph', 'query-monitor' );
	}

	public function __construct() {
		if ( ! function_exists( 'xhprof_sample_enable' ) && ! class_exists( 'ExcimerProfiler' ) ) {
			return;
		}

		if ( class_exists( 'ExcimerProfiler' ) ) {
			$this->profiler = new \ExcimerProfiler;
			$this->profiler->setPeriod( self::EXCIMER_INTERVAL / 1000 );
			$this->profiler->setEventType( EXCIMER_CPU );
			$this->profiler->start();
		}
	}

	/**
	 * @return int In microseconds
	 */
	public function get_sampling_interval() {

		if ( function_exists('xhprof_sample_disable') ) {
			$time = (int) ini_get( 'xhprof.sampling_interval' );
		} elseif ( class_exists( 'ExcimerProfiler' ) ) {
			$time = self::EXCIMER_INTERVAL * 1000;
		} else {
			$time = 100000;
		}

		return $time;
	}

	public function process_xhprof() {
		$stack = xhprof_sample_disable();

		$this->data = $this->folded_to_hierarchical( $stack );
	}

	public function process_excimer() {
		$this->profiler->stop();

		$data = $this->excimer_to_hierarchical( $this->profiler->getLog() );

		$this->data = $data;
	}

	public function process() {
		if ( function_exists( 'xhprof_sample_disable' ) ) {
			$this->process_xhprof();
		}

		if ( class_exists( 'ExcimerProfiler' ) ) {
			$this->process_excimer();
		}
	}

	protected function excimer_to_hierarchical( $excimer_log ) {
		$nodes = [];

		foreach ( $excimer_log as $log_entry ) {
			$stack = $log_entry->getTrace();

			$named_stack = array_map(function ($line) {
				switch (true) {
					case isset($line['class']) && isset($line['function']):
						return $line['class'] . '::' . $line['function'];
					case isset($line['function']):
						return $line['function'];
					case isset($line['class']):
						return $line['class'];
					case isset($line['file']):
						return $line['file'];
					default:
						return 'Unknown';
				}
			}, $stack);

			$named_stack = array_reverse($named_stack);

			if ( count( $named_stack ) > $this->max_depth ) {
				$this->max_depth = count( $named_stack );
			}

			$nodes = $this->add_children_to_nodes($nodes, $named_stack);
		};

		$nodes = array( (object) array(
			'name' => 'main()',
			'value' => $nodes[0]->value,
			'children' => $nodes,
		) );

		return $nodes;
	}

	protected function folded_to_hierarchical( $stack ) {

		$nodes = array( (object) array(
			'name' => 'main()',
			'value' => 1,
			'children' => [],
		) );

		foreach ( $stack as $time => $call_stack ) {
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
		$time = $this->get_sampling_interval();

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
