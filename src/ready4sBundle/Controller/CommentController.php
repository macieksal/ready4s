<?php
namespace ready4sBundle\Controller;

use ready4sBundle\Entity\Comment;
use ready4sBundle\Service\JsonSerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;


/**
 * Comment controller.
 *
 * @Route("comment")
 */
class CommentController extends Controller
{

    /**
     * Creates a new comment entity.
     *
     * @Route("/newCommentAjax", name="comment_new")
     * @Method("POST")
     */
    public function newCommentAjaxAction(Request $request)
    {
        if ($this->getUser() != null) {

            try {
                $comment = new Comment();

                $repository = $this->getDoctrine()->getRepository('ready4sBundle:Post');
                $post = $repository->find($request->request->get('id'));

                $comment->setDescription($request->request->get('description'));
                $comment->setDate(time());
                $comment->setUser($this->getUser());
                $comment->setPost($post);

                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();

                $date = $comment->getDate();
                $date = date('d-m-Y H:i:s', $date);

                $array = array(
                    'id' => $comment->getId(),
                    'description' => $comment->getDescription(),
                    'date' => $date,
                    'name' => $comment->getUser()->getName(),
                    'surname'   => $comment->getUser()->getSurname(),

                );
                return new JsonResponse($array);

            } catch (\Exception $e){
                return new JsonResponse($e->getMessage());
            }

        }
        return new JsonResponse(array('msg' => 'You must be logged in order to comment'));
    }
}
