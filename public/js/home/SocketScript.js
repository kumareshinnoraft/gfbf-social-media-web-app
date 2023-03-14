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

    if (userImage == "") {
      userImage = "avatar.png"
    }

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
 * On the page load fetches all posts at once. this block of ajax call is 
 * fetching all posts from the server.
 */
$.ajax({
  url: '/created-posts',
  success: function (response) {
    response.posts.forEach(post => {

      // First adding posts in the feed.
      addPosts(post);
      post = post;
      // This block of code checks if the current user has already clicked
      // On the liked button or not, if used has already liked a post change
      // dislike button to show like in that post.
      post.likes.forEach(id => {

        // Checks if email is same as all post likers emails.
        if (email === id.likes) {

          $(`#like${post.postId}`).css('display', 'block');
          $(`#dislike${post.postId}`).css('display', 'none');

        }
      });
    });
  }
});

/**
 * On click the post button this ajax call will be triggered and a post will be
 * updated in the feed.
 */
$('#postForm').submit(function (event) {
  event.preventDefault();
  var formData = fetchFormData($(this).serializeArray());

  var fileData = $('input[type="file"]')[0].files[0];
  formData.append("image", fileData);

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
        document.getElementById("postText").value = "";
        var defaultImage = `http://${location.hostname}/img/image-icon.png`;

        $('#post-file').attr('src', defaultImage);
      }
      hideRightLoader();
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.log(jqXHR.responseText);
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
    // When the connection is established call sendDataToSocket function to
    // send information to the socket.
    sendDataToSocket();
  };

  socket.onmessage = (event) => {
    const message = JSON.parse(event.data);

    // A socket can listen multiple requests at once, so it's important to
    // filter out the incoming messages.
    switch (message.type) {
      // If the values are catagories active user then first it will check if
      // user is active then addActiveUsers will be called and each user will 
      // be added.
      case 'active-users':
        message.data.users.forEach(user => {
          if (isActive(user)) {
            addActiveUsers(user);
          } else {
            removeInactiveUsers(user);
          }
        });
        break;
      // Same as active users this case will update each post in the list.
      case 'posts':
        message.data.posts.forEach(post => {
          addPosts(post);
        });
        break;
    }
  };
}
/**
 * This function checks if the socket is connected or not if it is not connected 
 * then each one second it will sent the current user email.
 */
function sendDataToSocket() {
  // On open first check if the socket is ready to send.
  if (socket.readyState === WebSocket.OPEN) {
    // Send this post and email and check if new posts are available in every
    // one second.
    setInterval(function () {
      socket.send(JSON.stringify({ "email": email }));
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

  // Current date and time.
  const now = new Date();
  // User last active time.
  const userDate = new Date(element.lastActiveTime.date);
  // Difference between current and last active time.
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

  if(element.img === "") {
    element.img = "avatar.png";
  }

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

/**
 * When the user will not be connected with the web socket it wil be remove
 * from the list of active users.
 * 
 * @param post
 *  This post object is the post that will be added in the feed.
 */
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
 * 
 * @param post
 *  This post object is the post that will be added in the feed.
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

    if(post.userImage === "") {
      post.userImage = "avatar.png";
    }

    // Insert the card details into the li.
    divItem.innerHTML = `
    <div class="cardPost">
    <div class="header">
      <div class="imgName">
        <img src="http://${location.hostname}/userImage/${post.userImage}" height="35" width="35" alt="">
        <h4>${post.userName}</h4>
      </div>
      <div class="menuBox" id="${post.postId}">
        <img class="menu" src="./img/menu.png" id="menuPost${post.postId}" postId="${post.postId}" onclick="postMenu(this.getAttribute('postId'))" height="22" width="22"
          alt="">
        <img class="closeMenu" id="closePostMenu${post.postId}" src="./img/closeBtn.png" postId="${post.postId}"  onclick="closePostMenu(this.getAttribute('postId'))"
          height="22" width="22" alt="">
  
        <ul class="list-group subMenuForPost" id="subMenuForPost${post.postId}">
          <li class="list-group-item">
            <a id="reportBtn" href="javascript:void(0);" >Report</a>
          </li>
        </ul>
      </div>
    </div>
    <hr id="postBorder">
    <div class="contentPost">
      <p>${post.content}</p>
      <img src="./postImage/${post.postImage}" onerror="this.style.display='none'" class="postImage" id="postImage${post.postId}" postId="${post.postId}">
    </div>
    <hr id="postBorder">
    <div class="likeComment">
      <div class="likeCount">
        <img src="./img/like.png" id="like${post.postId}" postId="${post.postId}"
          onclick="like(this.getAttribute('postId'))" class="likeBtn" alt="">
        <img src="./img/dislike.png" id="dislike${post.postId}" postId="${post.postId}"
          onclick="disLike(this.getAttribute('postId'))" class="dislikeBtn" alt="">
        <p id="count${post.postId}" class="count">${post.likes.length}</p>
      </div>
      <div class="CommentArea">
        <button class="comment" id="comment${post.postId}" postId="${post.postId}"
          onclick="comment(this.getAttribute('postId'))">Comments
          <span>${post.commentNo}</span></button>
        <button class="commentHide" id="commentHide${post.postId}" postId="${post.postId}"
          onclick="hideComment(this.getAttribute('postId'))">Hide</button>
      </div>
      <div class="shareBtn">
        <button postId="${post.postId}" onclick="shareBtn(this.getAttribute('postId'))" class="share">Share</button>
      </div>
    </div>
  </div>
  
  <div class="shadow commentSection" id="commentSection${post.postId}">
    <span id="commentError${post.postId}" class="errorComment"></span>
    <div class="commentInputArea">
      <textarea id="commentTextArea${post.postId}" placeholder="type your comment ..." name="comment" required></textarea>
      <button id="${post.postId}" onclick="postComment(this.id)">comment</button>
    </div>
    <div class="commentViewArea">
      <div class="comments">
        <ul class="list-group" id="commentsUl${post.postId}">
          <ul>
      </div>
    </div>
  </div>
  `;
    // Adding each post in the list on the top.
    const secondChild = postContentList.children[1];
    postContentList.insertBefore(divItem, secondChild.nextSibling);
  }
}
/**
 * Add comments function add comments one by one in the list.
 * 
 * @param comment
 *  This comment is the individual comment which will be added in the list.
 */
function addComments(comment) {
  // Fetch unordered list element from front-end.
  const commentUi = document.querySelector(`#commentsUl${comment.postId}`);

  // If there is a already active user card present, in the list. Then Don't 
  // Duplicate the user.
  const commentId = document.querySelector(`#comment${comment.commentId}`);

  // If the user is not null it means user is already in the list and does not
  // need to include it again.
  if (commentId !== null || comment.comment === "") {
    return;
  }
  // Creating a li element and setting class name and dynamic id.
  const divItem = document.createElement('li');
  divItem.className = 'list-group-item ';
  divItem.id = `comment${comment.commentId}`;

  if(comment.imageName === "") {
    comment.imageName = "avatar.png";
  }

  // Insert the card details into the li.
  divItem.innerHTML = `
  <div class="shadow commentCard">
  <div class="menuBox" id="${comment.commentId}">
  
  <div class="nameImg">
  <img src="http://${location.hostname}/userImage/${comment.imageName}" id="personBtn" width="25px" height="25px"
  alt="">
  <h4>${comment.fullName}</h4>
  </div>
    <img class="menu" src="./img/menu.png" id="menuComment${comment.commentId}" postId="${comment.commentId}" onclick="commentMenu(this.getAttribute('postId'))" height="15" width="15"
      alt="">
    <img class="closeMenu" id="closeCommentMenu${comment.commentId}" src="./img/closeBtn.png" postId="${comment.commentId}" onclick="closeCommentMenu(this.getAttribute('postId'))"
      height="15" width="15" alt="">

    <ul class="list-group subMenuForComment" id="subMenuForComment${comment.commentId}">
      <li class="list-group-item">
        <a id="reportBtn" href="javascript:void(0);" >Report</a>
      </li>
    </ul>
    </div>
    <div class="commentCardText">
      <p>${comment.comment}</p>
    </div>
  </div>
    `;
  // It's important to append child each time to get multiple li inside ul.
  commentUi.insertBefore(divItem, commentUi.firstChild);
}

/**
 * This post comment function post a comment in the feed of the user.
 * 
 * @param postID
 *  Post ID is the id of the post in which user will click the post.
 */
function postComment(postId) {

  // Each comment error span will contain the error message.
  var commentError = document.getElementById(`commentError${postId}`);

  // Input field of the comment.
  var inputVal = $(`#commentTextArea${postId}`).val();

  // Check if input comment field is not empty.
  if (inputVal === "") {
    commentError.textContent = "Comment should not be empty.";
    return;
  }

  // Calling ajax and passing input field value and post Id.
  $.ajax({
    url: '/postComment',
    type: "POST",
    data: {
      comment: inputVal,
      postId: postId
    },
    beforeSend: function () {
      showRightLoader();
    },
    success: function (response) {
      // If server successfully updated the comment.
      if (response.comment) {
        // If response is not NULL then reset the comment box and remove the
        // error message.
        commentError.textContent = "";
        document.getElementById(`commentTextArea${postId}`).value = "";

        // Looping through the comments and adding them to the list.
        response.comment.forEach(element => {
          addComments(element);
        });
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
}

/**
 * On the POST button new select image file button is added when a user will
 * select an image file it will labeled the image.
 */
$('#post-file').click(() => {
  $('.file-upload').click();
});

$(".file-upload").on('change', function () {
  readURL(this);
});

/**
 * This function takes the input file and labeled the file ain the select image 
 * icon.
 * 
 * @param input 
 *   Input is the file which will be selected by the user.
 */
function readURL(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    
    reader.onload = function (e) {
      $('#post-file').attr('src', e.target.result);
    }
    reader.readAsDataURL(input.files[0]);
  }
}
/**
 * On clicking on the profile icon this block of method will be executed.
 * this function first encode the URL and then redirect user to the link.
 */
function profile() {
  var token = encodeURIComponent(window.btoa(email));
  var profileLink = "http://" + location.hostname + "/profile/" + token;
  window.location.href = profileLink;
}