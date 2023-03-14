/**
 * When user will click the dislike button this function will be called and 
 * it will call add like button.
 * 
 * @param postId
 *   This post id the post in which dislike button is pressed.
 */
function disLike(postId) {
  $(`#like${postId}`).css('display', 'block');
  $(`#dislike${postId}`).css('display', 'none');
  addLike(postId);
}
/**
 * When user will click the like button this function will be called and 
 * it will call add dislike button.
 * 
 * @param postId
 *   This post id the post in which like button is pressed.
 */
function like(postId) {
  $(`#dislike${postId}`).css('display', 'block');
  $(`#like${postId}`).css('display', 'none');
  removeLike(postId);
}
/**
 * When user clicks comment button comments will be shown to the user.
 * 
 * @param commentId
 *   Comment ID is to uniquely identify which post is chosen and which comments
 *   will be shown.
 */
function comment(commentId) {

  closePostMenu(commentId);

  $(`#commentSection${commentId}`).css('display', 'block');
  $(`#comment${commentId}`).css('display', 'none');
  $(`#commentHide${commentId}`).css('display', 'block');
  showComment(commentId);
}
/**
 * When user clicks hide comment button comments will be disappear to the user.
 * 
 * @param commentId
 *   Comment ID is to uniquely identify which post is chosen and which comments
 *   will be removed.
 */
function hideComment(commentId) {
  $(`#commentSection${commentId}`).css('display', 'none');
  $(`#comment${commentId}`).css('display', 'block');
  $(`#commentHide${commentId}`).css('display', 'none');

  // Setting the comment count value
  $(`#comment${commentId} span`).html(($(`#commentsUl${commentId} li`).length) / 2)
}
/**
 * When the post menu is clicked many things checks simultaneously. first we 
 * close the comment box if it is opened then we open the menu to the following
 * post through the post ID.
 * 
 * @param postId
 *   This post id defines the unique ID of the post so that we can identify
 *   in which post we have to add the check.
 */
function postMenu(postId) {

  // Hide comment box.
  hideComment(postId);

  // Toggling between close menu icon and menu icon.
  $(`#subMenuForPost${postId}`).css('display', 'block');
  $(`#closePostMenu${postId}`).css('display', 'block');
  $(`#menuPost${postId}`).css('display', 'none');

  // This block of functionality which is required to find the sub menu list
  // of posts.
  showPostMenuOptions(postId);
}
/**
 * On clicking the close menu icon, the close menu icon will disappear and menu
 * options will be closed.
 * 
 * @param postId
 *   This post id defines the unique ID of the post so that we can identify
 *   in which post we have to add the check.
 */
function closePostMenu(postId) {
  $(`#subMenuForPost${postId}`).css('display', 'none');
  $(`#closePostMenu${postId}`).css('display', 'none');
  $(`#menuPost${postId}`).css('display', 'block');
}
/**
 * When the comment menu is clicked many things checks simultaneously. it makes
 * display on of the close menu.
 * 
 * @param commentId
 *   This comment ID defines the unique ID of the comment.
 */
function commentMenu(commentId) {
  $(`#subMenuForComment${commentId}`).css('display', 'block');
  $(`#closeCommentMenu${commentId}`).css('display', 'block');
  $(`#menuComment${commentId}`).css('display', 'none');

  // This function checks if the user is the author of the comment or not.
  showCommentMenuOptions(commentId);
}
/**
 * Closing the comment menu options from the screen.
 * 
 * @param commentId
 *   This comment ID defines the unique ID of the comment.
 */
function closeCommentMenu(commentId) {
  $(`#subMenuForComment${commentId}`).css('display', 'none');
  $(`#closeCommentMenu${commentId}`).css('display', 'none');
  $(`#menuComment${commentId}`).css('display', 'block');
}

/**
 * This blog of code is for toggling profile.
 */
var flag = true;
$('.profile').click(() => {
  if (flag) {
    $('.subMenuProfile').css('display', 'block');
    flag = false;
  } else {
    $('.subMenuProfile').css('display', 'none');
    flag = true;
  }
});
/**
 * Show post menu options function checks if the current user is same as the
 * post owner or not if it proofs that the user is the post owner then a sub
 * menu containing the edit and delete options will be shown.
 * 
 * @param postId
 *   This is the ID of the post which will be used to check th author of the
 *   post.
 */
function showPostMenuOptions(postId) {
  $.ajax({
    url: '/checkPostVsUser',
    method: 'POST',
    data: {
      postId: postId
    },
    beforeSend: function () {
      showRightLoader();
    },
    success: function (response) {
      if (response.USER) {
        // This is the function which lists the menu options for the user.
        modifyPost(postId);
      }
      hideRightLoader();
    },
    error: function (xhr, status, error) {
      hideRightLoader();
      console.log('Error:', xhr.responseText);
    }
  });
}
/**
 * Show comment menu options function checks if the current user is same as the
 * post owner or not if it proofs that the user is the comment owner then a sub
 * menu containing the edit and delete options will be shown.
 * 
 * @param commentId
 *   This is the ID of the comment which will be used to check the author of the
 *   post.
 */
function showCommentMenuOptions(commentId) {
  $.ajax({
    url: '/checkCommentVsUser',
    method: 'POST',
    data: {
      commentId: commentId
    },
    beforeSend: function () {
      showRightLoader();
    },
    success: function (response) {
      if (response.USER) {
        // This functions will list the sub menu items for a particular comment.
        modifyComment(commentId);
      }
      hideRightLoader();
    },
    error: function (xhr, status, error) {
      hideRightLoader();
      console.log('Error:', xhr.responseText);
    }
  });
}
/**
 * This modify comment function will be responsible for loading the sub menu
 * for comment menu with edit and delete options. 
 * 
 * @param commentId 
 *   Comment id is the unique identifier for each comment.
 */
function modifyComment(commentId) {
  // Fetch the submenu where in which user is clicking.
  const ulForMenu = document.querySelector(`#subMenuForComment${commentId}`);

  // Check if list contains delete button or not.
  const deleteBtnComment = document.querySelector(`#deleteBtnComment${commentId}`);
  if (deleteBtnComment !== null) {
    return;
  }

  // Create a li for adding a new field.
  const divItem = document.createElement('div');

  // Insert the delete button in the list.
  divItem.innerHTML = `
      <li class='list-group-item'>
        <a href="javascript:void(0);" id="commentEdit${commentId}" class="postEdit" postId="${commentId}" onclick="editComment(this.getAttribute('postId'))" >Edit</a>
      </li>
      <li class='list-group-item'>
        <a href="javascript:void(0);" id="deleteBtnComment${commentId}" class="postDeleteBtn" postId="${commentId}" onclick="deleteComment(this.getAttribute('postId'))">Delete</a>
      </li>
      `;
  // Append the child.
  ulForMenu.appendChild(divItem);
}
/**
 * This modify comment function will be responsible for loading the sub menu
 * for post menu with edit and delete options. 
 * 
 * @param postId 
 *   Post id is the unique identifier for each comment.
 */
function modifyPost(postId) {
  // Fetch the submenu where in which user is clicking.
  const ulForMenu = document.querySelector(`#subMenuForPost${postId}`);

  // Check if list contains delete button or not.
  const deleteBtn = document.querySelector(`#deleteBtn${postId}`);
  if (deleteBtn !== null) {
    return;
  }

  // Create a li for adding a new field.
  const divItem = document.createElement('div');

  // Insert the delete button in the list.
  divItem.innerHTML = `
    <li class='list-group-item'>
      <a href="javascript:void(0);" id="postEdit${postId}" class="postEdit" postId="${postId}" onclick="editPost(this.getAttribute('postId'))">Edit</a>
    </li>
    <li class='list-group-item'>
      <a href="javascript:void(0);" id="deleteBtn${postId}" class="postDeleteBtn" postId="${postId}" onclick="deletePost(this.getAttribute('postId'))">Delete</a>
    </li>
    `;
  // Append the child.
  ulForMenu.appendChild(divItem);
}
/**
 * Delete post button sends an ajax call to the server with the post id for 
 * deleting the post.
 * 
 * @param postId 
 *   Post id is the unique identifier for each post.
 */
function deletePost(postId) {

  const post = document.getElementById(postId);

  // If the user is already in the list, remove it.
  if (post) {
    $.ajax({
      url: '/deletePost',
      method: 'POST',
      data: {
        postId: postId
      },
      beforeSend: function () {
        showRightLoader();
      },
      success: function (response) {
        // On the success loader will be stopped.
        hideRightLoader();
      },
      error: function (xhr, status, error) {
        hideRightLoader();
        console.log('Error:', xhr.responseText);
      }
    });
    post.parentNode.removeChild(post);
  }
}
/**
 * Edit post option enable a modal dialog containing the edit text option for
 * editing the post.
 * 
 * @param postId 
 *   Post id is the unique identifier for each post.
 */
function editPost(postId) {
  $('.modal').css('display', 'block');
  $("#update").attr("id", `${postId}`);
  $("#updatePost").val($(`#${postId} p`).html());
}
/**
 * When the button will be pressed for updating a post this function will be
 * called.
 * 
 * @param postId 
 *   Post id is the unique identifier for each post.
 */
function updateFunction(postId) {
  var updatedPost = $("#updatePost").val();
  $.ajax({
    url: '/updatePost',
    method: 'POST',
    data: {
      postId: postId,
      updatedPost: updatedPost
    },
    beforeSend: function () {
      showRightLoader();
    },
    success: function (response) {

      // If update is successful this block of code will be executed.
      if (response.UPDATE) {

        // Closing modal and post menu
        closeModal();
        closePostMenu(postId);

        // Updating the new post content.
        $(`#${postId} .contentPost p`).text(updatedPost);
      }
      hideRightLoader();
    },
    error: function (xhr, status, error) {
      hideRightLoader();
      console.log('Error:', xhr.responseText);
    }
  });
}
/**
 * Edit comment function same as post comment which enables a modal dialog with
 * a input field.
 * 
 * @param commentId 
 *   Comment id is the unique identifier for each comment.
 */
function editComment(commentId) {
  $('.commentModal').css('display', 'block');
  $("#updateComment").attr("id", `${commentId}`);
  $("#updateCommentArea").val($(`#comment${commentId} .commentCard .commentCardText p`).html());
}
/**
 * Update comment function updates the comment without page refresh through the
 * ajax request.
 * 
 * @param commentId 
 *   Comment id is the unique identifier for each comment.
 */
function updateComment(commentId) {
  var updatedComment = $("#updateCommentArea").val();
  $.ajax({
    url: '/updateComment',
    method: 'POST',
    data: {
      commentId: commentId,
      updatedComment: updatedComment
    },
    beforeSend: function () {
      showRightLoader();
    },
    success: function (response) {
      // If update is successful this block of code will be executed.
      if (response.UPDATE) {
        // Closing modal and post menu.
        closeCommentModal();
        // Updating the new post content.
        $(`#comment${commentId} .commentCard .commentCardText p`).text(updatedComment);
      }
      hideRightLoader();
    },
    error: function (xhr, status, error) {
      hideRightLoader();
      console.log('Error:', xhr.responseText);
    }
  });
}
/**
 * Share button enable a modal with a input field and a link will shown to the
 * user.
 * 
 * @param postId 
 *   Post id is the unique identifier for each comment.
 */
function shareBtn(postId) {
  $('.copyLinkModel').css('display', 'block');

  // Encoding the post id.
  var token = encodeURIComponent(window.btoa(postId));

  // Creating the post link.
  var postLink = "http://" + location.hostname + "/post/" + token;

  $('#linkField').val(postLink);
  $("#copyArea").attr("id", `${postId}`);

}
/**
 * When the copy button is clicked post link will be copied to the clipboard.
 */
function copyBtn() {
  var copyTextarea = document.querySelector('#linkField');
  copyTextarea.focus();
  copyTextarea.select();
  document.execCommand('copy');
}
/**
 * This block of code identifies the click event outside the modal and hide the
 * modal from the screen.
 */
$(document).click(function (e) {
  if ($(e.target).is('#copyLinkModel')) {

    // Hiding link modal.
    $('.copyLinkModel').css('display', 'none');

  } else if ($(e.target).is('#myModal')) {

    // Hiding post modal.
    $('#myModal').css('display', 'none');

  } else if ($(e.target).is('#commentModal')) {

    // Hiding comment modal.
    $('#commentModal').css('display', 'none');

  } else if (!$(e.target).is('.subMenuForPost') && !$(e.target).is('.menu')) {

    // Hiding the post menu as well as comment menu.
    $('.subMenuForPost').css('display', 'none');
    $('.subMenuForComment').css('display', 'none');
    $('.closeMenu').css('display', 'none');
    $('.menu').css('display', 'block');
  }
});
/**
 * Delete comment identified the comment and send a ajax request to the server
 * for deleting the comment,
 * 
 * @param commentId
 *   Comment to be deleted will be identified through the comment id.
 */
function deleteComment(commentId) {
  const comment = document.getElementById(`comment${commentId}`);
  // If the user is already in the list, remove it.
  if (comment) {
    $.ajax({
      url: '/deleteComment',
      method: 'POST',
      data: {
        commentId: commentId
      },
      beforeSend: function () {
        showRightLoader();
      },
      success: function (response) {
        hideRightLoader();
      },
      error: function (xhr, status, error) {
        hideRightLoader();
        console.log('Error:', xhr.responseText);
      }
    });
    comment.parentNode.removeChild(comment);
  }
}
/**
 * Close modal function close the modal.
 */
function closeModal() {
  var modal = document.getElementById("myModal");
  modal.style.display = "none";
}
/**
 * Close comment modal function hide the modal.
 */
function closeCommentModal() {
  // Closing modal and post menu
  var modal = document.getElementById("commentModal");
  modal.style.display = "none";
}
/**
 * Add like in the database for the user and count the latest number of likes
 * in a post.
 * 
 * @param postID
 *   Post ID is to identify which post have have been liked.
 */
function addLike(postId) {
  $.ajax({
    url: '/like',
    method: 'POST',
    data: {
      postId: postId
    },
    success: function (response) {
      // Set the likes count.
      $(`#count${postId}`).text(response.likesList.length);
    },
    error: function (xhr, status, error) {
      console.log('Error:', xhr.responseText);
    }
  });
}
/**
 * Remove like function remove the like from the view and update the database.
 * 
 * @param postID
 *   Post ID is to identify which post have have been liked.
 */
function removeLike(postId) {
  $.ajax({
    url: '/dislike',
    method: 'POST',
    data: {
      postId: postId
    },
    success: function (response) {
      // Set the likes count.
      $(`#count${postId}`).text(response.likesList.length);
    },
    error: function (xhr, status, error) {
      console.log('Error:', xhr.responseText);
    }
  });
}
/**
 * This functions fetch comments for a particular post. 
 * 
 * @param postId
 *   Post ID is the unique identifier for each post.
 */
function showComment(postId) {
  $.ajax({
    url: '/fetchComments',
    method: 'POST',
    data: {
      postId: postId
    },
    beforeSend: function () {
      showRightLoader();
    },
    success: function (response) {
      // Setting comments one by one in the list.
      response.comment.forEach(element => {
        addComments(element);
      });
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
// When logout button will be clicked this ajax request will be pushed.
$('#deleteBtn').click(function (event) {
  event.preventDefault();
  $.ajax({
    url: '/logout',
    data: { 'flag': true },
    beforeSend: function () {
      showRightLoader();
      showLeftLoader();
    },
    success: function (response) {
      if (response.msg) {
        //on the success user will be redirected to the login page.
        window.location.href = '/login';
        hideRightLoader();
        hideLeftLoader();
      }
    },
    complete: function () {
      hideRightLoader();
      hideLeftLoader();
    }
  });
});
// Showing skeleton effect.
$('.post-content').hide();
setTimeout(
  function () {
    $('.active-users-skeleton').hide();
    $('.post-skeleton').hide();

    $('.post-content').show();
    $('.active-users-content').show();
}, 2000);
/**
 * On clicking on the other side of the window, list elements will disappear.
 */
document.addEventListener('click', (event) => {
  const target = event.target;
  if (!target.closest('.subMenuProfile') && !target.closest('.profile')) {
    $('.subMenuProfile').css('display', 'none');
  }
});