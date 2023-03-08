/**
 * This email is belongs to the user which will be initialized in the variable
 * so that when it required it will be accessed without any further computation.
 */
var email = '';
/**
 * This post will be initialized when user will create a new post instead of
 * loading all the posts again it will just add this post to all active users
 * feed list in real time.
 */
var post = '';
/**
 * Socket it is used to updated all the clients in real time and it is used
 * to update all the clients.
 */
var socket = '';

/**
 * This active users list retrieve current user email and profile image and 
 * set it in the home page when it loads for the first time. And if user is not
 * logged in and no email found from the list redirect to the login page.
 */
$.ajax({
  url: '/active-users',
  success: function (response) {

    // Getting email and user profile image.
    email = response.email;
    userImage = response.userImage;

    // Setting the image into profile image using Jquery.
    $('.profile').attr('src', 'http://' + location.hostname + '/userImage/' + userImage);

    // If user is not null and email is preset let other users know that this
    // user is now online and update the active user list accordingly using
    // socket.
    if (email) {
      // Active socket is used to take two arguments.
      activeSocket();
      } else {
      // If email is null, it means that user is not logged in and sent user
      // to login page.
      window.location = "/login";
    }
  }
});

/**
 * On the page load fetches all posts at once.
 */
$.ajax({
  url: '/created-posts',
  success: function (response) {
    response.posts.forEach(post => {

      // First adding posts in the feed.
      addPosts(post);

      // This block of code checks if the current user has already clicked
      // On the liked button or not, if used has already liked a post change
      // dislike button to show like in that post.
      post.likes.forEach(id =>{

        // Checks if email is same as all post likers emails.
        if(email === id.likes){
          
          $(`#like${post.postId}`).css('display', 'block');
          $(`#dislike${post.postId}`).css('display', 'none');
          
        }
      });
    });
  }
});

/**
 * On click the post button this ajax call will be triggered.
 */
$('#postForm').submit(function (event) {
  event.preventDefault();
  var formData = fetchFormData($(this).serializeArray())
  $.ajax({
    url: '/post',
    type: "POST",
    data: formData,
    async: true,
    processData: false,
    contentType: false,
    beforeSend: function () {
      showRightLoader();
    },
    success: function (response) {
      // Calling the socket to add the new post.
      // Here on the confirmation we would load all the posts again.
      if (response.post) {
        // Let user know that new post has been created successfully.
        $("#postArea").css("color", "green");
        $("#postArea").text("Post created successfully.");
      }
      hideRightLoader();
    },
    error: function (jqXHR, textStatus, errorThrown) {
      hideRightLoader();
    },
    complete: function () {
      hideRightLoader();
    }
  });
});

/**
 * This function activate socket connections. And on message it checks what
 * Kind of messages it has received and based on that it updates the frontend.
 */
function activeSocket() {

  // Starting the socket connection.
  socket = new WebSocket('ws://' + location.hostname + ':8080');

  socket.onopen = () => {
    sendDataToSocket();
  };

  socket.onmessage = (event) => {
    const message = JSON.parse(event.data);

    // A socket can listen multiple requests at once, so it's important to
    // filter out the incoming messages.
    switch (message.type) {

      case 'active-users':
        message.data.users.forEach(user => {
          if (isActive(user)) {
            addActiveUsers(user);
          } else {
            removeInactiveUsers(user);
          }
        });
        break;
      case 'posts':
        message.data.posts.forEach(post => {
          addPosts(post);
        });
        break;
      // TODO: Others users will know about new posts Like.
      
      // case 'likes':
      //     message.data.postLikes.forEach(postLike => {
      //       const postContent = document.getElementById(postLike.postId);
      //       if (postContent !== null) {
      //         // console.log(postContent)
      //         $(`#count${postLike.postId}`).text(postLike.likes.length);

      //         if($(`#count${postLike.postId}`).val > postLike.likes.length){
      //         }
      //       }
      //     });

    }
  };
}

function sendDataToSocket() {
  // On open first check if the socket is ready to send.
  if (socket.readyState === WebSocket.OPEN) {
    // Send this post and email and check if new posts are available in every
    // one second.
    setInterval(function () {
      socket.send(JSON.stringify({ "email": email}));
    }, 1000);
  }
}

/**
 * This function checks user last active time and if is more than 2 seconds
 * behind the current user will be removed from the list.
 * 
 * @param element
 *   This element contains the user last active time.
 * 
 * @returns boolean
 *   If user is currently active it will return true instead false.
 */
function isActive(element) {

  const now = new Date();
  const userDate = new Date(element.lastActiveTime.date);
  const diff = now - userDate;

  // If difference between current time and user last active time is less than
  // 2 seconds, then returns true.
  if (diff < 2000) {
    return true;
  }
  return false;
}

/**
 * This function checks if the user is already in the list of active users or
 * not. If the user is already in the list it will not update the list.
 * 
 * @param element 
 *   This element contains user information.
 */
function addActiveUsers(element) {

  // Fetch unordered list element from front-end.
  const activeUsersList = document.querySelector('.active-users-content');

  // If there is a already active user card present, in the list. Then Don't 
  // Duplicate the user.
  const activeUser = document.querySelector(`#user${element.userId}`);

  // If the user is not null it means user is already in the list and does not
  // need to include it again.
  if (activeUser !== null) {
    return;
  }

  // Creating a li element and setting class name and dynamic id.
  const divItem = document.createElement('li');
  divItem.className = 'list-group-item ';
  divItem.id = `user${element.userId}`;

  // Insert the card details into the li.
  divItem.innerHTML = `
    <div class="shadow cardUser">
      <div class="imgActiveUsers">
        <img src="http://${location.hostname}/userImage/${element.img}" width="30px" height="30px">
      </div>
      <div class="nameActiveUsers">
        <h4>${element.fullName}</h4>
      </div>
    </div>
  `;
  // It's important to append child each time to get multiple li inside ul.
  // activeUsersList.insertBefore(divItem, activeUsersList.firstChild);
  activeUsersList.appendChild(divItem);
}

function removeInactiveUsers(element) {
  const activeUser = document.querySelector(`#user${element.userId}`);

  // If the user is already in the list, remove it.
  if (activeUser) {
    activeUser.parentNode.removeChild(activeUser);
  }
}

/**
 * Add posts function is responsible for showing all posts one by one in a list
 * This function is called two times on when page loads and second when new 
 * post created.  
 */
function addPosts(post) {
  // Fetch unordered list element from front-end.
  const postContentList = document.querySelector('.post-content-list');

  // If there is a already active user card present, in the list. Then Don't 
  // Duplicate the user.
  const postContent = document.getElementById(post.postId);
  if (!postContent) {
    // Creating a li element and setting class name and dynamic id.
    const divItem = document.createElement('li');
    divItem.className = 'list-group-item post-content';
    divItem.id = post.postId;

    // Insert the card details into the li.
    divItem.innerHTML = `
  <div class="cardPost">
    <div class="header">
      <div class="imgName">
        <img src="http://${location.hostname}/userImage/${post.userImage}" height="35" width="35" alt="">
        <h4>${post.userName}</h4>
      </div>
      <div class="menuBox">
        <img class="menu" src="./img/menu.png" id="post${post.postId}" onclick="postMenu(this.id)" height="22" width="22" alt="">
        <img class="closeMenu" src="./img/closeBtn.png" id="post${post.postId}" onclick="closePostMenu(this.id)" height="22" width="22" alt="">

        <ul class="list-group subMenuForPost">
          <li class="list-group-item">
            <a id="deleteBtn" href="">Delete Post</a>
          </li>
        </ul>
      </div>
    </div>
    <hr id="postBorder">
    <div class="contentPost">
      <p>${post.content}</p>
    </div>
    <hr id="postBorder">
    <div class="likcmt">
      <div class="likeCount">
        <img src="./img/like.png" id="like${post.postId}" onclick="like(this.parentNode.parentNode.parentNode.parentNode.id)" class="likeBtn" alt="">
        <img src="./img/dislike.png" id="dislike${post.postId}" onclick="disLike(this.parentNode.parentNode.parentNode.parentNode.id)" class="dislikeBtn" alt="">
        <p id="count${post.postId}" class="count">${post.likes.length}</p>
      </div>
      <div class="CommentArea">
        <button class="comment" id="post${post.postId}" onclick="comment(this.id)">Comment</button>
        <button class="commentHide" id="post${post.postId}" onclick="hideComment(this.id)">Hide</button>
      </div>
      <div class="shareBtn">
        <button id="share">Share</button>
      </div>
    </div>
  </div>
  `;
    const secondChild = postContentList.children[1];
    postContentList.insertBefore(divItem, secondChild.nextSibling);

  }
}