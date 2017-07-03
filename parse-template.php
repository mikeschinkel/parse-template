<?php

function parseTemplate( $template, $data ) {

	if ( false !== $pos = strrpos( $template, '}}' ) ) {
		$left = substr( $template, 0, $pos );
		$right = substr( $template, $pos + 2 );
		$left = parseTemplate( $left, $data );
		$template = parseTemplate( "{$left}{$right}", $data );
		return $template;
	}

	if ( false !== $pos = strrpos( $template, '{{' ) ) {
		$left = substr( $template, 0, $pos );
		$right = substr( $template, $pos + 2 );
		$right = parseTemplate( $right, $data );
		return "{$left}{$right}";
	}

	if ( preg_match('#^(\.[\.a-z_]+)$#' , $template ) ) {
		$value = extractData( $template, $data );
		if ( is_null( $value ) ) {
			return $template;
		}
		return $value;
	}

	return $template;

}

/**
 * Extracts value from $data using $path
 *
 * @example:
 *
 *      extractData( '.foo.bar', [ 'foo' => [ 'bar' => 'baz' ] ] );
 *
 *          =>
 *
 *      returns 'baz'
 *
 * @param string $path
 * @param array $data
 *
 * @return null|mixed
 */
function extractData( $path, $data ) {

	$parts = explode( '.', $path );
	do {
		if ( 0 === count( $parts ) ) {
			$data = null;
		}
		if ( 1 >= count( $parts ) ) {
			break;
		}
		if ( ! isset( $data[ $parts[ 1 ] ] ) ) {
			$data = null;
			break;
		}
		$data = $data[ $parts[ 1 ] ];
		if ( 2 === count( $parts ) ) {
			break;
		}
		unset( $parts[ 1 ] );
		$parts = array_values( $parts );

	} while ( false );

	return $data;

}

$template=<<<TEMPLATE
x = "{{.box.version}}"
y = 10
z =  "{{.hosts.list.{{.hosts.roles.local}}.domain}}"
abc = 50
TEMPLATE;
$data = [
	'box' => [ 
		'version' => '0.14.0' 
	],
	'hosts' => [
		'roles' => [ 
			'local' => 'local' 
		],
		'list' => [
			'local' => [
				'domain' => 'example.dev'
			],
		],
	],
];
echo parseTemplate( $template, $data );


