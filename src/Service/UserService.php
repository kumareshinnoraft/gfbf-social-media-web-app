<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use DateTime;

/**
 * This class is used to separate the database operations from websocket 
 * operations. When websocket gets the desired status from the client-side
 * this class will be called and perform all necessary database operations
 * and return the result as an array data.
 * 
 * @package ORM
 * @subpackage EntityManagerInterface
 */
class UserService
{
  /**
   * This object provides different functions for user operations.
   * 
   * @var object
   */
  private $performOperation;
  /**
   * This constructor is used to initialize the object. Performed operations
   * class contains necessary methods to generate different types of array by
   * extracting post, user or comment.
   */
  public function __construct()
  {
    $this->performOperation = new PerformedOperations();
  }
  /**
   * Get user email is called to fetch user information if the user is active
   * by the email of the user.
   *
   * @param string $email
   *   Email is used as a unique identifier for the database.
   * @param object $userTable
   *   User table is the table of the user which will be used for getting 
   *   values from the table.
   * @param object $em
   *   Entity manager interface is used to get the different database table.
   * 
   * @return mixed
   *   This function returns the array of user information and if the user is
   *   not found it returns FALSE.
   */
  public function getUserByEmail(string $email, object $userTable, EntityManagerInterface $em)
  {
    $userRow = $userTable->findOneBy(['email' => $email]);

    // Setting the user is activated and CURRENT DATETIME.
    $userRow->setLastActiveTime(new DateTime);
    $userRow->setIsActive(TRUE);
    $em->persist($userRow);
    $em->flush();

    // Getting the list of users from the database or session
    // For example, using Doctrine ORM:
    $users = $userTable->findBy(['isActive' => TRUE]);

    // Construct a message containing the updated list of active users.
    $activeUsersMessage = [
      'type' => 'active-users',
      'data' => [
        'users' => $this->performOperation->getUserData($users)
      ]
    ];
    return $activeUsersMessage;
  }
  /**
   * THis function return the posts that need to load on the home page.
   *
   * @param object $postTable
   *   Post table contains the posts that need to be loaded on the refresh of
   *   the page.
   * 
   * @return array
   *   This array contains the posts.
   */
  public function getPosts(object $postTable)
  {
    $posts = $postTable->findAll();

    // Construct a message containing the updated list of active users.
    $updatedPostList = [
      'type' => 'posts',
      'data' => [
        'posts' => $this->performOperation->postList($posts)
      ]
    ];
    return $updatedPostList;
  }
  /**
   * This function takes the posts table data and returns posts with corresponds 
   * likes.
   *
   * @param object  $postTable
   *   Post table contains all the posts data.
   * 
   * @return array
   *   Associative array of posts with corresponding likes.
   */
  public function getLikeCount(object $postTable)
  {
    // Fetching all the posts.
    $posts = $postTable->findAll();

    $postList = [];
    foreach ($posts as $post) {
      $postList[] = [
        "postId" => $post->getId(),
        "likes" => $this->performOperation->likes($post)
      ];
    }
    $updatedLikes = [
      'type' => 'likes',
      'data' => [
        'postLikes' => $postList
      ]
    ];
    return $updatedLikes;
  }
}