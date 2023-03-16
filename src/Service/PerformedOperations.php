<?php

namespace App\Service;

use App\Controller\HomeController;
use DateTimeImmutable;
use Exception;
use DateTime;

/**
 * This class is for different methods that will be performed inside controller.
 * This controller helps many times throughout the whole application.
 * 
 * @package DateTimeImmutable
 * @package DateTime
 * @package Exception
 * 
 * @author Kumaresh Baksi <kumaresh.baksi@innoraft.com>
 */
class PerformedOperations
{
  /**
   * This function stores user image in the project directory. This function 
   * takes three arguments and according to the need this function store the
   * file in the project directory.
   *
   * @param string $name
   *  This variable is the unique user name of the user.
   * @param string $location
   *  This variable is the location of the project directory where image
   *  will be stored.
   * @param object $image
   *  This variable is the object of the user image.
   * 
   * @return mixed
   *  If function stored the image successfully it returns name of the image
   *  if it does not then it will return FALSE otherwise.
   */
  public function storeImg(string $name, string $location, object $image)
  {
    // Guess extension of the image.
    $name = $name . "." . $image->guessExtension();
    try {
      // Moving the file in the location.
      $image->move($location, $name);
    } 
    catch (Exception $ex) {
      return FALSE;
    }
    return $name;
  }
  /**
   * This function generates random number for OTP.
   *
   * @return int
   *  rand function returns integer.
   */
  public function generateOtp()
  {
    return rand(1000, 9999);
  }
  /**
   * This function return date and time in a immutable format.
   *
   * @return DateTimeImmutable
   *  Returning date and time in a immutable format.
   */
  public function currentTime()
  {
    return DateTimeImmutable::createFromFormat(DateTime::RFC3339, (new DateTime())->format(DateTime::RFC3339));
  }
  /**
   * This function returns the posts contained data, this can be used to sent
   * to ajax.
   * 
   * @param array $posts
   *   This posts contains an array of posts objects.
   *
   * @return array
   *  Returning array of posts data.
   */
  public function postList(array $posts)
  {
    $postList = [];
    // Iterating the users list to individual users list.
    foreach ($posts as $post) {
      $postList[] = [
        'userImage' => $post->getUser()->getImageName(),
        'userName'  => $post->getUser()->getFullName(),
        'commentNo' => count($this->comments($post)),
        'userId'    => $post->getUser()->getId(),
        'createdAt' => $post->getCreatedAt(),
        'updatedAt' => $post->getUpdatedAt(),
        'content'   => $post->getContent(),
        'likes'     => $this->likes($post),
        'postImage' => $post->getImage(),
        'postLikes' => $post->getLikes(),
        'postId'    => $post->getId(),
      ];
    }
    return $postList;
  }
  /**
   * This function creates a array for storing user data.
   * 
   * @param array $users
   *   This function will be used to fetch different user data and store in
   *   an array.
   *
   * @return array
   *  Returning array of users data.
   */
  public function getUserData(array $users)
  {
    $userList = [];
    // Iterating the users list to individual users list.
    foreach ($users as $user) {
      $userList[] = [
        'lastActiveTime' => $user->getLastActiveTime(),
        'img'            => $user->getImageName(),
        'fullName'       => $user->getFullName(),
        'userId'         => $user->getId()
      ];
    }
    return $userList;
  }
  /**
   * Sanitize data is used to validate user inserted input if user has inserted
   * any malicious value ini the input field, this function will not allow it to
   * execute.
   *
   * @param string $data
   *   This data is the string that will be sanitized.
   * 
   * @return string
   *   This string data is the sanitized string data.
   */
  public function sanitizeData(string $data)
  {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
  }

  /**
   * This like returns the users who have liked a post.
   *
   * @param object $post
   *   This post is the a post which will have likes and will be sent to the 
   *   client side.
   * 
   * @return array
   *   An array of users who have liked the post.
   */
  public function likes(object $post)
  {
    $likes = [];
    foreach ($post->getLikes() as $like) {
      $likes[] = [
        "likes" => $like->getUser()->getEmail()
      ];
    }
    return $likes;
  }
  /**
   * This block of functions extracts the comments and stored in the array.
   *
   * @param object $post
   *   This post is the a post which will have comments and will be sent to the 
   *   client side.
   * 
   * @return array
   *   An array of comments of the post.
   */
  public function comments(object $post)
  {
    $comments = [];
    foreach ($post->getComments() as $comment) {
      $comments[] = [
        "imageName" => $comment->getUser()->getImageName(),
        "fullName"  => $comment->getUser()->getFullName(),
        "postId"    => $comment->getPost()->getId(),
        "comment"   => $comment->getContent(),
        "commentId" => $comment->getId()
      ];
    }
    return $comments;
  }
  /**
   * This single post information returns information about a post.
   *
   * @param object $post
   *   This post object contains information about the post.
   * 
   * @return array
   *   An array of information about the post.
   */
  public function singlePostInformation(object $post)
  {
    // Getting user image name.
    $userImage = $post->getUser()->getImageName();

    if($userImage == NULL) {
      $userImage = 'avatar.png';
    }
    // Creating the array of post information.
    return [
      "userName"    => $post->getUser()->getFullName(),
      "comments"    => count($this->comments($post)),
      "postContent" => $post->getContent(),  
      "image"       => $post->getImage(),
      "userImage"   => $userImage
    ];
  }
  /**
   * This function returns a single user data.
   *
   * @param object $user
   *   This user object contains user information including user profile link.
   * @param string $hostName
   *   Host name is require for creating the dynamic link.
   * @param string $encodedProfileId
   *   To identify the unique profile id, encoded profile id is required.
   * 
   * @return array
   *   An array of information about the user.
   */
  public function singleUserInformation(object $user, string $hostName, string $encodedProfileId)
  {
    // Creating a profile link.
    $profileLink = HomeController::HTTP . $hostName . HomeController::PROFILE_IMAGE_PATH . $encodedProfileId;

    // Getting user image and if it doesn't exist default name is avatar.png.
    $userImage   = $user->getImageName();
    if($userImage == NULL) {
      $userImage = 'avatar.png';
    }
    // Creating the array of user information.
    return [
      "fullName"    => $user->getFullName(),
      "gender"      => $user->getGender(),
      "email"       => $user->getEmail(),
      "profileLink" => $profileLink,
      "userImage"   => $userImage
    ];
  }
}