<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PerformedOperations;
use App\Service\Cryptography;
use App\Service\SendEmail;
use App\Service\Cookie;
use App\Entity\User;
use Exception;

/**
 * This Controller is responsible for validating user credentials and creating 
 * Users, resetting forgotten credentials and as well as OTP validation.
 *
 * @package Doctrine
 * @subpackage ORM
 * 
 * @author Kumaresh Baksi <kumaresh.baksi@innoraft.com>
 * @version 1.0
 * @license INNORAFT
 */
class AuthController extends AbstractController
{
  public const USER_IMAGE_PATH      = "../public/userImage";
  public const POSTS_IMAGE_PATH     = "../public/postImage";
  public const MESSAGE_SENT         = "Your One Time Password is";
  public const OTP_NOT_MATCHED      = "OTP not matched, try again";
  public const MAIL_EXISTS          = "Mail is already exists";
  public const USER_NOT_FOUND       = "User not found";
  private const RESET_PASSWORD_LINK = "/resetPassword?&id=";
  private const RESET_PASSWORD_MSG  = "Reset Password link is sent to your mail";
  private const WRONG               = "Something went wrong";
  private const PASSWORD_CHANGED    = "Password changed successfully";
  private const PASSWORD_WRONG      = "Password not valid";
  private const RESET_PASSWORD      = "Click the link for resetting password";
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
   * This user table object is used to point users database tables.
   * 
   * @var object
   */
  private $userTable;
  /**
   * This is the object of User class which is a entity having setter and 
   * getter methods.
   * 
   * @var object
   */
  private $user;
  /**
   * Cryptography object encode and decode values before
   * sending in link or storing password.
   *
   * @var object
   */
  private $cryptography;
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
    $this->userTable        = $em->getRepository(User::class);
    $this->performOperation = new PerformedOperations();
    $this->cryptography     = new Cryptography();
    $this->sendMail         = new SendEmail();
    $this->cookie           = new Cookie();
    $this->user             = new User();
    $this->em               = $em;
  }
  /**
   * This register routes validate user inserted data and calls for
   * OTP verification.
   *   
   * @Route("/register", name="registerUser")
   *   This route goes to register page of the project.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function register(Request $request): Response
  {
    if ($this->cookie->isActive($request)) {
      return $this->redirectToRoute('home');
    } 
    elseif ($request->request->all()) {

      // Storing all incoming values in variables
      $fullName = $request->request->get('fullName');
      $email    = $request->request->get('email');
      $password = $request->request->get('password');
      $gender   = $request->request->get('gender');
      $userName = substr($email, 0, strrpos($email, '@'));
      
      $checkUserEmail = $this->userTable->findOneBy(['email' => $email]);

      // If mail is already exists show this message.
      if ($checkUserEmail) {
        return new JsonResponse(['msg' => AuthController::MAIL_EXISTS]);
      } 
      elseif ($request->files->has('image')) {
        // Storing image in the project directory.
        $this->imageName = $this->performOperation->storeImg($userName, AuthController::USER_IMAGE_PATH, $request->files->get('image'));
      }

      // Encoding password and generating OTP and Current Time.
      $encodedPassword = $this->cryptography->encode($password);
      $otp             = $this->performOperation->generateOtp();
      $currentTime     = $this->performOperation->currentTime();

      // Store values in the user.
      $this->user->setUserDetails($fullName, $userName, $email, $this->imageName, $gender, $encodedPassword, $otp, $currentTime, $currentTime, FALSE);
      try {
        $this->em->persist($this->user);
        $this->em->flush();
      } 
      catch (Exception $th) {
        return new JsonResponse(['msg' => $th->getMessage()]);
      }
      // Send OTP to the mail
      $sendMail = $this->sendMail->sendEmail($email, $otp, AuthController::MESSAGE_SENT);

      if ($sendMail) {
        // Storing user information in cookie.
        $this->cookie->setCookie(['email' => $email]);

        return new JsonResponse(['mail' => TRUE]);
      }
      return new JsonResponse(['msg' => $sendMail]);
    }
    return $this->render('auth/register.html.twig');
  }
  /**
   * This reset password route redirect user to reset password page where two
   * password fields are shown to the user.
   *   
   * @Route("/login", name="loginUser")
   *   This route goes to reset page of the resetting password.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function login(Request $request): Response
  {
    // Check if the user is already logged in.
    if ($this->cookie->isActive($request)) {
      return $this->redirectToRoute('home');
    } 
    // Checks if the ajax request is present.
    elseif ($request->request->all()) {

      $email    = $request->request->get('email');
      $password = $request->request->get('password');
      $userRow = $this->userTable->findOneBy(['email' => $email]);

      // Check if the user is present in the database.
      if (!$userRow) {
        return new JsonResponse(['msg' => AuthController::USER_NOT_FOUND]);
      }
      // Decode the password and match with the password present in the
      // database.
      $decodedPassword = $this->cryptography->decode($userRow->getPassword());

      if (!strcmp($password, $decodedPassword)) {
        $this->cookie->setCookie(['email' => $userRow->getEmail()]);

        return new JsonResponse(['result' => TRUE]);
      }
      return new JsonResponse(['msg' => AuthController::PASSWORD_WRONG]);
    }
    return $this->render('auth/login.html.twig');
  }
  /**
   * This reset password route redirect user to reset password page where two
   * password fields are shown to the user.
   *   
   * @Route("/resetPassword", name="resetPassword")
   *   This route goes to reset page of the resetting password.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function resetPassword(Request $request): Response
  {
    // Getting both password and new password field.
    $newPassword = $request->request->get('password1');
    $password    = $request->get('password2');

    // Id in the URL parameters.
    $id = $request->get('id');

    // Decoding ID and concatenating base64 encoded code.
    $decodedId = $this->cryptography->decode($id . "%3D");

    // Find the user row from the table with the id of the user.
    $selectedRow = $this->userTable->findOneBy(['id' => $decodedId]);

    // If the user is not null then fetch the email of the user and store it
    // in the cookie.
    if ($selectedRow) {
      $this->cookie->setCookie(['email' => $selectedRow->getEmail()]);
    }
    $email = $this->cookie->getCookie("email", $request);

    // If password is NULL then show user this page again.
    if (!$newPassword) {
      // If password is NULL return the same page.
      return $this->render('auth/resetPassword.html.twig');
    } 
    elseif (!$email) {
      return $this->render('404.html.twig');
    }
    // Encoding the new password before storing it in the database.
    $encodedPassword = $this->cryptography->encode($newPassword);

    // If password is inserted then fetch the row again from the database.
    $userSelectedRow = $this->userTable->findOneBy(['email' => $email]);

    // Checks if user exits then encode the password and update the database.
    if ($userSelectedRow && $password == $newPassword) {

      $userSelectedRow->setPassword($encodedPassword);
      $this->cookie->removeCookie($request);

      $this->em->persist($userSelectedRow);
      $this->em->flush();

      return $this->render(
        'auth/login.html.twig',
        [
          'error' => AuthController::PASSWORD_CHANGED
        ]
      );
    }
    return $this->render('auth/resetPassword.html.twig');
  }
  /**
   * This register routes validate user inserted data and calls for
   * OTP verification.
   *   
   * @Route("/forgetPassword", name="forgetPassword")
   *   This route goes to register page of the project.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function forgetPassword(Request $request): Response
  {
    if ($this->cookie->isActive($request)) {
      return $this->redirectToRoute('home');
    } 
    elseif ($request->request->all()) {

      $email = $request->request->get('email');
      // Get the row of user with email.
      $userRow = $this->userTable->findOneBy(['email' => $email]);

      if (!$userRow) {
        // If userRow is not present, returns user not found.
        return new JsonResponse(['msg' => AuthController::USER_NOT_FOUND]);
      }
      // Encrypting user id before sending mail.
      $id = $this->cryptography->encode($userRow->getId());

      if ($this->sendMail->sendEmail($email, "http://" . $_SERVER['SERVER_NAME'] . AuthController::RESET_PASSWORD_LINK . $id, AuthController::RESET_PASSWORD)) {
        return new JsonResponse(['msg' => AuthController::RESET_PASSWORD_MSG]);
      }
      return new JsonResponse(['msg' => AuthController::WRONG]);
    }
    return $this->render('auth/forgetPassword.html.twig');
  }
  /**
   * This routes verify user inserted OTP and OTP send by server are matching.
   *   
   * @Route("/otp", name="otp")
   *   This routes verify user user OTP.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function otp(Request $request): Response
  {
    // This block concatenates four input fields inserted 
    // data into one string.
    $otp = $request->request->get('1') . $request->request->get('2') . $request->request->get('3') . $request->request->get('4');

    // Fetch the email from cookie.
    $email   = $this->cookie->getCookie("email", $request);
    $userRow = $this->userTable->findOneBy(['email' => $email]);

    // Getting the latest OTP value from database.
    $sentOtp = $userRow->getOtp();
    // Check the OTP's are matching.
    if ($otp == $sentOtp) {
      // Update in the database that user is verified.
      $userRow->setVerified(TRUE);
      $this->em->persist($userRow);
      $this->em->flush();

      return new JsonResponse(["otp" => TRUE]);
    }
    return new JsonResponse(['msg' => AuthController::OTP_NOT_MATCHED]);
  }
  /**
   * Resend OTP sends OTP to the user mail again and it updates the database
   * with latest OTP and OTP creation timestamp.
   *   
   * @Route("/resendOTP", name="resendOTP")
   *   This route is for sending OTP to the user mail.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function resendOTP(Request $request): Response
  {
    $email           = $this->cookie->getCookie("email", $request);
    $otp             = $this->performOperation->generateOtp();
    $otpCreationTime = $this->performOperation->currentTime();

    if ($this->sendMail->sendEmail($email, $otp, AuthController::MESSAGE_SENT)) {
      // Update the database with opt and time.
      $userRow = $this->userTable->findOneBy(['email' => $email]);
      if ($userRow) {
        // Update in the new OTP and time in the database.
        $userRow->setOtp($otp);
        $userRow->setOtpCreationTime($otpCreationTime);
        try {
          $this->em->persist($userRow);
          $this->em->flush();
        } 
        catch (Exception $th) {
          return new JsonResponse(['msg' => $th->getMessage()]);
        }
        return new JsonResponse(['msg' => TRUE]);
      }
      return new JsonResponse(['msg' => AuthController::USER_NOT_FOUND]);
    }
    return new JsonResponse(['msg' => FALSE]);
  }
  /**
   * Destroy all sessions and remove cookies.
   *   
   * @Route("/logout", name="logout")
   *   This route is for sending user to the home screen.
   * 
   * @param object $request
   *   Request object handles parameter from query parameter.
   * 
   * @return Response
   *   Response the view which contains user stored information.
   */
  public function logout(Request $request): Response
  {
    if($request->get('flag')) {
      // Removing cookies and sessions from the browser.
      $this->cookie->removeCookie($request);
      $session = $request->getSession();
      $session->invalidate();
      
      return new JsonResponse(['msg' => TRUE]);
    }
    return $this->render('404.html.twig');
  }
}