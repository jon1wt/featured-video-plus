<?php
$options = array (
	'version'  => FVP_VERSION,
	'usage'    => 'replace',
	'align'    => 'center',
	'issingle' => false,
	'ishome'   => false,

	'sizing' => array(
		'wmode'  => 'auto',
		'hmode'  => 'auto',
		'width'  => 640,
		'height' => 360,
	),

	'default_args' => array(
		'general'     => array(),
		'vimeo'       => array(),
		'youtube'     => array(),
		'dailymotion' => array(),
	),
);

update_option( 'fvp-settings', $options );
