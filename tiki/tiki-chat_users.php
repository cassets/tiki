<?php

// $Header: /cvsroot/tikiwiki/tiki/tiki-chat_users.php,v 1.9 2004-03-28 07:32:23 mose Exp $

// Copyright (c) 2002-2004, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

# $Header: /cvsroot/tikiwiki/tiki/tiki-chat_users.php,v 1.9 2004-03-28 07:32:23 mose Exp $
require_once ("tiki-setup.php");

include_once ('lib/chat/chatlib.php');

?>

<html>
	<head>
		<?php

		print ('<link rel="StyleSheet" href="styles/' . $style . '" type="text/css" />');

		?>

	</head>

	<body style = "margin:0px;" onload = "window.setInterval('location.reload()','10000');">
		<table width = "100%" height = "100%">
			<?php

			if (isset($_REQUEST["channelId"])) {
				$chatusers = $chatlib->get_chat_users($_REQUEST["channelId"]);

				foreach ($chatusers as $achatuser) {

			?>

					<tr>
						<td valign = 'top' class = 'chatchannels'>
							<?php

							echo $achatuser["nickname"]

							?>

						</td>

						</tr>

			<?php

						}
			}

			?>

		</table>
	</body>
</html>
