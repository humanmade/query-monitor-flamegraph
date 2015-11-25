<table width="100%">
	<tr>
		<td align="left" width="70">
			<strong>Query Monitor Flamegraph</strong><br />
			This Query Monitor extension will add profiling framegraphs to Query Monitor via the <a href="http://anthonyterrien.com/forp/">forp</a> PHP extension.
		</td>
		<td align="right" width="20%">
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

![](https://s3.amazonaws.com/joehoyle-captured/NdPpy.png)

## Install Instructions

1. Have the [Query](https://github.com/johnbillion/query-monitor) Monitor plugin installed and activated.
1. Have the [forp](http://anthonyterrien.com/forp/) PHP extension installed.
1. Install this plugin :)
1. Add a call to `forp_start( FORP_FLAG_TIME );` to your `wp-config.php`

