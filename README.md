PetrSladek/SkautIS
======

Servisní třída do Nette Frameworku pro práci a přihlašování ze SkautISem.

Pro přihlášení přes SkautIS nabízí stejné rozhraní jako známé knihovny
- [Kdyby/Google](https://github.com/Kdyby/Google)
- [Kdyby/Facebook](https://github.com/Kdyby/Facebook/)
- [Kdyby/GitHub](https://github.com/Kdyby/GitHub/)

Dále obsahuje metody pro jednoduší vytahování dat o přihlášeném uživateli.

Závislosti
------------
- [Nette Framework](https://github.com/nette/nette)
- [skaut/SkautisNette](https://github.com/skaut/SkautisNette)


Instalace
------------

Nejlépe pomocí [Composer](http://getcomposer.org/):

```sh
$ composer require petrsladek/skautis:~0.1
```


Návod
------------

Ukázka minimální konfigurace:
```
extensions:
    skautis: Skautis\Nette\SkautisExtension # pro original skautis extension
    skautislogin: PetrSladek\SkautIS\DI\SkautISExtension # pro toto skautis extension

skautis:
    applicationId : abcd-...-abcd # AppId přidělené administrátorem skautISu
    testMode: false # or true
```

Pouziti v presenteru
```php
class LoginPresenter extends BasePresenter
{

    /**
     * @var \PetrSladek\SkautIS\SkautIS @inject
     */
    public $skautis;

	/** @var UsersModel @inject*/
	public $usersModel;


	/**
	 * Vytvoří komponentu pro otevření login dialogu SkautISu
	 * @return \Kdyby\Google\Dialog\LoginDialog
	 */
	protected function createComponentSkautisLogin()
	{
		$dialog = new \PetrSladek\SkautIS\Dialog\LoginDialog($this->skautis);
		$dialog->onResponse[] = function(\PetrSladek\SkautIS\Dialog\LoginDialog $dialog) {
			$skautis = $dialog->getSkautIS();

			/** @var $api \SkautIS\SkautIS */
			$api = $dialog->getSkautIS()->getClient();

			if (!$skautis->isLoggedIn()) {
                $this->flashMessage("Přihlášení se nezdařilo.");
                return;
            }

			/**
			 * Pokud jsme se tady, bude fungovat normálně přístupné SkautIS API
			 */

			try {
			    // $skautisUserId = $skautis->getUserId(); // vrati ID skautis uctu kterym jste se prihlasil
				$skautisPersonId = $skautis->getPersonId(); // vrati ID sparovan osoby se skautis uctem kterym jste se prihlasil

                // $me = $skautis->getUserData(); // vrati data o prihlasenem skautis uzivateli
				$me = $skautis->getPersonData(); // vrati data o osobe ktera je sparovana s prihlasenym skautis uzivatelem

				if (!$existing = $this->usersModel->findBySkautisPersonId($skautisPersonId)) {
					/**
					 * Pokud uzivatel neni u nas v DB, tak ho zaregistrujeme
					 */
					$existing = $this->usersModel->registerFromSkautis($me);
				}

				/**
				 * Prihlasime uzivatele pomoci objektu Identity
				 */
				$this->user->login(new \Nette\Security\Identity($existing->id, $existing->roles, $existing));

				/**
				 * Nyni jste prihlaseni pres skautis
				 */

			} catch (\Exception $e) {
				\Tracy\Debugger::log($e, 'skautis');
				$this->flashMessage("Prihlaseni se nazdarilo.");
			}

			$this->redirect('this'); // jsme v obsluze handleru, takze presmerujeme na sebe abychom nemeli v adrese ?do=xxx
		};

		return $dialog;
	}

}
```

Do šablony pak stačí přigat odkaz na handler open! této komponenty.

```smarty
{* By the way, this is how you do a link to signal of subcomponent. *}
<a n:href="googleLogin-open!">Login using google</a>
```


V případě že chceme na Skautis přihlášení přesměrovat z prezentru, použijeme  následující zápis (v presenteru který má přístup k výše definované komponentě)

```php
if(!$this->getUser()->isLoggedIn()) { // nebo if(!$skautis->isLoggedIn()) pro skautis uzivatele
    $this['skautisLogin']->open();
}
```

