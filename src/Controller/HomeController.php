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
use App\Service\SendEmail;
use App\Service\Cookie;
use App\Entity\Comment;
use App\Entity\Likes;
use App\Entity\Post;
use App\Entity\User;
use DateTime;

class HomeController extends AbstractController
{
  /**
   * Entity Manager class object that manages the persistence and 
   * retrieval of entity objects from the database.
   * 
   * @var object
   */
  public $em;
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
   * This user table object is used to point users database tables.
   * 
   * @var object
   */
  private $userTable;
  /**
   * This post table object is used to point user posts database tables.
   *
   * @var object
   */
  private $postTable;
  /**
   * This like table object is used to point user likes database tables.
   *
   * @var object
   */
  private $likeTable;
  /**
   * This comment table object is used to point user comments database tables.
   *
   * @var object
   */
  private $commentTable;
  /**
   * This object variable is used to call sendMail function.
   *
   * @var object
   */
  private $sendMail;
  /**
   * OTP is the Entity which stores username, otp and otp created at time.
   *
   * @var object
   */
  private $otp;
  /**
   * This image by default contains the profile avatar.
   *
   * @var object
   */
  private $imageName = '';
  /**
   * This object is used to store and retrieve cookie. 
   *
   * @var object
   */
  private $cookie;
  /**
   * Constructor is initializing the objects.
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
    $this->em = $em;

    $this->cryptography = new Cryptography();
    $this->performOperation = new PerformedOperations();
    $this->cookie = new Cookie();
    $this->sendMail = new SendEmail();

    $this->user = new User();
    $this->post = new Post();
    $this->like = new Likes();
    $this->comment = new Comment();

    $this->userTable = $em->getRepository(User::class);
    $this->postTable = $em->getRepository(Post::class);
    $this->likeTable = $em->getRepository(Likes::class);
    $this->commentTable = $em->getRepository(Comment::class);
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
    $email = $this->cookie->getCookie('email', $request);

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
    // Checking if the user inserted data is not NULL
    if ($request->request->all()) {

      // Get user email from user's cookies.
      $email = $this->cookie->getCookie('email', $request);
      $postContent = $request->request->get('postContent');

      // Get the current user with email.
      $user = $this->userTable->findOneBy(['email' => $email]);

      // Setting all posts data at once.
      $this->post->setPostsData($this->performOperation->sanitizeData($postContent), NULL, new DateTime(), new DateTime(), $user);

      try {
        $this->em->persist($this->post);
        $this->em->flush();
      } catch (Exception $e) {
        return new JsonResponse(["post" => $e->getMessage()]);
      }
      return new JsonResponse(["post" => TRUE]);
    }
    return $this->redirectToRoute('home');
  }

  /**
   * This route fetches all the posts from the database.
   *   
   * @Route("/created-posts", name="created-posts")
   *   This route is for returning posts in ajax.
   * 
   * @return Response
   *   This response will be to the home screen.
   */
  public function createdPosts(Request $request): Response
  {
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
    $this->post = $this->postTable->findOneBy(["id" => $request->request->get('postId')]);
    $this->user = $this->userTable->findOneBy(["email" => $this->cookie->getCookie('email', $request)]);

    // Set like information.
    $this->like->setData($this->post, $this->user, new DateTime, new DateTime);

    // Add this like to the post.
    $this->post->addLike($this->like);

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
   *   This JSON response will return the count of likers.
   */
  public function dislike(Request $request): Response
  {
    // Fetching necessary information about the user is clicking and about the 
    // post where user is clicking.
    $this->post = $this->postTable->findOneBy(["id" => $request->request->get('postId')]);
    $this->user = $this->userTable->findOneBy(["email" => $this->cookie->getCookie('email', $request)]);

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
}