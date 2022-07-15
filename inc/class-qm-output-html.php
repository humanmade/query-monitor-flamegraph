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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

*/

class QM_Output_Html extends \QM_Output_Html {

	public function __construct( \QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 110 );
	}

	public function output() {
		$time = ( new QM_Collector() )->get_sampling_interval();
		$cell_height = 18;
		$height = isset( $this->collector->max_depth ) ? $cell_height * $this->collector->max_depth : 540
		?>

		<div class="qm" id="qm-flamegraph">
			<div class="timestack-flamegraph">

			</div>
			<div id="qm-flamegraph-data" style="display: none"><?php echo json_encode( $this->collector->get_data()[0] ); ?></div>
			<style>
				.d3-flame-graph-tip {
					z-index: 99999;
				}
			</style>
			<script type="text/javascript">
				var flameGraph = d3.flameGraph()
					.height(<?php echo esc_js( $height ); ?>)
					// .width(960)
					.cellHeight(<?php echo esc_js( $cell_height ); ?>)
					.transitionDuration(350)
					.transitionEase('cubic-in-out')
					//.sort(true)
					.title("Flamegraph (<?php echo sprintf( '%dms intervals', $time / 1000 ) ?>)")

				// Example on how to use custom tooltips using d3-tip.
				var tip = d3.tip()
					.direction("s")
					.offset([8, 0])
					.attr('class', 'd3-flame-graph-tip')
					.html(function(d) { return "name: " + d.name + ", time: " + d.value + 'ms'; });

				flameGraph.tooltip(tip);
				data = JSON.parse( document.getElementById( 'qm-flamegraph-data').innerHTML )
					d3.select(".timestack-flamegraph")
					.datum(data)
					.call(flameGraph);
			</script>
		</div>
		<?php

	}

}
