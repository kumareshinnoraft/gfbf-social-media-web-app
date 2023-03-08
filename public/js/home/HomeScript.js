/**
 * Like and dislike area.
 */
function disLike(postId) {

  $(`#like${postId}`).css('display', 'block');
  $(`#dislike${postId}`).css('display', 'none');
  addLike(postId);
}

function like(postId) {
  $(`#dislike${postId}`).css('display', 'block');
  $(`#like${postId}`).css('display', 'none');
  removeLike(postId);
}
/**
 * Comments area.
 */
function comment(button) {
    $('.commentSection').css('display', 'block');
    $('.comment').css('display', 'none');
    $('.commentHide').css('display', 'block');
    showComment();
}

function hideComment() {
  $('.commentSection').css('display', 'none');
  $('.comment').css('display', 'block');
  $('.commentHide').css('display', 'none');
}
/**
 * Menu button activities
 */

function postMenu(){
  $('.subMenuForPost').css('display', 'block');
  $('.closeMenu').css('display', 'block');
  $('.menu').css('display', 'none');
}

function closePostMenu(){
  $('.subMenuForPost').css('display', 'none');
  $('.closeMenu').css('display', 'none');
  $('.menu').css('display', 'block');
}
/**
 * Profile icon on click
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

function addLike(postId) {
  $.ajax({
    url: '/like',
    method: 'POST',
    data: {
      postId: postId
    },
    success: function(response) {
      $(`#count${postId}`).text(response.likesList.length);
    },
    error: function(xhr, status, error) {
      console.log('Error:', xhr.responseText);
    }
  });
}

function removeLike(postId) {
  $.ajax({
    url: '/dislike',
    method: 'POST',
    data: {
      postId: postId
    },
    success: function(response) {
      $(`#count${postId}`).text(response.likesList.length);
    },
    error: function(xhr, status, error) {
      console.log('Error:', xhr.responseText);
    }
  });
}

/**
 *  This functions fetch comments for a particular post. 
 */
function showComment() {
  $.ajax({
    url: '/fetchComments/{postID}',
    type: 'POST',
    beforeSend: function () {
      showRightLoader();
    },
    success: function (response) {
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
 * Post comments to the server
 */
$('#commentForm').submit(function (event) {
  event.preventDefault();
  var formData = $(this).serialize();
  $.ajax({
    url: '/postComment',
    type: 'POST',
    data: formData,
    beforeSend: function () {
      showRightLoader();
    },
    success: function (response) {
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

$('.post-content').hide();

setTimeout(
  function () {
    $('.active-users-skeleton').hide();
    $('.post-skeleton').hide();

    $('.post-content').show();
    $('.active-users-content').show();
}, 2000);