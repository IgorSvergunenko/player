var player = {

    init: function() {

        $('#searchTrack').click(player.searchTrack);
        $('#trackName').keypress(function(e) {
            if (e.which==13) {
                searchTrack();
            }
        });

        $('#audio-player').mediaelementplayer({
            alwaysShowControls: true,
            features: ['playpause','volume','progress'],
            audioVolume: 'horizontal',
            audioWidth: 400,
            audioHeight: 120,
            success: function (mediaElement, domObject) {
                playerObj = mediaElement;
                mediaElement.addEventListener('ended', function (e) {
                    var currentSongNumber = $('#currentSongNumber').val()
                    player.getNextSong(currentSongNumber);
                    mediaElement.play();
                }, false);
            }
        });

    },

    // search songs and show on page
    searchTrack: function() {

        var searchValue = $('#trackName').val();

        $.ajax({
            url: "search",
            data: {
                'searchValue': searchValue
            },
            type: "POST",
            dataType: "json",
            success: function(response) {
                displaySongs(response);
            }
        });
    },

    addToPlayList: function(songArtist, songName, url) {
        var number = $('#player .audioWrapper:last').attr('id');
        if (number == undefined) {
            number = 0;
        }
        number++;
        var audioElement = "<div class='audioWrapper' id='" + number + "'>"
            + "<span class='table_song'>" + songArtist + " - " + songName + "</span>"
            + "<input type='hidden' class='songUrl' value='"+url+"'>"
            + "<span class='glyphicon audio glyphicon-play'></span>"
            + "<span class='glyphicon audio glyphicon-remove'></span></div>";
        $('#player').append(audioElement);

        // Play song from playlist
        $('.glyphicon-play').click(function() {
            var number = $(this).parents('.audioWrapper').attr('id');
            var songName = $(this).parents('.audioWrapper').find('.table_song').text();
            var currentSong = $(this).parents('.audioWrapper').find('.songUrl').val();
            $('#song-name').html(songName);
            $('#audio-player').attr('src', currentSong);
            $('#currentSongNumber').val(number);
            playerObj.play()
        });

        // Remove song from playlist
        $('.glyphicon-remove').click(function() {
            $(this).parents('div.audioWrapper').remove()
        });
    },

    getNextSong: function(nextSongNumber) {
        nextSongNumber++;
        var songName = $('#' + nextSongNumber + ' .table_song').html();
        var src = $('#' + nextSongNumber + ' .songUrl').val();

        $('#song-name').html(songName);
        $('#audio-player').attr('src', src);
        $('#currentSongNumber').val(nextSongNumber);
    },

    playSong: function() {
        var url = $(this).data('url');
        var songArtist = $(this).parents('tr').find('.table_title').text();
        var songName = $(this).parents('tr').find('.table_song').text();
        var currentSong = $('#audio-player').attr('src');

        if (currentSong == 'empty') {
            // No songs in playlist, add first
            $('#song-name').html(songArtist + " - " + songName);
            $('#audio-player').attr('src', url);
            playerObj.play();
            player.addToPlayList(songArtist, songName, url);
            return;
        }

        // Add song in to playlist
        player.addToPlayList(songArtist, songName, url);
    }

}

$(function(){
    player.init();
});