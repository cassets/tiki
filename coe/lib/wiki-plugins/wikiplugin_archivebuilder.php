<?php

function wikiplugin_archivebuilder_info() {
	return array(
		'name' => tra('Archive Builder'),
		'description' => tra('Builds a zip archive containing the specified data from tikiwiki.'),
		'prefs' => array( 'wikiplugin_archivebuilder' ),
		'body' => tra('Description of the archive content. Multiple handlers are available for content types. One per line. Ex: page-as-pdf:some-folder/foo.pdf:HomePage , tracker-attachments:target-folder/:3435'),
		'params' => array(
			'name' => array(
				'name' => tra('Archive name'),
				'description' => tra('Upon download, the name of the file that will be provided.'),
				'required' => true,
			),
		),
	);
}

function wikiplugin_archivebuilder( $data, $params ) {
	if( ! class_exists( 'ZipArchive' ) ) {
		return '^' . tra('Missing extension zip.') . '^';
	}

	$archive = md5( serialize( array( $data, $params ) ) );

	if( isset( $_POST[$archive] ) ) {
		$files = array();

		$handlers = array(
			'tracker-attachments' => 'wikiplugin_archivebuilder_trackeratt',
			'page-as-pdf' => 'wikiplugin_archivebuilder_pagetopdf',
		);

		$archive = new ZipArchive;
		$archive->open( $file = tempnam( 'temp/', 'archive' ) . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE );

		foreach( explode( "\n", $data ) as $line ) {
			$parts = explode( ":", trim( $line ) );
			$handler = array_shift( $parts );

			if( isset( $handlers[$handler] ) ) {
				$result = call_user_func_array( $handlers[$handler], $parts );
				foreach( $result as $name => $content ) {
					$archive->addFromString( $name, $content );
					$files[] = $name;
				}
			}
		}

		$archive->addFromString( 'manifest.txt', implode( "\n", $files ) );
		$archive->close();

		// Compression of the stream may corrupt files on windows
		ob_end_clean();
		ini_set('zlib.output_compression','Off');

		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header( 'Content-Length: ' . filesize( $file ) );
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="' . $params['name'] . '";');
		header('Connection: close');
		header('Content-Transfer-Encoding: binary'); 
		readfile( $file );
		unlink( $file );
		die;
	} else {
		$label = tra('Download archive');
		return <<<FORM
<form method="post" action="">
	<input type="submit" name="$archive" value="$label" />
</form>
FORM;
	}
}

function wikiplugin_archivebuilder_trackeratt( $basepath, $trackerItem ) {
	global $trklib; require_once 'lib/trackers/trackerlib.php';
	$basepath = rtrim( $basepath, '/' ) . '/';

	$attachments = array();

	$files = $trklib->list_item_attachments( $trackerItem, 0, -1, 'attId_asc' );
	foreach( $files['data'] as $file ) {
		$name = $basepath . $file['filename'];
		$complete = $trklib->get_item_attachment( $file['attId'] );

		$attachments[$name] = $complete['data'];
	}
	
	return $attachments;
}

function wikiplugin_archivebuilder_pagetopdf( $file, $pageName ) {
	require_once 'lib/pdflib.php';
	$generator = new PdfGenerator;

	return array(
		$file => $generator->getPdf( 'tiki-print.php', array(
			'page' => $pageName,
		) ),
	);
}
