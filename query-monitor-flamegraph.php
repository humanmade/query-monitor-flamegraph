<?php

namespace QM_Flamegraph;
/**
 * Plugin Name: Query Monitor Flamegraph
 * Description: Add a flamegraph to Query Monitor
 * Author: Joe Hoyle | Human Made
 */

function register_qm_collector( array $collectors, \QueryMonitor $qm ) {
	include_once dirname( __FILE__ ) . '/inc/class-qm-collector.php';
	$collectors['flamegraph'] = new QM_Collector;
	return $collectors;
}

add_filter( 'qm/collectors', 'QM_Flamegraph\register_qm_collector', 20, 2 );


function register_qm_output( array $output, \QM_Collectors $collectors ) {
	if ( $collector = \QM_Collectors::get( 'flamegraph' ) ) {
		include_once dirname( __FILE__ ) . '/inc/class-qm-output-html.php';
		$output['flamegraph'] = new QM_Output_Html( $collector );
	}
	return $output;
}

add_filter( 'qm/outputter/html', 'QM_Flamegraph\register_qm_output', 120, 2 );

add_action( 'wp_enqueue_scripts', 'QM_Flamegraph\enqueue_styles', 999 );
add_action( 'admin_head', 'QM_Flamegraph\enqueue_styles', 999 );

function enqueue_styles() {
	wp_enqueue_style( 'qm-flamegraph', plugins_url( 'css/flamegraph.css', __FILE__ ) );
}