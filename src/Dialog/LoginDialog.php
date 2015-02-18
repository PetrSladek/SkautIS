<?php

namespace PetrSladek\SkautIS\Dialog;

use Nette\Http\UrlScript;



/**
 * @author Petr Sladek <petr.sladek@skaut.cz>
 *
 */
class LoginDialog extends AbstractDialog
{

	/**
	 * Checks, if there is a user in storage and if not, it redirects to login dialog.
	 * If the user is already in session storage, it will behave, as if were redirected from Google right now,
	 * this means, it will directly call onResponse event.
	 */
	public function handleOpen()
	{
		$this->open();
	}


	/**
	 * @return UrlScript
	 */
	public function getUrl()
	{
		return new UrlScript($this->skautis->client->getLoginUrl());
	}

}
