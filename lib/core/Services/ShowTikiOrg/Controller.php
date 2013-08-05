<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_ShowTikiOrg_Controller
{
	function setUp()
	{
		global $prefs;

		Services_Exception_Disabled::check('trackerfield_showtikiorg');
	}

	function action_process($input)
	{
		$id = $input->id->int();
		$userid = $input->userid->int();
		$username = $input->username->text();
		$fieldId = $input->fieldId->int();
		$command = $input->command->word();
		$svntag = $input->svntag->text();

		$field = TikiLib::lib('trk')->get_tracker_field($fieldId);
		$options = json_decode($field['options']);
		$domain = $options->domain;

		$conn = ssh2_connect($domain, 22);
                $conntry = ssh2_auth_pubkey_file(
                        $conn,
                        $options->remoteShellUser,
                        $options->publicKey,
                        $options->privateKey
                );

                if (!$conntry) {
			$ret['status'] = 'DISCONNECTED';
                        return $ret;
                }

                $infostring = "info -i $id -U $userid";
                $infostream = ssh2_exec($conn, $infostring);

                stream_set_blocking( $infostream, TRUE );
                $infooutput = stream_get_contents( $infostream );
                $ret['debugoutput'] = $infooutput;

                $statuspos = strpos($infooutput, 'STATUS: ');
                $status = substr($infooutput, $statuspos + 8, 5);
		$status = trim($status);
                if (!$status || $status == 'FAIL') {
                        $ret['status'] = 'FAIL';
                } else {
                        $ret['status'] = $status;
                }

		if (!empty($command)) {
			if ($command == 'destroy' && !TikiLib::lib('user')->user_has_permission($user, 'tiki_p_admin') && $user != $creator) {
				throw new Services_Exception_Denied;
			}

			if (empty($svntag)) {
				$fullstring = "$command -u $creator -i $id -U $userid";
			} else {
				$fullstring = "$command -t $svntag -u $username -i $id -U $userid";
			}

			$stream = ssh2_exec($conn, $fullstring);
			stream_set_blocking( $stream, TRUE );
			$output = stream_get_contents( $stream );
			fclose( $stream );
			$ret['debugoutput'] = $fullstring . "\n" . $output;

			if ($command == 'snapshot') {
				$ret['status'] = 'SNAPS';
			} else if ($command == 'destroy') {
				$ret['status'] = 'DESTR';
			} else if ($command == 'create') {
				$ret['status'] = 'BUILD';
			}
		}

		$ret['debugoutput'] = '-' . $status . '- ' . $ret['debugoutput'];

		$cachelib = TikiLib::lib('cache');
		$cacheKey = 'STO-' . $options->domain . '-' . $fieldId . "-" . $id;
		$cachelib->invalidate($cacheKey);
	
		return $ret;
	}
}

