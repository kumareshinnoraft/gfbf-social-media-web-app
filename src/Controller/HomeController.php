<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PerformedOperations;
use PHPUnit\TextUI\Exception;
use App\Service\Cryptography;
use App\Service\Cookie;
use App\Entity\Comment;
use App\Entity\Likes;
use App\Entity\Post;
use App\Entity\User;
use DateTime;

/**
 * This home controller manages the all operations that can be performed on the
 * home page. From loading the current live active users list to all current
 * posts, this controller will help to do all these things. This controller has
 * total seventeen controllers and most of them are called by ajax requests and
 * those are returning array and few controllers are directly returning twig 
 * pages for showing desired output.
 *
 * @package Doctrine
 * @subpackage ORM
 * 
 * @author Kumaresh Baksi <kumaresh.baksi@innoraft.com>
 * @version 1.0
 * @license INNORAFT
 */
class HomeController extends AbstractController
{
  public const PROFILE_IMAGE_PATH = "/profile/";
  public const HTTP               = "http://";
  /**
   * Entity Manager class object that manages the persistence and 
   * retrieval of entity objects from the database.
   * 
   * @var object
   */
  private $em;
  /**
   * This object provides different functions for user operations.
   * 
   * @var object
   */
  private $performOperation;

  /**
   * Cryptography object encode and decode values before
   * sending in link or storing password.
   *
   * @var object
   */
  private $cryptography;
  /**
   * This user object is used to point user entity class.
   * 
   * @var object
   */
  private $user;
  /**
   * This post object is used to point post entity class.
   * 
   * @var object
   */
  private $post;
  /**
   * This like object is used to point likes entity class.
   * 
   * @var object
   */
  private $like;
  /**
   * This comment table object is used to point comment database tables.
   * 
   * @var object
   */
  private $comment;
  /**
   * This user table object is used to point user database tables.
   * 
   * @var object
   */
  private $userTable;
  /**
   * This post table object is used to point user post database tables.
   *
   * @var object
   */
  private $postTable;
  /**
   * This like table object is used to point user like database tables.
   *
   * @var object
   */
  private $likeTable;
  /**
   * This comment table object is used to point user comment database tables.
   *
   * @var object
   */
  private $commentTable;
  /**
   * This object is used to store and retrieve cookie. 
   *
   * @var object
   */
  private $cookie;
  /**
   * This constructor is initializing the objects and also provides access of
   * entity manager interface.
   *
   * @param object $em
   *   EntityManagerInterface is used to manage entity with database
   *   it helps to alter database easily.
   *    
   * @return void
   *   Contractor does not return anything instead it is used to initialize
   *   the object.
   */
  public function __construct(EntityManagerInterface $em)
  {
    $this->commentTable     = $em->getRepository(Comment::class);
    $this->likeTable        = $em->getRepository(Likes::class);
    $this->userTable        = $em->getRepository(User::class);
    $this->postTable        = $em->getRepository(Post::class);
    $this->performOperation = new PerformedOperations();
    $this->cryptography     = new Cryptography();
    $this->comment          = new Comment();
    $this->cookie           = new Cookie();
    $this->like             = new Likes();
    $this->post             = new Post();
    $this->user             = new User();
    $this->em               = $em;
  }
  /**
   * Home controller is the main feed that will be shown to the user, at one
   * side of the screen online user's will be present and on the other side 
   * posts will be present. 
   *   
   * @Route("/home", name="home")
   *   This route is for sending user to the home screen.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function home(Request $request): Response
  {
    if (!$this->cookie->isActive($request)) {
      return $this->redirectToRoute('loginUser');
    }
    return $this->render('home/index.html.twig');
  }
  /**
   * This root redirects user to home page, home pages if the user is already
   * logged in or not if not then user will be redirected to login page.
   *   
   * @Route("/", name="root")
   *   This route is for sending user to the home screen.
   * 
   * @return Response
   *   This response will be to the home screen.
   */
  public function rootPage(): Response
  {
    return $this->redirectToRoute('home');
  }
  /**
   * This route returns with image name of the user.
   *   
   * @Route("/active-users", name="active-users")
   *   This route is for sending user to the home screen.
   * 
   * @return Response
   *   This response will be to the home screen.
   */
  public function sendEmail(Request $request): Response
  {
    $email   = $this->cookie->getCookie('email', $request);
    $userRow = $this->userTable->findOneBy(['email' => $email]);

    return new JsonResponse(
      [
        'email' => $email,
        'userImage' => $userRow->getImageName()
      ]
    );
  }
  /**
   * This routes will be used to store user post in the database and sent a
   * acknowledge message to the ajax request.
   *   
   * @Route("/post", name="post")
   *   This route is for posting user created post.
   * 
   * @return Response
   *   This response will be to the ajax request.
   */
  public function post(Request $request): Response
  {
    // Checking if the user inserted data is not NULL.
    if ($request->request->all()) {

      // Get user email from user's cookies.
      $email       = $this->cookie->getCookie('email', $request);
      $postContent = $request->request->get('postContent');

      // Get the current user with email.
      $user = $this->userTable->findOneBy(['email' => $email]);

      // By default image name will be NULL and if image found in the request
      // it will store the image with a proper name with timestamp.
      $imageName = NULL;
      if ($request->files->has('image')) {
        $imageName = $this->performOperation->storeImg($user->getUserName() . date("mdYHis"), AuthController::POSTS_IMAGE_PATH, $request->files->get('image'));
      }

      // Setting all posts data at once.
      $this->post->setPostsData($this->performOperation->sanitizeData($postContent), $imageName, new DateTime(), new DateTime(), $user);
      try {
        $this->em->persist($this->post);
        $this->em->flush();
      } 
      catch (Exception $e) {
        return new JsonResponse(["post" => $e->getMessage()]);
      }
      return new JsonResponse(["post" => TRUE]);
    }
    return $this->redirectToRoute('home');
  }
  /**
   * This route fetches all the posts from the database. This request occurs
   * when page loads for the first time.
   *   
   * @Route("/created-posts", name="created-posts")
   *   This route is for returning posts in ajax.
   * 
   * @return Response
   *   This response will be to the home screen.
   */
  public function createdPosts(Request $request): Response
  { 
    // Getting all the posts and extracting each post in an array by calling
    // post list method.
    $posts = $this->postTable->findAll();
    return new JsonResponse(['posts' => $this->performOperation->postList($posts)]);
  }
  /**
   * This route updates likes in the database and return the total number of
   * likers.
   *   
   * @Route("/like", name="like")
   *   This route is for returning likes in ajax.
   * 
   * @return Response
   *   This JSON response will return the count of likers.
   */
  public function like(Request $request): Response
  {
    // Fetching necessary information about the user is clicking and about the 
    // post where user is clicking.
    $this->post = $this->postTable->findOneBy(["id"    => $request->request->get('postId')]);
    $this->user = $this->userTable->findOneBy(["email" => $this->cookie->getCookie('email', $request)]);

    // Set like information.
    $this->like->setData($this->post, $this->user, new DateTime, new DateTime);

    // Add this like to the post.
    $this->post->addLike($this->like);

    // Updating like in both post and like table.
    $this->em->persist($this->like);
    $this->em->persist($this->post);
    $this->em->flush();
    return new JsonResponse(['likesList' => $this->performOperation->likes($this->post)]);
  }
  /**
   * This route delete the likes from the table.
   *   
   * @Route("/dislike", name="dislike")
   *   This route is for returning current number of likes in ajax.
   * 
   * @return Response
   *   This json response will returns likes of the current post in an array.
   */
  public function dislike(Request $request): Response
  {
    // Fetching necessary information about the user is clicking and about the 
    // post where user is clicking.
    $this->post = $this->postTable->findOneBy(["id"    => $request->request->get('postId')]);
    $this->user = $this->userTable->findOneBy(["email" => $this->cookie->getCookie('email', $request)]);
    
    // Finding the like with the user and post.
    $this->like = $this->likeTable->findOneBy([
      "user" => $this->user,
      "post" => $this->post
    ]);

    // Remove the like from the post and as well as from the Like table.
    $this->post->removeLike($this->like);
    if ($this->like) {
      $this->em->remove($this->like);
      $this->em->flush();
    }
    return new JsonResponse(['likesList' => $this->performOperation->likes($this->post)]);
  }
  /**
   * This controller post the comments in the database.
   *   
   * @Route("/postComment", name="postComment")
   *   This route is for sending user to the home screen.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   The json response containing the comments of a particular post post.
   */
  public function postComment(Request $request): Response
  {
    // Getting the comment and post id from the query parameters.
    $commentContent = $request->request->get('comment');
    $postId         = $request->request->get('postId');

    // Finding the current user object and the post when comment is written.
    $this->user = $this->userTable->findOneBy(["email" => $this->cookie->getCookie('email', $request)]);
    $this->post = $this->postTable->findOneBy(["id"    => $postId]);

    // Backend validation if the comment is an empty string.
    if ($commentContent === '') {
      return new JsonResponse(['comment' => FALSE]);
    }
    $this->comment->setComment($this->performOperation->sanitizeData($commentContent), new DateTime(), new DateTime(), $this->user, $this->post);
    $this->post->addComment($this->comment);

    // Setting comment in both comment and post table field.
    $this->em->persist($this->comment);
    $this->em->persist($this->post);
    $this->em->flush();
    return new JsonResponse(['comment' => $this->performOperation->comments($this->post)]);
  }
  /**
   * Before user do a comment when user clicks the comment button this 
   * controller identify it and send all comments related to the post.
   *   
   * @Route("/fetchComments", name="fetchComments")
   *   This route is for sending user to the home screen.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function fetchComments(Request $request): Response
  {
    $this->post = $this->postTable->findOneBy(["id" => $request->request->get('postId')]);

    return new JsonResponse(['comment' => $this->performOperation->comments($this->post)]);
  }
  /**
   * This block of controller is used to identify whether the current user and 
   * the post menu which user has clicked are same or different.
   *   
   * @Route("/checkPostVsUser", name="checkPostVsUser")
   *   Checks current user and the post menu have same email id or different.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function checkPostVsUser(Request $request): Response
  {
    // Getting the post object with the post ID.
    $this->post = $this->postTable->findOneBy(["id" => $request->request->get('postId')]);

    // Matching if both contains the same email address.
    if ($this->cookie->getCookie('email', $request) === $this->post->getUser()->getEmail()) {
      return new JsonResponse(['USER' => TRUE]);
    }
    return new JsonResponse(['USER' => FALSE]);
  }
  /**
   * This controller deletes the post from the database and returns the flag
   * message.
   * 
   * @Route("/deletePost", name="deletePost")
   *   This route is for deleting the post.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function deletePost(Request $request): Response
  {
    // Finding the post ID.
    $post = $this->postTable->findOneBy(["id" => $request->request->get('postId')]);
    try {
      // Removing the post from the database.
      $this->em->remove($post);
      $this->em->flush();
    } 
    catch (Exception $ex) {
      return new JsonResponse(['DELETE' => $ex->getMessage()]);
    }
    return new JsonResponse(['DELETE' => TRUE]);
  }
  /**
   * This controller updates the database by deleting the comment.
   *   
   * @Route("/deleteComment", name="deleteComment")
   *   This route is for deleting the comments in the database.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function deleteComment(Request $request): Response
  {
    $comment = $this->commentTable->findOneBy(["id" => $request->request->get('commentId')]);
    try {
      $this->em->remove($comment);
      $this->em->flush();
    } 
    catch (Exception $ex) {
      return new JsonResponse(['DELETE' => $ex->getMessage()]);
    }
    return new JsonResponse(['DELETE' => TRUE]);
  }
  /**
   * This controller post the comments in the database.
   *   
   * @Route("/updatePost", name="updatePost")
   *   This route is for updating the post in the database.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function updatePost(Request $request): Response
  {
    // Getting the post object from the post id following the post id.
    $post = $this->postTable->findOneBy(["id" => $request->request->get('postId')]);

    // Getting the latest post content.
    $updatedPost = $request->request->get('updatedPost');

    // Sanitize the post content before updating.
    $post->setPostsData($this->performOperation->sanitizeData($updatedPost), $post->getImage(), $post->getCreatedAt(), new DateTime(), $post->getUser());

    try {
      $this->em->flush();
    } 
    catch (Exception $e) {
      return new JsonResponse(['UPDATE' => $e->getMessage()]);
    }
    return new JsonResponse(['UPDATE' => TRUE]);
  }
  /**
   * This controller update the comments in the database.
   *   
   * @Route("/updateComment", name="updateComment")
   *   This route is updating comment.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response is returning a flag of success and a string containing error
   *   message.
   */
  public function updateComment(Request $request): Response
  {
    // Identify the comment id and fetch the updated comment.
    $comment     = $this->commentTable->findOneBy(["id" => $request->request->get('commentId')]);
    $updatedPost = $request->request->get('updatedComment');

    // Setting the comment in the comment object.
    $comment->setComment($this->performOperation->sanitizeData($updatedPost), $comment->getCreatedAt(), new DateTime(), $comment->getUser(), $comment->getPost());
    try {
      $this->em->flush();
    } 
    catch (Exception $e) {
      return new JsonResponse(['UPDATE' => $e->getMessage()]);
    }
    return new JsonResponse(['UPDATE' => TRUE]);
  }
  /**
   * This controller is for menu buttons when user will click menu button on
   * the comment if the comment belongs to the current user it will return TRUE
   * as user can access the menu with delete and edit buttons.
   *   
   * @Route("/checkCommentVsUser", name="checkCommentVsUser")
   *   This route is for identification of the user and comment.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response contains the flag if the comment belongs to the current user it
   *   will return TRUE instead FALSE otherwise.
   */
  public function checkCommentVsUser(Request $request): Response
  {
    $this->comment = $this->commentTable->findOneBy(["id" => $request->request->get('commentId')]);

    // Checking if both email in the comment and user is same or not.
    if ($this->cookie->getCookie('email', $request) === $this->comment->getUser()->getEmail()) {
      return new JsonResponse(['USER' => TRUE]);
    }
    return new JsonResponse(['USER' => FALSE]);
  }
  /**
   * This controller post the comments in the database.
   *   
   * @Route("/post/{postId}", name="viewPost")
   *   This route is for sending user to the home screen.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function viewPost(Request $request, string $postId): Response
  {
    // Get the post by decoding the post id.
    $post = $this->postTable->find($this->cryptography->decode($postId));

    return $this->render('home/post.html.twig', $this->performOperation->singlePostInformation($post));
  }
  /**
   * This controller shows the profile of the user.
   *   
   * @Route("/profile/{profileId}", name="viewProfile")
   *   This route is for sending user to the home screen.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function viewProfile(Request $request, string $profileId): Response
  {
    // Decoding the id from the URL and getting the user object.
    $user = $this->userTable->findOneBy(["email" => $this->cryptography->decode($profileId)]);

    // Directly sending user information to the twig for showing user profile information.
    return $this->render('home/profile.html.twig', $this->performOperation->singleUserInformation($user, $request->getHost(), $profileId));
  }
}