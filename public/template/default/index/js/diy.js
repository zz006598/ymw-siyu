// 点赞
$.fn.postLike = function() {
    if ($(this).hasClass('done')) {
		const Toast = Swal.mixin({
        toast: true,
        showConfirmButton: false,
        timer: 3000
	      });
	      Toast.fire({
	        type: 'error',
	        title: '您已经点过赞了！！！'
	      })
        return false;
    } else {
        $(this).addClass('done');
        var id = $(this).data("id"),
        action = $(this).data('action'),
        rateHolder = $(this).children('.count');
        var ajax_data = {
            action: "bigfa_like",
            um_id: id,
            um_action: action
        };
        $.post("https://www.sucaihu.com/wp-admin/admin-ajax.php", ajax_data,
        function(data) {
            $(rateHolder).html(data);
        });
        return false;
    }
};
$(document).on("click", ".favorite",
function() {
    $(this).postLike();
});