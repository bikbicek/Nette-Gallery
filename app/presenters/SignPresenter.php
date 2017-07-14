<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class SignPresenter extends Nette\Application\UI\Presenter
{

    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->addText('username')
            ->setRequired('Prosím vyplňte své uživatelské jméno.')
            ->setHtmlAttribute('autocomplete','off');

        $form->addPassword('password')
            ->setRequired('Prosím vyplňte své heslo.');

        $form->addSubmit('send','Přihlásit');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }
    
    public function signInFormSucceeded($form, $values)
    {
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:default');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }
    
    public function actionOut()
    {
        $this->getUser()->logout();
        $this->redirect('Homepage:default');
    }

}