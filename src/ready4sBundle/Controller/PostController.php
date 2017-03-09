<?php

namespace ready4sBundle\Controller;

use ready4sBundle\Entity\Comment;
use ready4sBundle\Entity\Post;
use ready4sBundle\Service\JsonSerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Post controller.
 *
 * @Route("post")
 */
class PostController extends Controller
{
    /**
     * Lists all post entities.
     *
     * @Route("/", name="post_index")
     * @Method({"GET", "POST"})
     * @Template ("ready4sBundle:post:index.html.twig")
     */
    public function indexAction()
    {

        if ($this->getUser() != null) {
            $em = $this->getDoctrine()->getManager();

            $posts = $em->getRepository('ready4sBundle:Post')->findAll();

            $post = new Post();
            $form = $this->createForm('ready4sBundle\Form\PostType', $post);

            return array(
                'posts' => $posts,
                'form' => $form->createView(),
            );
        }

        return new Response('log in ritard');
    }

    /**
     * Creates a new post entity.
     *
     * @Route("/new", name="post_new")
     * @Method("POST")
     */
    public function newAjaxAction(Request $request)
    {

        $post = new Post();
        $form = $this->createForm('ready4sBundle\Form\PostType', $post);
        $form->handleRequest($request);
        $post->setDate(time());
        $post->setUser($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush($post);

            $repository = $this->getDoctrine()->getRepository('ready4sBundle:Post');
            $newPost = $repository->find($post->getId());

            $date = $newPost->getDate();
            $date = date('d-m-Y H:i:s', $date);
            $newPost->setDate($date);
            $newPost->setUser($this->getUser());

            $jsonContent = JsonSerializerService::serializeObjectToJson($newPost);
            return new Response($jsonContent);
        }

        return new JsonResponse('error');
    }


    /**
     * Finds and displays a post entity.
     *
     * @Route("/{id}", name="post_show")
     * @Method({"GET", "POST"})
     * @Template ("ready4sBundle:post:show.html.twig")
     *
     *
     */
    public function showAction(Post $post)
    {
        $deleteForm = $this->createDeleteForm($post);
        $comment = new Comment();
        $form = $this->createForm('ready4sBundle\Form\CommentType', $comment);

        return array(
            'post' => $post,
            'delete_form' => $deleteForm->createView(),
            'form' => $form->createView(),
        );
    }

    /**
     * mozna dodac pole edycji posta, link w show,
     *
     * @Route("/{id}/edit", name="post_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Post $post)
    {
        $deleteForm = $this->createDeleteForm($post);
        $editForm = $this->createForm('ready4sBundle\Form\PostType', $post);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_edit', array('id' => $post->getId()));
        }

        return $this->render('post/edit.html.twig', array(
            'post' => $post,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a post entity.
     *
     * @Route("/{id}", name="post_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Post $post)
    {
        $form = $this->createDeleteForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('post_index');

    }

    /**
     * Creates a form to delete a post entity.
     *
     * @param Post $post The post entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
