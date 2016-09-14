<table>
	<tr>
		<td>
			<strong>Query Monitor Flamegraph</strong><br />
			This Query Monitor extension will add profiling framegraphs to Query Monitor via the <a href="https://pecl.php.net/package/xhprof">xhprof</a> PHP extension.
		</td>
		<td>
				</td>
	</tr>
	<tr>
		<td>
			A <strong><a href="https://hmn.md/">Human Made</a></strong> project. Maintained by @joehoyle.
		</td>
		<td align="center">
			<img src="https://hmn.md/content/themes/hmnmd/assets/images/hm-logo.svg" width="100" />
		</td>
	</tr>
</table>

![](http://joehoyle-captured.s3.amazonaws.com/dH6vR5pg.png)

## Install Instructions

1. Have the [Query Monitor](https://github.com/johnbillion/query-monitor) plugin installed and activated.
1. Have the [xhprof](https://pecl.php.net/package/xhprof) PHP extension installed.
1. Install this plugin :)
1. Add a call to `xhprof_sample_enable();` to your `wp-config.php`

## Versions of XHProf

Unfortunately XHProf is not actively maintained and has some shortcomings. Flamegraph makes use of the `xhprof_sample_enable()` function which by is hardcoded to 100ms intervals with the official XHProf extension. There's several forks and updates to XHProf, refer to the table below:

|Project|Compatible|Notes|
|---|-|---|
|[XHProf Official](https://pecl.php.net/package/xhprof)|yes|Note: Doesn't support PHP 7. Is restricted to 100ms interval sampling, making it useless for the most part.|
|[XHProf with configurable sampling](https://github.com/phacility/xhprof/pull/80)|yes|Best solution! However, you'll need to build from source.|
|[Tideways](https://tideways.io/profiler/xhprof-for-php7-php5.6)|no|Tideways is an XHProf fork, however doesn't support `*_sample_enabled`. See https://github.com/tideways/php-profiler-extension/issues/26.|
|[XHProf PHP7 fork](https://github.com/RustJason/xhprof/tree/php7)|no|Doesn't support `xhprof_sample_enabled`|

If you know of any other XHProf forks or compatible output libraries, be sure to let me know!
