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

class QM_Output_Html extends \QM_Output_Html {

	public function __construct( \QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 110 );
	}

	public function output() {

		if ( ! $dump = $this->get_dump() ) {
			return "To forp() dump found, did you all forp_init()?";
		}

		$stack = $this->make_stack_hierachical( $dump['stack'] );
		?>

		<div class="qm" id="qm-flamegraph">
			<h1><?php echo $stack['usec'] / 1000 ?> Milliseconds</h1>
			<div class="timestack-flamegraph">
				<?php $this->render_split_timeline( $stack ) ?>
			</div>
		</div>
		<?php

	}

	protected function get_dump() {
		if ( empty( $this->dump ) ) {

			// forp uses a lot of memory!
			ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );
			forp_end();
			$this->dump = forp_dump();

			if ( empty( $this->dump['stack'] ) ) {
				$this->dump = null;
			}
		}

		return $this->dump;
	}

	protected function make_stack_hierachical( $stack ) {

		foreach ( $stack as $key => $item ) {
			if ( $item['usec'] < 1000 ) {
				unset( $stack[$key] );
			}
		}

		$parent = $stack[0];

		$parent['children'] = $this->get_children( 0, $stack );
		return $parent;
	}

	protected function get_children( $id, $stack ) {

		$children = wp_list_filter( $stack, array( 'parent' => $id ) );

		foreach ( $children as $id => &$child ) {
			$child['children'] = $this->get_children( $id, $stack );
		}

		return $children;
	}

	private function render_split_timeline( $stack ) {
		?>
		<ul class="timestack-split-timeline">
			<?php foreach ( $stack['children'] as $child ) :
				$percent = $child['usec'] / $stack['usec'] * 100;
				$this->render_timeline_block( $percent, $child );
			endforeach; ?>
		</ul>
		<?php
	}

	private function render_timeline_block( $percent, $stack ) {

		?>

			<li class="type-operation"
				title="<?php echo esc_attr( $this->get_name( $stack ) ) ?>&#013;Time: <?php echo $stack['usec'] / 1000; ?> ms"
				data-name="<?php echo esc_attr( $this->get_name( $stack ) ) ?>"
				data-time="<?php echo esc_attr( $stack['usec'] / 1000 ) ?>"
				style="width: <?php echo floatval( $percent ); ?>%; background-color: <?php echo esc_attr( $this->string_to_color( $stack['function'] ) ) ?>;">
				<?php
				if ( $stack['children'] ) {
					$this->render_split_timeline( $stack );
				}
				?>
			</li>
		<?php
	}

	public function string_to_color( $str ) {

		if ( isset( $this->colors[$str] ) ) {
			return $this->colors[$str];
		}

		$colors = array(
			'rgb(201, 90, 41)',
			'rgb(208, 207, 56)',
			'rgb(212, 164, 51)',
			'rgb(229, 103, 47)',
			'rgb(200, 120, 44)',
			'rgb(205, 123, 45)',
			'rgb(194, 34, 36)',
			'rgb(225, 60, 43)',
			'rgb(202, 42, 38)',
			'rgb(235, 136, 51)',
			'rgb(197, 35, 41)',
			'rgb(223, 116, 51)',
			'rgb(229, 91, 46)',
			'rgb(203, 130, 59)',
			'rgb(214, 100, 45)',
			'rgb(231, 163, 53)',
			'rgb(196, 34, 37)',
			'rgb(232, 148, 56)'
		);

		return $this->colors[$str] = $colors[array_rand( $colors )];
	}

	public function get_name( $item ) {
		$name = '';
		if ( ! empty( $item['class'] ) ) {
			$name = $item['class'] . ':';
		}

		if ( $item['function'] == '{closure}' ) {
			$name .= $this->make_file_relative( $item['file'] ) . ':' . ( ! empty( $item['lineno'] ) ? $item['lineno'] : '' );
		} else if ( in_array( $item['function'], array( 'require_once', 'include_once', 'require', 'include' ) ) ) {
			$name .= $this->make_file_relative( $item['file'] );
		} else if ( $item['function'] === 'do_action' ) {
			$name .= 'action: ' . $this->make_file_relative( $item['file'] ) . ( ! empty( $item['lineno'] ) ? $item['lineno'] : '' );
		} else {
			$name .= $item['function'];
		}

		return $name;
	}

	protected function make_file_relative( $file ) {
		return str_replace( array( ABSPATH, WP_CONTENT_DIR ), '', $file );
	}
}