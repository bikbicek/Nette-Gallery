<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Image;
use Nette\Database\Table\Selection;


class GalleryPresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function renderDefault()
    {
        $this->template->images = $this->database->table('images')->where('public', True);
    }
    
    public function createComponentUploadForm()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        else{
            $form = new Form;
            $form->addUpload('image')
            ->setRequired(TRUE)
            ->addRule(Form::IMAGE, 'Avatar musí být JPEG, PNG nebo GIF.');
            
            $form->addText('title');
            $form->addText('info');
            $form->addCheckbox('public');
            
            $form->addSubmit('upload', 'Nahrát fotografii');
            $form->onSuccess[] = [$this, 'uploadFormSucceeded'];
            return $form;
        }
    }
    
    public function renderMyGallery()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro prohlížení osobních fotografií se potřebujete přihlásit.');
        }
        else{
            $this->template->images = $this->database->table('images')->where('user_id', $this->getUser()->id);
        }
    }
    
    public function renderShowImage($id)
    {
        $image = $this->database->table("images")->get($id);
        if(!$image){$this->error('Tato stránka neexistuje');}
        else{
            if($image->public == True || ( $this->getUser()->isLoggedIn() && $image->user_id == $this->getUser()->id)){
                $this->template->image = $image;
            }
            else{
                $this->error('Nemáte přístup k této stránce');
            }
        }
    }
    
    public function actionDelete($id)
    {
        $image = $this->database->table("images")->get($id);
        if($image->user_id == $this->getUser()->id){
            $path = __DIR__.'/../images/'.$image->path.'.jpg';
            unlink($path);
            $image->delete();
            $this->redirect("my-gallery");
        }
        else{
            $this->error("Nejste oprávněny toto udělat");
        }
    }
    
    public function uploadFormSucceeded(Form $form, $values)
    {
        $id_name = uniqid();
        $image = $values->image;
        unset($values['image']);
        $values->user_id = $this->getUser()->id;
        $values->path = $id_name;
        $this->database->table('images')->insert($values);
        
        $image = Image::fromFile($image);
        $image->save(__DIR__.'/../images/'.$id_name.'.jpg');
        $this->redirect('Gallery:myGallery');
    }
    
    public function renderImage($id){
        $image = $this->database->table("images")->get($id);
        if($image){
        
            if($image->public == True || ( $this->getUser()->isLoggedIn() && $image->user_id == $this->getUser()->id)){
                $image = Image::fromFile(__DIR__.'/../images/'.$image->path.'.jpg');
                $image->send();
                return;
            }
            
        }
        else{$this->error('Tato stránka neexistuje');}
    }
}
