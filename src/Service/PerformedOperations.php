<?php

namespace App\Service;

use DateTimeImmutable;
use Exception;
use DateTime;

/**
 * This class is for different methods that will be performed inside 
 * controller. 
 * 
 * @method storeImg()
 *   Store user image in the database.
 *  
 * @author Kumaresh Baksi <kumaresh.baksi@innoraft.com>
 */
class PerformedOperations
{
  /**
   * This function stores user image in the project directory.
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
    $name = $name . "." . $image->guessExtension();
    try {
      $image->move($location, $name);
    } catch (Exception $ex) {
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
   * @return array
   *  Returning array of posts data.
   */
  public function postList(array $posts)
  {
    $postList = [];
    // Iterating the users list to individual users list.
    foreach ($posts as $post) {
      $postList[] = [
        'postId' => $post->getId(),
        'userId' => $post->getUser()->getId(),
        'userImage' => $post->getUser()->getImageName(),
        'userName' => $post->getUser()->getFullName(),
        'postLikes' => $post->getLikes(),
        'content' => $post->getContent(),
        'createdAt' => $post->getCreatedAt(),
        'updatedAt' => $post->getUpdatedAt(),
        'likes' => $this->likes($post)
      ];
    }
    return $postList;
  }
  /**
   * This function creates a array for storing user data.
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
        'fullName' => $user->getFullName(),
        'img' => $user->getImageName(),
        'lastActiveTime' => $user->getLastActiveTime(),
        'userId' => $user->getId()
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
  public function sanitizeData($data)
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
}