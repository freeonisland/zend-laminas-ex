<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace BlogTuto\Controller;

use BlogTuto\{
    Table\AlbumTable,
    Form\AlbumForm,
    Model\Album
};

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class AlbumController extends AbstractActionController
{
    private $table;

    public function __construct(AlbumTable $table)
    {
        $this->table = $table;
    }

    public function fixtureAction()
    {
        \BlogTuto\Fixture\TableSchema::createTable();
        \BlogTuto\Fixture\AlbumFixture::load();
        \BlogTuto\Fixture\BlogFixture::load();
    }

    public function indexAction()
    {
        return new ViewModel([
            'albums' => $this->table->fetchAll()
        ]);
    }

    public function createAction()
    {
        $form = new AlbumForm;
        $form->get('submit')->setValue($form->get('submit')->getValue() . ' create');

        if (!$this->getRequest()->isPost()) {
            return ['form' => $form];
        } 

        $album = new Album;
        $form->setInputFilter($album->getInputFilter());

        $form->setData($this->getRequest()->getPost());

        if (!$form->isValid()) {
            return ['form'=>$form];
        }

        $album->exchangeArray($form->getData());
        
        $this->table->saveAlbum($album);
        
        //return new ViewModel();
        return $this->redirect()->toRoute('album-tuto');
    }


    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('album-tuto', ['action' => 'add']);
        }

        // Retrieve the album with the specified id. Doing so raises
        // an exception if the album is not found, which should result
        // in redirecting to the landing page.
        try {
            $album = $this->table->getAlbum($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('album-tuto', ['action' => 'index']);
        }

        $form = new AlbumForm;
        $form->bind($album);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($album->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return $viewData;
        }

        $this->table->saveAlbum($album);

        // Redirect to album list
        return $this->redirect()->toRoute('album-tuto', ['action' => 'index']);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album-tuto');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $delete = $request->getPost('validate_delete');

            if ($delete) {
                $id = (int) $request->getPost('id');
                $this->table->deleteAlbum($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('album-tuto');
        }

        return [
            'id'    => $id,
            'album' => $this->table->getAlbum($id),
        ];
    }
}
