<?php
// This file is generated. Do not modify it manually.
return array(
	'track-timetable' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'bm/track-timetable',
		'title' => 'Track Timetable',
		'description' => 'Show a timetable for a single track.',
		'category' => 'event',
		'textdomain' => 'bm-event',
		'keywords' => array(
			'calendar',
			'events',
			'agenda'
		),
		'attributes' => array(
			'track_id' => array(
				'type' => 'number',
				'default' => 0
			),
			'showTimezoneSelector' => array(
				'type' => 'boolean',
				'default' => false
			)
		),
		'supports' => array(
			'anchor' => true,
			'align' => false
		),
		'styles' => array(
			array(
				'name' => 'list',
				'label' => 'Agenda',
				'isDefault' => true
			),
			array(
				'name' => 'grid',
				'label' => 'Visual Grid'
			)
		),
		'editorScript' => 'file:../../dist/blocks/track-timetable.js',
		'editorStyle' => 'bm-event-block',
		'style' => 'bm-event-public',
		'viewScriptModule' => 'file:../../dist/blocks/track-timetable/view.js',
		'render' => 'file:./render.php'
	)
);
