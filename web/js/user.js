var user = {
    init: function() {
        $('#signUp').click(user.register);
        $('#signIn').click(user.signin);
    },

    register: function() {
        var login = $('#userLogin').val();
        var pass = $('#userPass').val();

        $.ajax({
            url: "signUp",
            data: {
                'login': login,
                'pass': pass
            },
            type: "POST",
            dataType: "json",
            success: function(response) {
                $('#regModal').modal('hide')
            }
        });
    },

    signin: function() {
        var login = $('#userLogin').val();
        var pass = $('#userPass').val();

        $.ajax({
            url: "signUp",
            data: {
                'login': login,
                'pass': pass
            },
            type: "POST",
            dataType: "json",
            success: function(response) {
                $('#regModal').modal('hide')
            }
        });
    }
}


$(function(){
    user.init();
});