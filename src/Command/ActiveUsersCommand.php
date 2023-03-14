<?php

namespace App\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Ratchet\WebSocket\WsServer;
use App\Websocket\ActiveUsers;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;

/**
 * Active Users Command class turns active the web socket connection. By default
 * the constructor does not takes any doctrine class but after mentioning 
 * service.yaml configuration this class enables to take Entity Manager
 * interface. And this class extends the Command class which provides SYMFONY.
 * 
 * @extends Command
 *   Command is the symfony inbuilt class which is used to create custom
 *   commands.
 */
class ActiveUsersCommand extends Command
{
  /**
   * This command is the name of the command which will turn on websocket and
   * start listening client side connections.
   * 
   * @var string
   */
  protected static $defaultName = "run:websocket-server";
  /**
   * Entity Manager class object that manages the persistence and 
   * retrieval of entity objects from the database.
   * 
   * @var object
   */
  private $em;
  /**
   * This constructor is used to initialize the the entity manager interface
   * and calls the parent constructor.
   *
   * @param EntityManagerInterface $entityManagerInterface
   *   This entity manager interface is used to manipulate the database.
   */
  public function __construct(EntityManagerInterface $entityManagerInterface)
  {
    $this->em = $entityManagerInterface;

    // Calling parent class constructor.
    parent::__construct();
  }
  /**
   * This execute function is called when the command is executed in the
   * terminal.
   *
   * @param InputInterface $input
   *   In the command line input takes the message from the user.
   * @param OutputInterface $output
   *   In the command line output provides the output from the user.
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // So, we can not run this service directly in 80 port as this application
    // is running on that port and it is busy.
    $port = 8080;
    $output->writeln("Starting server on port " . $port);
    $server = IoServer::factory(
      new HttpServer(
        new WsServer(
          new ActiveUsers($this->em)
        )
      ),
      $port
    );
    // After setting all the ports and mentioning the class i wanted to execute
    // Run the server.
    $server->run();
  }
}