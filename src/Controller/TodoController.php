<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Form\TodoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TodoController extends AbstractController
{
    /**
     * @Route("/todo",name="todo_list")
     */
    public function listAction() {
        $todos = $this->getDoctrine()
            ->getRepository(Todo::class)
            ->findAll();
        return $this->render('todo/index.html.twig',['todos'=>$todos]);
    }

    /**
     * @Route("/todo/details/{id}",name="todo_details")
     */
    public function detailsAction($id) {
        $todos = $this->getDoctrine()
            ->getRepository(Todo::class)
            ->find($id);
        return $this->render('todo/details.html.twig',['todos'=>$todos]);
    }

    /**
     * @Route("/todo/delete/{id}",name="todo_delete")
     */
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $todo = $em->getRepository(Todo::class)->find($id);
        $em->remove($todo);
        $em->flush();

        $this->addFlash('error','Todo deleted');
        return $this->redirectToRoute('todo_list');
    }

    /**
     * @Route("/todo/create", name="todo_create", methods={"GET","POST"})
     */
    public function createAction(Request $request)
    {
        $todo = new Todo();
        $form = $this->createForm(TodoType::class, $todo);

        if ($this->saveChanges($form, $request, $todo)) {
            $this->addFlash(
                'notice',
                'Todo Added'
            );

            return $this->redirectToRoute('todo_list');
        }

        return $this->render('todo/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function saveChanges($form, $request, $todo)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $todo->setName($request->request->get('todo')['name']);
            $todo->setCategory($request->request->get('todo')['category']);
            $todo->setDescription($request->request->get('todo')['description']);
            $todo->setPriority($request->request->get('todo')['priority']);
            $todo->setDueDate(\DateTime::createFromFormat('Y-m-d', $request->request->get('todo')['due_date']));
            $em = $this->getDoctrine()->getManager();
            $em->persist($todo);
            $em->flush();

            return true;
        }
        return false;
    }

    /**
     * @Route("/todo/edit/{id}", name="todo_edit")
     */
    public function editAction($id, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $todo = $em->getRepository(Todo::class)->find($id);
        $form = $this->createForm(TodoType::class,$todo);

        if ($this->saveChanges($form, $request, $todo)) {
            $this->addFlash('notice', "Todo Edited");
            return $this->redirectToRoute('todo_list');
        }

        return $this->render('todo/edit.html.twig', ['form'=>$form->createView()]);
    }
}
