<?php


namespace App\Presenters;

Use Nette;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Image;

final class GaleriePresenter extends Nette\Application\UI\Presenter {

    private $database;
    public function __construct(Nette\Database\Context $database) {
        $this->setLayout("AdministrationLayout");
        $this->database = $database;
    }
    protected function createComponentAddGalerie(): Form {
        $form = new Form();
        $form->addText("nazev")
                ->setRequired("Zadejte prosím název fotogalerie!")
                ->setHtmlAttribute("placeholder", "Název fotogalerie");
        $form->addUpload("obrazky")
                ->setRequired("Vybrete prosím jeden nebo více obrázků");
        $form->addSubmit("btnAdd", "Vytvořit fotogalerii");
        $form->onSuccess[] = [$this, 'addFormSucceeded'];
        return $form;
    }
    public function addFormSucceeded(Form $form, \stdClass $values) : void {
        $slozka = $values->nazev;
        mkdir("Galerie/$slozka", 0777);
        $this->database->table("fotogalerie")->insert(
            [
                "Nazev" => $values->nazev,
                "Pridano" => date("Y-m-d"),
                "Uzivatele_ID" => $this->user->getId(),
            ]
        );
        $lastID = $this->database->getInsertId();
        foreach ($values->obrazky as $obrazek) {
            if($obrazek->isImage() && $obrazek->isOk()) {
                $obrazek->move("./Galerie/$slozka/" . $obrazek);
                $this->database->table("fotografie")->insert(
                  [
                      "Foto" => $obrazek,
                      "Fotogalerie_Fotogalerie_ID" => $lastID,
                  ]
                );
            }
        }
        $this->redirect("Administration:galerie");
    }

}