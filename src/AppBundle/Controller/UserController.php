<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController.
 *
 * @codeCoverageIgnore
 */
class UserController extends AbstractController
{
    /**
     * @Route("/users", name="user_list")
     *
     * @return Response
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('GET', $this->getUser());

        $response = $this->render('user/list.html.twig', ['users' => $this->getDoctrine()->getRepository('AppBundle:User')->findAll()]);

        $response->setSharedMaxAge(200);

        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     *
     * @Route("/users/create", name="user_create")
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     *
     * @Route("/users/{id}/edit", name="user_edit")
     *
     * @return RedirectResponse|Response
     */
    public function editAction(User $user, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $this->denyAccessUnlessGranted('EDIT', $this->getUser());

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
