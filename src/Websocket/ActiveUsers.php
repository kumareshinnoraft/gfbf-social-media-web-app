<?php

namespace App\Websocket;

use Doctrine\ORM\EntityManagerInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Service\UserService;
use SplObjectStorage;
use App\Entity\Post;
use App\Entity\User;
use Exception;

/**
 * Active Users class is the web socket which is used to updates active users
 * in realtime to all the other users. It implements MessageComponentInterface
 * which provides multiple methods like onError, onMessage, onOpen, onClose etc.
 * 
 * @see https://packagist.org/packages/cboden/ratchet
 */
class ActiveUsers implements MessageComponentInterface
{
  /**
   * Connection is an array which contains all the users who have connection
   * with the socket.
   *
   * @var object
   */
  protected $connections;
  /**
   * User service is a service which fetch users information and set active
   * TRUE and last active time.
   *
   * @var object
   */
  private $userService;
  /**
   * Entity Manager class object that manages the persistence and 
   * retrieval of entity objects from the database.
   * 
   * @var object
   */
  public $em;
  /**
   * Email is contained the users unique id which will is received when from the
   * client side and this will be used to fetch other information from Database.
   *
   * @var string
   */
  private $email;
  /**
   * Post is basically fetching the updated post for informing all connections.
   *
   * @var object
   */
  private $postTable;
  /**
   * Post is basically fetching the updated post for informing all connections.
   *
   * @var object
   */
  private $userTable;
  /**
   * Constructor is called in the Command constructor and it initializes
   * spl objects storage.
   *
   * @param EntityManagerInterface $entityManager
   *   Entity manager is a doctrine class which helps to do multiple operations
   *   on a single entity
   * 
   * @return void
   *   Constructor does not returns anything, it is used to initialize the
   *   object.
   */
  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->em          = $entityManager;
    $this->userService = new UserService;
    $this->connections = new SplObjectStorage;
    $this->postTable   = $entityManager->getRepository(Post::class);
    $this->userTable   = $entityManager->getRepository(User::class);
  }
  /**
   * This function takes an array of connections and on open it attaches the
   * connections.
   *
   * @param ConnectionInterface $conn
   *   This object conn having a value of unique id of each individual connection.
   * 
   * @return void
   *   This function does returns nothing.
   */
  public function onOpen(ConnectionInterface $conn)
  {
    $this->connections->attach($conn);
  }
  /**
   * This function provides message from the client connection and sends message
   * to the sender.
   *
   * @param ConnectionInterface $from
   *   This from contains the sender id.
   * @param string $msg
   *   Message contains the message sent to the sender.
   * 
   * @return void
   *   This function does return anything instead calls user service class to
   *   fetch the the particular user information and broadcast it to all clients.
   */
  public function onMessage(ConnectionInterface $from, $msg)
  {
    // Receiving the message email from client side.
    $this->email = json_decode($msg, TRUE)['email'];

    // If email is not NULL, fetch the user's details and BROADCAST them
    // through all the connections.
    if ($this->email) {
      $this->broadcastActiveUsers();
    }
  }
  /**
   * This function takes an array of connections and on close, it detach the
   * connections.
   *
   * @param ConnectionInterface $conn
   *   This object conn having a value of unique id of each individual connection.
   * 
   * @return void
   *   This function does returns nothing.
   */
  public function onClose(ConnectionInterface $conn)
  {
    $this->connections->detach($conn);
  }
  /**
   * This function checks the error and return the exception and close the 
   * connection.
   *
   * @param ConnectionInterface $conn
   *   This object conn having a value of unique id of each individual
   *   connection.
   * @param Exception $e
   *   Exception instance tells the reason of the error.
   * 
   * @return void
   *   This function does returns nothing.
   */
  public function onError(ConnectionInterface $conn, Exception $e)
  {
    $this->connections->detach($conn);
    $conn->close();
  }
  /**
   * Broadcast active users sends the updated active users to all the
   * connections.
   *
   * @param object $conn
   *   This object conn having a value of unique id of each individual
   *   connection
   * 
   * @return void
   *   This function does returns nothing.
   */
  private function broadcastActiveUsers()
  {
    // Broadcast the message to all connected clients.
    foreach ($this->connections as $client) {
      $client->send(json_encode($this->userService->getUserByEmail($this->email, $this->userTable, $this->em)));
      $client->send(json_encode($this->userService->getPosts($this->postTable)));
    }
  }
}